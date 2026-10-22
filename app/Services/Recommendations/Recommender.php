<?php

namespace App\Services\Recommendations;

use App\Helpers\ChristianPicks;
use App\Models\Category;
use App\Models\CreatorModels\CreatorInteraction;
use App\Models\User;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoInteraction;
use App\Models\VideoModels\VideoView;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Chooses the videos for the home page rows and the watch page suggestions.
 *
 * Home rows: candidates per category are scored on freshness, metadata
 * quality and on site popularity, then diversified (one video per channel,
 * platform mix, no near duplicate titles) and deduplicated across rows in
 * row order. Results are cached per hour bucket for guests and briefly per
 * user, so rows rotate gently and pages stay fast.
 *
 * Watch page: candidates come from the same channel, the same category and a
 * MySQL FULLTEXT title search, and are ranked by text similarity plus a
 * little freshness. The current video is always excluded.
 */
class Recommender
{
    /** Home rows in page order. Earlier rows win when a video fits several. */
    public const HOME_ROWS = ['vidgaze_picks', 'music', 'gaming', 'crypto_currency', 'alternate_news'];

    public const ROW_SIZE = 12;

    private const COLUMNS = ['id', 'creator_id', 'category_id', 'preferred_source', 'title', 'description',
        'duration', 'time_published', 'pinned', 'view_count', 'like_count', 'impressions_count', 'tags'];

    /** Half life per row in days: news ages fast, worship and music slowly. */
    private const HALF_LIFE = [
        'alternate_news' => 3,
        'crypto_currency' => 4,
        'gaming' => 10,
        'music' => 30,
        'vidgaze_picks' => 21,
    ];

    /**
     * Ordered video ids for one home row.
     */
    public function homeRow(string $slug, ?User $user = null, int $size = self::ROW_SIZE): array
    {
        if (in_array($slug, self::HOME_ROWS, true)) {
            $rows = $this->homeRows($user);
            return array_slice($rows[$slug] ?? [], 0, $size);
        }
        $category = Category::where('slug', $slug)->first();
        if (!$category) {
            return [];
        }
        $seed = $this->seed();
        return Cache::remember("reco:row:{$slug}:{$size}:{$seed}", 900, function () use ($category, $size, $seed) {
            $ctx = ['seed' => $seed, 'half_life' => self::HALF_LIFE[$category->slug] ?? 14];
            return $this->pickRow($category, $size, $ctx, []);
        });
    }

    /** All home rows, deduplicated across rows. Cached. */
    public function homeRows(?User $user = null): array
    {
        $seed = $this->seed();
        $key = $user ? "reco:home:u{$user->id}:{$seed}" : "reco:home:guest:{$seed}";
        $ttl = $user ? 300 : 900;

        return Cache::remember($key, $ttl, function () use ($user, $seed) {
            $personal = $user ? $this->personalContext($user) : [];
            $taken = $personal['hidden'] ?? [];
            $rows = [];
            $categories = Category::whereIn('slug', self::HOME_ROWS)->get()->keyBy('slug');
            foreach (self::HOME_ROWS as $slug) {
                $category = $categories[$slug] ?? null;
                if (!$category) {
                    $rows[$slug] = [];
                    continue;
                }
                $ctx = [
                    'seed' => $seed,
                    'half_life' => self::HALF_LIFE[$slug] ?? 14,
                    'boost_creators' => $personal['boost_creators'] ?? [],
                    'seen' => $personal['seen'] ?? [],
                ];
                $ids = $this->pickRow($category, self::ROW_SIZE, $ctx, $taken);
                foreach ($ids as $id) {
                    $taken[$id] = true;
                }
                $rows[$slug] = $ids;
            }
            return $rows;
        });
    }

    /** Ranked videos in a category for the category suggestions list. */
    public function categoryRanked(Category $category, int $size, array $excludeIds = []): array
    {
        $ctx = ['seed' => $this->seed(), 'half_life' => self::HALF_LIFE[$category->slug] ?? 14];
        return $this->pickRow($category, $size, $ctx, array_fill_keys(array_map('intval', $excludeIds), true), 2);
    }

