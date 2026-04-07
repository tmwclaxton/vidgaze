<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\VideoModels\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoRecommendationsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommended_video_index_returns_results_for_guest_with_heuristic_only(): void
    {
        config(['services.nanogpt.key' => '', 'services.nanogpt.recommended_ranking_enabled' => true]);

        $category = Category::factory()->create();
        Video::factory()->count(5)->create([
            'category_id' => $category->id,
            'visibility' => 'public',
            'time_published' => now(),
            'preferred_source' => 'youtube',
            'impressions_count' => 10,
            'view_count' => 5,
        ]);

        $response = $this->getJson('/api/v1/video/index?category=recommended&per_page=4');

        $response->assertOk();
        $response->assertJsonPath('results', 4);
        $this->assertCount(4, $response->json('videos.data'));
    }

    public function test_category_videos_orders_without_error(): void
    {
        config(['services.nanogpt.key' => '', 'services.nanogpt.watch_next_ranking_enabled' => true]);

        $category = Category::factory()->create();
        $videos = Video::factory()->count(4)->create([
            'category_id' => $category->id,
            'visibility' => 'public',
            'time_published' => now(),
            'preferred_source' => 'youtube',
        ]);

        $anchor = $videos->first();

        $response = $this->getJson('/api/v1/video/category-videos?slug='.$category->slug.'&per_page=3&anchor_video_id='.$anchor->id);

        $response->assertOk();
        $this->assertLessThanOrEqual(3, count($response->json('videos.data')));
    }
}
