<?php

namespace Tests\Unit;

use App\Helpers\PlatformAPIs\Odysee;
use App\Helpers\SearchQueryDTO;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OdyseeSearchTest extends TestCase
{
    public function test_search_maps_lighthouse_hits_to_valid_results(): void
    {
        Http::fake([
            'lighthouse.odysee.tv/search*' => Http::response([
                [
                    'channel' => '@TestChannel',
                    'claimId' => 'abc123def456',
                    'description' => 'Hello',
                    'duration' => 120,
                    'name' => 'my-video-slug',
                    'release_time' => '2026-01-15T12:00:00Z',
                    'thumbnail_url' => 'https://thumbs.example.com/t.webp',
                    'title' => 'Test Video Title',
                ],
            ], 200),
        ]);

        $dtos = Odysee::search(new SearchQueryDTO('test', 10));

        $this->assertCount(1, $dtos);
        $this->assertSame('my-video-slug:abc123def456', $dtos[0]->content->id);
        $this->assertSame('TestChannel', $dtos[0]->creator->id);
        $this->assertSame('Test Video Title', $dtos[0]->content->name);
        $this->assertSame('od_abc123def456', $dtos[0]->content->storage_slug);
    }

    public function test_search_sets_creator_avatar_via_resolve_api_when_channel_claim_id_present(): void
    {
        Http::fake([
            'lighthouse.odysee.tv/search*' => Http::response([
                [
                    'channel' => '@AvatarChan',
                    'channel_claim_id' => 'be8a681efd5302f4fdc27fbf3b04ea51b6afdf78',
                    'claimId' => '0263229ca18b4d77195ad05ad084068dd566f142',
                    'description' => '',
                    'duration' => 60,
                    'name' => 'my-claim-name',
                    'release_time' => '2026-01-01T00:00:00Z',
                    'thumbnail_url' => 'https://thumbs.example.com/v.webp',
                    'title' => 'Vid title',
                ],
            ], 200),
            'api.na-backend.odysee.com/api/v1/proxy' => Http::response([
                'jsonrpc' => '2.0',
                'result' => [
                    'lbry://@avatarchan#be8a681efd5302f4fdc27fbf3b04ea51b6afdf78' => [
                        'value' => [
                            'thumbnail' => ['url' => 'https://thumbs.odycdn.com/face.webp'],
                        ],
                    ],
                ],
                'id' => 1,
            ], 200),
        ]);

        $dtos = Odysee::search(new SearchQueryDTO('avatar test', 5));

        $this->assertCount(1, $dtos);
        $this->assertSame('https://thumbs.odycdn.com/face.webp', $dtos[0]->creator->avatar_url);
    }

    public function test_search_falls_back_when_primary_fails(): void
    {
        Http::fake([
            'lighthouse.odysee.tv/search*' => Http::response('', 500),
            'lighthouse.lbry.com/search*' => Http::response([
                [
                    'channel' => '@Zed',
                    'claimId' => 'z199',
                    'description' => '',
                    'duration' => 1,
                    'name' => 'claim-name',
                    'release_time' => '2026-01-01T00:00:00Z',
                    'thumbnail_url' => 'https://example.com/y.png',
                    'title' => 'Fallback title',
                ],
            ], 200),
        ]);

        $dtos = Odysee::search(new SearchQueryDTO('q', 5));

        $this->assertCount(1, $dtos);
        $this->assertSame('Zed', $dtos[0]->creator->id);
        $this->assertSame('claim-name:z199', $dtos[0]->content->id);
    }
}