    private function pickRow(Category $category, int $size, array $ctx, array $exclude, int $maxPerCreator = 1): array
    {
        $now = time();
        $candidates = [];
        foreach ($this->categoryCandidates($category) as $row) {
            $c = $this->toCandidate($row);
            // The Picks row stays Christian even if a categoriser slips.
            if ($category->slug === ChristianPicks::SLUG && !ChristianPicks::isChristian($c['title'], $c['description'])) {
                continue;
            }
            $c['score'] = VideoRanker::score($c, $now, $ctx);
            $candidates[] = $c;
        }
        return array_map(fn ($c) => $c['id'], VideoRanker::diversify($candidates, $size, $maxPerCreator, 0.67, $exclude));
    }

    /** Recent and pinned videos in a category, enough to rank but cheap. */
    private function categoryCandidates(Category $category)
    {
        $base = fn () => $this->baseQuery()->where('category_id', $category->id);
        $recent = $base()->orderByDesc('time_published')->limit(300)->get();
        $pinned = $base()->where('pinned', true)->limit(100)->get();
        return $recent->concat($pinned)->unique('id')->values();
    }

    /**
     * Ordered ids of videos to suggest next to $video.
     */
    public function related(Video $video, int $size = 20, ?User $user = null): array
    {
        $seed = $this->seed();
        $ids = Cache::remember("reco:related:{$video->id}:{$size}", 1800, function () use ($video, $size, $seed) {
            return $this->computeRelated($video, $size, $seed);
        });
        if ($user) {
            $hidden = $this->personalContext($user)['hidden'] ?? [];
            $ids = array_values(array_filter($ids, fn ($id) => !isset($hidden[$id])));
        }
        return $ids;
    }

    private function computeRelated(Video $video, int $size, string $seed): array
    {
        $now = time();
        $current = $this->toCandidate($video);

        $pool = collect();
        // Same channel, newest first.
        $pool = $pool->concat($this->baseQuery()->where('creator_id', $video->creator_id)
            ->where('id', '!=', $video->id)->orderByDesc('time_published')->limit(15)->get());
        // Same category.
        if ($video->category_id) {
            $pool = $pool->concat($this->baseQuery()->where('category_id', $video->category_id)
                ->where('id', '!=', $video->id)->orderByDesc('time_published')->limit(150)->get());
        }
        // Title text search.
        $fulltext = [];
        foreach ($this->fulltextMatches($video, 80) as $id => $score) {
            $fulltext[$id] = $score;
        }
        if ($fulltext) {
            $pool = $pool->concat($this->baseQuery()->whereIn('id', array_keys($fulltext))->get());
        }
        // Cold fallback so the column is never empty: fresh videos site wide.
        if ($pool->count() < $size) {
            $pool = $pool->concat($this->baseQuery()->where('id', '!=', $video->id)
                ->orderByDesc('time_published')->limit(100)->get());
        }
        $pool = $pool->unique('id')->reject(fn ($v) => $v->id === $video->id);

        $maxFt = $fulltext ? max($fulltext) : 0;
        $candidates = [];
        foreach ($pool as $row) {
            $c = $this->toCandidate($row);
            $ft = $maxFt > 0 ? (($fulltext[$c['id']] ?? 0) / $maxFt) : 0;
            $rel = VideoRanker::relatedness($current, $c, $ft);
            // Mostly relevance, a little freshness and quality, a tiny rotation.
            $c['score'] = (0.2 + $rel)
                * (0.7 + 0.3 * VideoRanker::freshness($c['published'], $now, 30))
                * VideoRanker::quality($c)
                * VideoRanker::jitter($c['id'], $seed, 0.05);
            $candidates[] = $c;
        }
        $picked = VideoRanker::diversify($candidates, $size, 3, 0.8, [$video->id => true]);
        return array_map(fn ($c) => $c['id'], $picked);
    }

    /** id => relevance from MySQL FULLTEXT on the title, empty on other drivers. */
    private function fulltextMatches(Video $video, int $limit): array
    {
        if (DB::getDriverName() !== 'mysql' || !$this->hasFulltext()) {
            return [];
        }
        $words = array_keys(VideoRanker::tokens($video->title . ' ' . implode(' ', array_slice((array) $this->tagsOf($video), 0, 8))));
        $words = array_slice($words, 0, 12);
        if (!$words) {
            return [];
        }
        $against = implode(' ', $words);
        try {
            return DB::table('videos')
                ->selectRaw('id, MATCH(title) AGAINST (? IN NATURAL LANGUAGE MODE) AS rel', [$against])
                ->whereRaw('MATCH(title) AGAINST (? IN NATURAL LANGUAGE MODE)', [$against])
                ->where('id', '!=', $video->id)
                ->where('available', true)
                ->orderByDesc('rel')
                ->limit($limit)
                ->pluck('rel', 'id')
                ->map(fn ($r) => (float) $r)
                ->all();
        } catch (\Throwable $e) {
            Log::warning('Related fulltext failed: ' . $e->getMessage());
            return [];
        }
    }

