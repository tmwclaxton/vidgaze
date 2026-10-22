<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RecommenderRoutesTest extends TestCase
{
    public function test_related_route_is_registered_and_numeric_only(): void
    {
        $this->assertTrue(Route::has('api.video.related'));
        $this->assertStringEndsWith('/api/v1/video/42/related', route('api.video.related', ['video_id' => 42]));
    }

    public function test_recommender_is_resolvable(): void
    {
        $this->assertInstanceOf(\App\Services\Recommendations\Recommender::class, app(\App\Services\Recommendations\Recommender::class));
    }
}
