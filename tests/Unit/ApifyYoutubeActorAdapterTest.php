<?php

namespace Tests\Unit;

use App\Helpers\ApifyYoutubeActorAdapter;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ApifyYoutubeActorAdapterTest extends TestCase
{
    protected function tearDown(): void
    {
        Config::set('services.apify.youtube_actor', 'streamers~youtube-scraper');
        parent::tearDown();
    }

    public function test_streamers_actor_leaves_input_and_rows_unchanged(): void
    {
        Config::set('services.apify.youtube_actor', 'streamers~youtube-scraper');
        $in = ['searchQueries' => ['a'], 'maxResults' => 5, 'sortVideosBy' => 'NEWEST'];
        $this->assertSame($in, ApifyYoutubeActorAdapter::normalizeInputBeforeSync($in));
        $rows = [['channelId' => 'UCx', 'title' => 't']];
        $this->assertSame($rows, ApifyYoutubeActorAdapter::normalizeRowsAfterSync($rows));
    }

    public function test_apidojo_maps_search_and_max_and_flattens_start_urls(): void
    {
        Config::set('services.apify.youtube_actor', 'apidojo~youtube-scraper');
        $in = [
            'searchQueries' => ['cats'],
            'maxResults' => 7,
            'startUrls' => [['url' => 'https://www.youtube.com/channel/UCabc1234567890123456789']],
        ];
        $out = ApifyYoutubeActorAdapter::normalizeInputBeforeSync($in);
        $this->assertSame(['cats'], $out['keywords']);
        $this->assertArrayNotHasKey('searchQueries', $out);
        $this->assertSame(7, $out['maxItems']);
        $this->assertArrayNotHasKey('maxResults', $out);
        $this->assertSame(
            ['https://www.youtube.com/channel/UCabc1234567890123456789/videos'],
            $out['startUrls']
        );
        $this->assertSame('us', $out['gl']);
        $this->assertSame('en', $out['hl']);
    }

    public function test_apidojo_bumps_max_items_for_start_url_only_requests(): void
    {
        Config::set('services.apify.youtube_actor', 'apidojo/youtube-scraper');
        $in = [
            'startUrls' => [['url' => 'https://www.youtube.com/watch?v=abc']],
            'maxResults' => 6,
        ];
        $out = ApifyYoutubeActorAdapter::normalizeInputBeforeSync($in);
        $this->assertSame(10, $out['maxItems']);
    }

    public function test_apidojo_raises_max_items_for_channel_start_urls(): void
    {
        Config::set('services.apify.youtube_actor', 'apidojo~youtube-scraper');
        $in = [
            'startUrls' => [['url' => 'https://www.youtube.com/channel/UCabc1234567890123456789/videos']],
            'maxResults' => 5,
        ];
        $out = ApifyYoutubeActorAdapter::normalizeInputBeforeSync($in);
        $this->assertSame(25, $out['maxItems']);
    }

    public function test_apidojo_does_not_bump_max_when_keywords_present(): void
    {
        Config::set('services.apify.youtube_actor', 'apidojo~youtube-scraper');
        $in = ['searchQueries' => ['q'], 'maxResults' => 5];
        $out = ApifyYoutubeActorAdapter::normalizeInputBeforeSync($in);
        $this->assertSame(5, $out['maxItems']);
    }

    public function test_apidojo_output_drops_error_rows_and_flattens_channel(): void
    {
        Config::set('services.apify.youtube_actor', 'apidojo~youtube-scraper');
        $rows = [
            ['noResults' => true, 'error' => true, 'message' => 'x'],
            [
                'id' => 'vid1',
                'description' => 'd',
                'views' => 99,
                'channel' => ['id' => 'UCch', 'name' => 'N', 'url' => 'https://www.youtube.com/channel/UCch'],
            ],
        ];
        $out = ApifyYoutubeActorAdapter::normalizeRowsAfterSync($rows);
        $this->assertCount(1, $out);
        $this->assertSame('UCch', $out[0]['channelId']);
        $this->assertSame('N', $out[0]['channelName']);
        $this->assertSame('d', $out[0]['text']);
        $this->assertSame(99, $out[0]['viewCount']);
    }

    public function test_apidojo_adjust_handle_root_url(): void
    {
        $this->assertSame(
            'https://www.youtube.com/@kurzgesagt/videos',
            ApifyYoutubeActorAdapter::apidojoAdjustChannelRootUrl('https://www.youtube.com/@kurzgesagt')
        );
    }
}
