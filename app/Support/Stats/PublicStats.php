<?php

namespace App\Support\Stats;

use App\Models\SiteEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Aggregate, anonymous numbers for the public /stats page. Only counts and the
 * names of public channels and videos leave this class: never emails, user
 * names, IPs or anything about a single signed up person.
 */
class PublicStats
{
    public const CACHE_KEY = 'public-stats:v1';
    public const CACHE_SECONDS = 600;
    public const DAYS = 30;
    /** A search term is only shown once this many searches used it. */
    public const MIN_TERM_COUNT = 3;

    public static function cached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, fn () => (new self())->build());
    }

    public function build(): array
    {
        $now = Carbon::now();
        $since = $now->copy()->startOfDay()->subDays(self::DAYS - 1);
        $hasEvents = Schema::hasTable('site_events');

        $platformLabels = [
            'youtube' => 'YouTube', 'rumble' => 'Rumble', 'vimeo' => 'Vimeo', 'twitch' => 'Twitch',
            'dailymotion' => 'Dailymotion', 'odysee' => 'Odysee', 'vidgaze' => 'VidGaze',
        ];

        $byPlatform = DB::table('videos')->where('visibility', 'public')
            ->selectRaw('preferred_source as k, COUNT(*) as c')->groupBy('preferred_source')->orderByDesc('c')->get()
            ->map(fn ($r) => ['label' => $platformLabels[$r->k] ?? ucfirst((string) $r->k), 'count' => (int) $r->c])->all();

        $byCategory = DB::table('videos')->leftJoin('categories', 'categories.id', '=', 'videos.category_id')
            ->where('videos.visibility', 'public')
            ->selectRaw('categories.name as k, COUNT(*) as c')->groupBy('categories.name')->orderByDesc('c')->get()
            ->map(fn ($r) => ['label' => $r->k ?? 'Not categorised yet', 'count' => (int) $r->c])->all();

        $publicChannels = DB::table('creators')->whereNull('user_id')
            ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('videos')->whereColumn('videos.creator_id', 'creators.id'));

        $events = fn (string $type) => $hasEvents ? DB::table('site_events')->where('type', $type) : null;
        $countSince = function ($query, Carbon $from, string $col = 'created_at') {
            return $query ? (int) (clone $query)->where($col, '>=', $from)->count() : 0;
        };

        $live = [];
        foreach (['day' => $now->copy()->subDay(), 'week' => $now->copy()->subDays(7)] as $key => $from) {
            $live[$key] = [
                'pageViews' => $countSince($events(SiteEvent::PAGE_VIEW), $from),
                'watches' => $countSince($events(SiteEvent::WATCH), $from),
                'searches' => $countSince($events(SiteEvent::SEARCH), $from),
                'newVideos' => $countSince(DB::table('videos')->where('visibility', 'public'), $from),
                'signups' => $countSince(DB::table('users'), $from),
                'likes' => $countSince(DB::table('video_interactions')->where('liked', 'like'), $from, 'updated_at'),
                'comments' => $countSince(DB::table('comments'), $from),
            ];
        }

        return [
            'generatedAt' => $now->toIso8601String(),
            'cacheMinutes' => intdiv(self::CACHE_SECONDS, 60),
            'days' => self::DAYS,
            'totals' => [
                'videos' => (int) DB::table('videos')->where('visibility', 'public')->count(),
                'channels' => (int) (clone $publicChannels)->count(),
                'categories' => (int) DB::table('categories')->count(),
                'users' => (int) DB::table('users')->count(),
                'playerViews' => (int) DB::table('videos')->sum('view_count'),
                'likes' => (int) DB::table('video_interactions')->where('liked', 'like')->count(),
                'comments' => (int) DB::table('comments')->count(),
                'pageViews' => $hasEvents ? (int) DB::table('site_events')->where('type', SiteEvent::PAGE_VIEW)->count() : 0,
                'watches' => $hasEvents ? (int) DB::table('site_events')->where('type', SiteEvent::WATCH)->count() : 0,
                'searches' => $hasEvents ? (int) DB::table('site_events')->where('type', SiteEvent::SEARCH)->count() : 0,
            ],
            'live' => $live,
            'videosByPlatform' => $byPlatform,
            'videosByCategory' => $byCategory,
            'daily' => [
                'dates' => $this->dates($since),
                'newVideos' => $this->daily(DB::table('videos')->where('visibility', 'public'), $since),
                'signups' => $this->daily(DB::table('users'), $since),
                'pageViews' => $hasEvents ? $this->daily(DB::table('site_events')->where('type', SiteEvent::PAGE_VIEW), $since) : $this->zeros(),
                'watches' => $hasEvents ? $this->daily(DB::table('site_events')->where('type', SiteEvent::WATCH), $since) : $this->zeros(),
                'searches' => $hasEvents ? $this->daily(DB::table('site_events')->where('type', SiteEvent::SEARCH), $since) : $this->zeros(),
            ],
            'publishedByMonth' => $this->publishedByMonth($now),
            'topChannelsByVideos' => (clone $publicChannels)
                ->select('creators.name', 'creators.slug')
                ->selectSub(fn ($q) => $q->from('videos')->selectRaw('COUNT(*)')->whereColumn('videos.creator_id', 'creators.id')->where('visibility', 'public'), 'c')
                ->orderByDesc('c')->limit(10)->get()
                ->map(fn ($r) => ['label' => $r->name, 'slug' => $r->slug, 'count' => (int) $r->c])->all(),
            'mostWatchedVideos' => $hasEvents ? $this->mostWatchedVideos($since) : [],
            'mostWatchedChannels' => $hasEvents ? $this->mostWatchedChannels($since) : [],
            'topSearches' => $hasEvents ? $this->topSearches($since) : [],
        ];
    }

    private function dates(Carbon $since): array
    {
        return collect(range(0, self::DAYS - 1))->map(fn ($i) => $since->copy()->addDays($i)->toDateString())->all();
    }

    private function zeros(): array
    {
        return array_fill(0, self::DAYS, 0);
    }

    private function daily($query, Carbon $since, string $col = 'created_at'): array
    {
        $rows = $query->where($col, '>=', $since)
            ->selectRaw("DATE($col) as d, COUNT(*) as c")->groupBy('d')->pluck('c', 'd');
        return array_map(fn ($d) => (int) ($rows[$d] ?? 0), $this->dates($since));
    }

    /** Videos per month of their original upload date, last 12 months. */
    private function publishedByMonth(Carbon $now): array
    {
        $start = $now->copy()->startOfMonth()->subMonths(11);
        $monthExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', time_published)"
            : "DATE_FORMAT(time_published, '%Y-%m')";
        $rows = DB::table('videos')->where('visibility', 'public')->where('time_published', '>=', $start)
            ->selectRaw("$monthExpr as m, COUNT(*) as c")->groupBy('m')->pluck('c', 'm');
        $months = collect(range(0, 11))->map(fn ($i) => $start->copy()->addMonths($i)->format('Y-m'));
        return [
            'months' => $months->all(),
            'counts' => $months->map(fn ($m) => (int) ($rows[$m] ?? 0))->all(),
        ];
    }

    private function mostWatchedVideos(Carbon $since): array
    {
        return DB::table('site_events')
            ->join('videos', 'videos.id', '=', 'site_events.video_id')
            ->join('creators', 'creators.id', '=', 'videos.creator_id')
            ->where('site_events.type', SiteEvent::WATCH)->where('site_events.created_at', '>=', $since)
            ->where('videos.visibility', 'public')->whereNull('creators.user_id')
            ->selectRaw('videos.title, videos.slug, creators.name as channel, COUNT(*) as c')
            ->groupBy('videos.id', 'videos.title', 'videos.slug', 'creators.name')
            ->orderByDesc('c')->limit(10)->get()
            ->map(fn ($r) => ['label' => $r->title, 'slug' => $r->slug, 'channel' => $r->channel, 'count' => (int) $r->c])->all();
    }

    private function mostWatchedChannels(Carbon $since): array
    {
        return DB::table('site_events')
            ->join('videos', 'videos.id', '=', 'site_events.video_id')
            ->join('creators', 'creators.id', '=', 'videos.creator_id')
            ->where('site_events.type', SiteEvent::WATCH)->where('site_events.created_at', '>=', $since)
            ->where('videos.visibility', 'public')->whereNull('creators.user_id')
            ->selectRaw('creators.name, creators.slug, COUNT(*) as c')
            ->groupBy('creators.id', 'creators.name', 'creators.slug')
            ->orderByDesc('c')->limit(10)->get()
            ->map(fn ($r) => ['label' => $r->name, 'slug' => $r->slug, 'count' => (int) $r->c])->all();
    }

    /** Popular cleaned search terms. Rare terms are left out so no one search can be singled out. */
    private function topSearches(Carbon $since): array
    {
        return DB::table('site_events')
            ->where('type', SiteEvent::SEARCH)->whereNotNull('term')->where('created_at', '>=', $since)
            ->selectRaw('term, COUNT(*) as c')->groupBy('term')
            ->havingRaw('COUNT(*) >= ?', [self::MIN_TERM_COUNT])
            ->orderByDesc('c')->limit(10)->get()
            ->filter(fn ($r) => SiteEvents::cleanTerm($r->term) !== null)
            ->map(fn ($r) => ['label' => $r->term, 'count' => (int) $r->c])->values()->all();
    }
}
