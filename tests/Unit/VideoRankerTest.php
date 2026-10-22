<?php

namespace Tests\Unit;

use App\Services\Recommendations\VideoRanker;
use PHPUnit\Framework\TestCase;

class VideoRankerTest extends TestCase
{
    private int $now = 1_800_000_000;

    private function v(int $id, array $o = []): array
    {
        return array_merge([
            'id' => $id, 'creator_id' => $id, 'category_id' => 1, 'platform' => 'youtube',
            'title' => "Interesting video number {$id} about gardening", 'description' => str_repeat('Long description. ', 20),
            'duration' => 900, 'published' => $this->now - 86400, 'pinned' => false,
            'views' => 0, 'likes' => 0, 'impressions' => 0, 'tags' => [],
        ], $o);
    }

    public function test_fresher_videos_score_higher(): void
    {
        $new = VideoRanker::score($this->v(1), $this->now);
        $old = VideoRanker::score($this->v(2, ['published' => $this->now - 90 * 86400]), $this->now);
        $this->assertGreaterThan($old, $new);
        $this->assertEqualsWithDelta(0.5, VideoRanker::freshness($this->now - 10 * 86400, $this->now, 10), 0.001);
    }

    public function test_quality_penalises_shorts_caps_and_empty_descriptions(): void
    {
        $good = VideoRanker::quality($this->v(1));
        $this->assertLessThan($good, VideoRanker::quality($this->v(2, ['duration' => 30])));
        $this->assertLessThan($good, VideoRanker::quality($this->v(3, ['title' => 'YOU WONT BELIEVE WHAT HAPPENED NEXT'])));
        $this->assertLessThan($good, VideoRanker::quality($this->v(4, ['description' => ''])));
        $this->assertLessThan($good, VideoRanker::quality($this->v(5, ['duration' => 24 * 3600])));
    }

    public function test_popularity_and_personal_boosts(): void
    {
        $base = VideoRanker::score($this->v(1), $this->now);
        $this->assertGreaterThan($base, VideoRanker::score($this->v(1, ['views' => 500, 'likes' => 20]), $this->now));
        $this->assertGreaterThan($base, VideoRanker::score($this->v(1), $this->now, ['boost_creators' => [1 => 1.6]]));
        $this->assertLessThan($base, VideoRanker::score($this->v(1), $this->now, ['seen' => [1 => true]]));
    }

    public function test_jitter_is_deterministic_and_bounded(): void
    {
        $this->assertSame(VideoRanker::jitter(42, 'seed'), VideoRanker::jitter(42, 'seed'));
        foreach (range(1, 200) as $id) {
            $j = VideoRanker::jitter($id, 'x', 0.15);
            $this->assertGreaterThanOrEqual(0.85, $j);
            $this->assertLessThanOrEqual(1.15, $j);
        }
    }

    public function test_diversify_limits_one_channel_per_row(): void
    {
        $c = [];
        foreach (range(1, 10) as $i) {
            $c[] = $this->v($i, ['creator_id' => 7, 'score' => 100 - $i, 'title' => "Channel seven upload {$i} topic{$i}"]);
        }
        foreach (range(11, 20) as $i) {
            $c[] = $this->v($i, ['score' => 10 - $i / 100, 'title' => "Different topic {$i} word{$i} other{$i}"]);
        }
        $picked = VideoRanker::diversify($c, 6);
        $this->assertCount(6, $picked);
        $creators = array_count_values(array_column($picked, 'creator_id'));
        $this->assertSame(1, $creators[7]);
        $this->assertSame(1, $picked[0]['id']);
    }

    public function test_diversify_mixes_platforms_and_excludes_ids(): void
    {
        $c = [];
        foreach (range(1, 10) as $i) {
            $c[] = $this->v($i, ['platform' => 'youtube', 'score' => 100 - $i, 'title' => "Youtube clip {$i} alpha{$i} gamma{$i}"]);
        }
        foreach (range(11, 14) as $i) {
            $c[] = $this->v($i, ['platform' => 'rumble', 'score' => 1, 'title' => "Rumble clip {$i} delta{$i} omega{$i}"]);
        }
        $picked = VideoRanker::diversify($c, 6, 1, 0.67, [1 => true]);
        $ids = array_column($picked, 'id');
        $this->assertNotContains(1, $ids);
        $platforms = array_count_values(array_column($picked, 'platform'));
        $this->assertLessThanOrEqual(4, $platforms['youtube']);
        $this->assertGreaterThanOrEqual(2, $platforms['rumble']);
    }

    public function test_diversify_skips_near_duplicate_titles(): void
    {
        $c = [
            $this->v(1, ['title' => 'Best Morning Worship Songs Powerful Christian Praise', 'score' => 10]),
            $this->v(2, ['title' => 'Best Morning Worship Songs Powerful Christian Praise 2026', 'score' => 9]),
            $this->v(3, ['title' => 'Bible study on the book of Romans', 'score' => 8]),
        ];
        $ids = array_column(VideoRanker::diversify($c, 2), 'id');
        $this->assertSame([1, 3], $ids);
    }

    public function test_diversify_relaxes_when_short_of_candidates(): void
    {
        $c = [];
        foreach (range(1, 5) as $i) {
            $c[] = $this->v($i, ['creator_id' => 3, 'score' => 10 - $i, 'title' => "Distinct subject {$i} alpha{$i} beta{$i}"]);
        }
        $this->assertCount(5, VideoRanker::diversify($c, 5));
    }

    public function test_relatedness_prefers_similar_titles_and_same_channel(): void
    {
        $current = $this->v(1, ['title' => 'Bible study Romans chapter 8', 'creator_id' => 5, 'tags' => ['bible', 'romans']]);
        $similar = $this->v(2, ['title' => 'Romans chapter 9 Bible study', 'creator_id' => 9, 'category_id' => 2]);
        $unrelated = $this->v(3, ['title' => 'Nursery rhymes for toddlers', 'creator_id' => 9, 'category_id' => 2]);
        $sameChannel = $this->v(4, ['title' => 'Q and A night', 'creator_id' => 5, 'category_id' => 2]);
        $this->assertGreaterThan(VideoRanker::relatedness($current, $unrelated), VideoRanker::relatedness($current, $similar));
        $this->assertGreaterThan(VideoRanker::relatedness($current, $unrelated), VideoRanker::relatedness($current, $sameChannel));
    }

    public function test_tokens_drop_stopwords_and_short_words(): void
    {
        $this->assertSame(['romans' => true, 'grace' => true], VideoRanker::tokens('The Romans and of grace 2026 a'));
    }
}
