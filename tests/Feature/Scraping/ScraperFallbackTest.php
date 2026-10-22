<?php

namespace Tests\Feature\Scraping;

use App\Helpers\PlatformAPIs\Apify;
use App\Helpers\PlatformAPIs\Rumble;
use App\Helpers\PlatformAPIs\YouTube;
use App\Helpers\SearchQueryDTO;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ScraperFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        YouTube::forgetChannelPages();
        config([
            'services.crawl4ai.url' => 'http://crawl4ai:11235',
            'services.crawl4ai.token' => 'test-token',
            'services.apify.token' => 'apify-test',
            'services.apify.daily_cap' => 2,
            'services.scraper.key' => null,
        ]);
    }

    private function fixture(string $name): string
    {
        return file_get_contents(__DIR__ . '/../../Fixtures/scraping/' . $name);
    }

    private function crawlOk(string $html): array
    {
        return ['success' => true, 'results' => [['url' => 'x', 'success' => true, 'status_code' => 200, 'html' => $html]]];
    }

    private function query(string $q = 'news'): SearchQueryDTO
    {
        return new SearchQueryDTO($q, 10, []);
    }

    public function test_rumble_search_uses_crawl4ai_and_never_calls_apify(): void
    {
        Http::fake([
            'crawl4ai:11235/*' => Http::response($this->crawlOk($this->fixture('rumble_search.html'))),
            'api.apify.com/*' => Http::response([], 500),
        ]);

        $results = Rumble::search($this->query());

        $this->assertCount(2, $results); // 1 channel + 1 video
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'crawl4ai:11235/crawl')
            && $r->hasHeader('Authorization', 'Bearer test-token')
            && $r['urls'] === ['https://rumble.com/search/all?q=news']);
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'apify.com'));
    }

    public function test_rumble_search_falls_back_to_cheap_apify_run_when_crawl4ai_fails(): void
    {
        Http::fake([
            'crawl4ai:11235/*' => Http::response('down', 502),
            'api.apify.com/*' => Http::response([
                ['type' => 'video', 'id' => '441288236', 'title' => 'WHITE NUKES FOX NEWS', 'durationSeconds' => 36,
                    'uploadedDate' => '2026-07-18T14:01:14+00:00', 'thumbnail' => 'https://x/t.jpg',
                    'channelName' => 'RealAmericasVoice', 'channelUrl' => 'https://rumble.com/user/RealAmericasVoice'],
            ]),
        ]);

        $results = Rumble::search($this->query());

        $this->assertCount(1, $results);
        $this->assertSame('v7aqc6k', $results[0]->content->id);
        Http::assertSent(function (Request $r) {
            if (!str_contains($r->url(), 'apify.com')) return false;
            parse_str(parse_url($r->url(), PHP_URL_QUERY), $q);
            return str_contains($r->url(), '/acts/memo23~rumble-scraper/run-sync-get-dataset-items')
                && $q['memory'] === '512' && $q['timeout'] === '90' && $q['maxItems'] === '20'
                && $r['fetchDetails'] === false && $r['includeChannelInfo'] === false
                && $r['maxPagesPerList'] === 1 && $r['proxy'] === ['useApifyProxy' => false]
                && $r['searchQueries'] === ['news'];
        });
        $this->assertSame(1, Apify::runsToday());
    }

    public function test_apify_results_are_reused_and_daily_cap_is_enforced(): void
    {
        Http::fake([
            'crawl4ai:11235/*' => Http::response('down', 502),
            'api.apify.com/*' => Http::response([]),
        ]);

        Rumble::search($this->query('a'));
        Rumble::search($this->query('a')); // cached: no second run
        $this->assertSame(1, Apify::runsToday());

        Rumble::search($this->query('b'));
        Rumble::search($this->query('c')); // over the cap of 2
        $this->assertSame(2, Apify::runsToday());
        Http::assertSentCount(4 * 3 + 2); // 4 searches x 3 crawl4ai attempts, 2 Apify runs
    }

    public function test_no_apify_call_without_token(): void
    {
        config(['services.apify.token' => null]);
        Http::fake(['crawl4ai:11235/*' => Http::response('down', 502)]);

        $this->assertSame([], Rumble::search($this->query()));
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'apify.com'));
    }

    public function test_rumble_creator_videos_from_crawl4ai(): void
    {
        Http::fake(['crawl4ai:11235/*' => Http::response($this->crawlOk($this->fixture('rumble_channel_videos.html')))]);

        $page = Rumble::getCreatorVideos('bongino');

        $this->assertCount(2, $page['results']);
        $this->assertSame('The Dan Bongino Show', $page['results'][0]->creator->name);
        $this->assertSame('https://hugh.cdn.rumble.cloud/banner.png', $page['results'][0]->creator->banner_url);
    }

    public function test_rumble_channel_videos_retry_when_layout_has_no_grid(): void
    {
        Http::fakeSequence('crawl4ai:11235/*')
            ->push($this->crawlOk('<html><body><div class="channel-header--title"><h1>X</h1></div></body></html>'))
            ->push($this->crawlOk($this->fixture('rumble_channel_videos.html')));
        Http::fake(['api.apify.com/*' => Http::response([], 500)]);

        $this->assertCount(2, Rumble::getCreatorVideos('bongino')['results']);
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'apify.com'));
    }

    public function test_youtube_search_uses_crawl4ai_without_scraper_tech(): void
    {
        Http::fake([
            'crawl4ai:11235/*' => Http::response($this->crawlOk($this->fixture('youtube_search.html'))),
            'api.scraper.tech/*' => Http::response([], 500),
        ]);

        $results = YouTube::searchVideos($this->query());

        $this->assertCount(2, $results);
        $this->assertSame('BBC News', $results[0]->creator->name);
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'scraper.tech'));
    }

    public function test_youtube_channel_videos_from_rss_feed(): void
    {
        Http::fake([
            // crawl4ai answers with the consent wall, so the feed is used
            'crawl4ai:11235/*' => Http::response($this->crawlOk('<html>Before you continue to YouTube</html>')),
            'www.youtube.com/feeds/*' => Http::response($this->fixture('youtube_feed.xml')),
        ]);

        $page = YouTube::getCreatorVideosBeforeDate('UCX6OQ3DkcsbYNE6H8uQQuVA', null, 3);

        $this->assertCount(2, $page['results']);
        $this->assertSame('UCX6OQ3DkcsbYNE6H8uQQuVA', $page['results'][0]->creator_id);
        $this->assertFalse($page['hasNext']);
    }

    public function test_youtube_channel_videos_from_channel_page_with_consent_profile(): void
    {
        Http::fake([
            'crawl4ai:11235/*' => Http::response($this->crawlOk($this->fixture('youtube_channel_videos.html'))),
            'www.youtube.com/feeds/*' => Http::response('', 404),
        ]);

        $page = YouTube::getCreatorVideosBeforeDate('UCHnyfMqiRRG1u-2MsSQLbXA', null, 50);

        $this->assertCount(20, $page['results']);
        $this->assertSame('NiCgcUTL9_Q', $page['results'][0]->id);
        $this->assertSame('2072', $page['results'][0]->duration);
        $this->assertSame('UCHnyfMqiRRG1u-2MsSQLbXA', $page['results'][0]->creator_id);
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'crawl4ai:11235/crawl')
            && $r['site_profile'] === 'youtube'
            && $r['urls'] === ['https://www.youtube.com/channel/UCHnyfMqiRRG1u-2MsSQLbXA/videos']);
    }

    public function test_youtube_creator_gets_banner_from_channel_page(): void
    {
        Http::fake([
            'crawl4ai:11235/*' => Http::response($this->crawlOk($this->fixture('youtube_channel_videos.html'))),
            'www.youtube.com/feeds/*' => Http::response('', 404),
        ]);

        $creators = YouTube::getCreators(['UCHnyfMqiRRG1u-2MsSQLbXA']);

        $this->assertCount(1, $creators);
        $this->assertSame('Veritasium', $creators[0]->name);
        $this->assertStringStartsWith('https://yt3.googleusercontent.com/', $creators[0]->banner_url);
        $this->assertStringStartsWith('https://yt3.googleusercontent.com/', $creators[0]->avatar_url);
    }

    public function test_youtube_profile_is_not_sent_for_search_or_watch_pages(): void
    {
        Http::fake([
            'crawl4ai:11235/*' => Http::response($this->crawlOk($this->fixture('youtube_search.html'))),
        ]);

        YouTube::searchVideos($this->query());

        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'crawl4ai') && isset($r['site_profile']));
    }

    public function test_youtube_profile_can_be_disabled(): void
    {
        config(['services.crawl4ai.youtube_profile' => '']);
        Http::fake([
            'crawl4ai:11235/*' => Http::response($this->crawlOk($this->fixture('youtube_channel_videos.html'))),
            'www.youtube.com/feeds/*' => Http::response($this->fixture('youtube_feed.xml')),
        ]);

        $page = YouTube::getCreatorVideosBeforeDate('UCX6OQ3DkcsbYNE6H8uQQuVA', null, 3);

        $this->assertCount(2, $page['results']);
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'crawl4ai'));
    }
}