    private function hasFulltext(): bool
    {
        return Cache::remember('reco:has_fulltext', 3600, function () {
            return collect(DB::select("SHOW INDEX FROM videos WHERE Index_type = 'FULLTEXT'"))->isNotEmpty();
        });
    }

    /**
     * What we know about a signed in user: channels they subscribe to or
     * watch (boosted), videos they watched (lowered), and videos or channels
     * they marked not interested or reported (hidden).
     */
    public function personalContext(User $user): array
    {
        return Cache::remember("reco:personal:{$user->id}", 300, function () use ($user) {
            $boost = [];
            $hidden = [];
            $seen = [];

            $creatorRows = CreatorInteraction::where('viewer_id', $user->id)->get(['creator_id', 'subscribed', 'disinterested', 'reported']);
            $blockedCreators = [];
            foreach ($creatorRows as $r) {
                if ($r->disinterested || $r->reported) {
                    $blockedCreators[] = $r->creator_id;
                } elseif ($r->subscribed) {
                    $boost[$r->creator_id] = 1.6;
                }
            }

            $watched = VideoView::where('viewer_id', $user->id)->latest()->limit(300)->pluck('video_id')->all();
            foreach ($watched as $id) {
                $seen[$id] = true;
            }
            if ($watched) {
                $watchedCreators = Video::whereIn('id', $watched)->pluck('creator_id')->countBy();
                foreach ($watchedCreators as $creatorId => $n) {
                    $boost[$creatorId] = max($boost[$creatorId] ?? 1.0, min(1.5, 1 + 0.1 * $n));
                }
            }

            $interactions = VideoInteraction::where('viewer_id', $user->id)
                ->where(fn ($q) => $q->where('disinterested', true)->orWhere('reported', true)->orWhere('liked', 'dislike'))
                ->pluck('video_id')->all();
            foreach ($interactions as $id) {
                $hidden[$id] = true;
            }
            if ($blockedCreators) {
                foreach (Video::whereIn('creator_id', $blockedCreators)->pluck('id') as $id) {
                    $hidden[$id] = true;
                }
            }

            return ['boost_creators' => $boost, 'hidden' => $hidden, 'seen' => $seen];
        });
    }

    private function baseQuery()
    {
        $q = Video::query()->without(['creator', 'category'])->select(self::COLUMNS)->where('visibility', 'public');
        if ($this->hasAvailableColumn()) {
            $q->where('available', true);
        }
        return $q;
    }

    private function hasAvailableColumn(): bool
    {
        static $has = null;
        return $has ??= Schema::hasColumn('videos', 'available');
    }

    private function toCandidate($v): array
    {
        $published = $v->time_published ? strtotime((string) $v->time_published) : null;
        return [
            'id' => (int) $v->id,
            'creator_id' => (int) $v->creator_id,
            'category_id' => $v->category_id ? (int) $v->category_id : null,
            'platform' => (string) $v->preferred_source,
            'title' => (string) $v->title,
            'description' => (string) $v->description,
            'duration' => (int) $v->duration,
            'published' => $published ?: null,
            'pinned' => (bool) $v->pinned,
            'views' => (int) $v->view_count,
            'likes' => (int) $v->like_count,
            'impressions' => (int) $v->impressions_count,
            'tags' => $this->tagsOf($v),
        ];
    }

    private function tagsOf($v): array
    {
        $tags = $v->tags;
        if (is_string($tags)) {
            $tags = json_decode($tags, true);
        }
        return is_array($tags) ? array_values(array_filter($tags, 'is_string')) : [];
    }

    /** Changes every hour so rows rotate without reshuffling on every request. */
    private function seed(): string
    {
        return date('YmdH');
    }

    /** Load videos by id keeping the given order. */
    public static function loadOrdered(array $ids)
    {
        if (!$ids) {
            return collect();
        }
        $byId = Video::whereIn('id', $ids)->get()->keyBy('id');
        return collect($ids)->map(fn ($id) => $byId[$id] ?? null)->filter()->values();
    }
}
