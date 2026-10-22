<?php

namespace Tests\Unit\Scraping;

use App\Helpers\Scraping\RumbleParser;
use PHPUnit\Framework\TestCase;

class RumbleParserTest extends TestCase
{
    private function fixture(string $name): string
    {
        return file_get_contents(__DIR__ . '/../../Fixtures/scraping/' . $name);
    }

    public function test_embed_id_is_base36_of_numeric_id(): void
    {
        $this->assertSame('v7eyxz8', RumbleParser::embedId('448408196'));
        $this->assertSame('v7aqc6k', RumbleParser::embedId(441288236));
        $this->assertNull(RumbleParser::embedId('abc'));
    }

    public function test_parses_channel_header(): void
    {
        $c = RumbleParser::parseChannel($this->fixture('rumble_channel_videos.html'));
        $this->assertSame('The Dan Bongino Show', $c['name']);
        $this->assertSame('https://hugh.cdn.rumble.cloud/avatar.jpeg', $c['avatar']);
        $this->assertSame('https://hugh.cdn.rumble.cloud/banner.png', $c['banner']);
        $this->assertStringContainsString('The Dan Bongino Show', $c['description']);
    }

    public function test_channel_without_header_returns_null(): void
    {
        $this->assertNull(RumbleParser::parseChannel('<html><body>blocked</body></html>'));
    }

    public function test_parses_channel_videos_and_dedupes(): void
    {
        $videos = RumbleParser::parseChannelVideos($this->fixture('rumble_channel_videos.html'));
        $this->assertCount(2, $videos);
        $this->assertSame('v7ehr8a', $videos[0]['id']);
        $this->assertSame(3350, $videos[0]['duration']);
        $this->assertSame('2026-10-10T16:03:02+00:00', $videos[0]['upload_date']);
        $this->assertSame('bongino', $videos[0]['channel_slug']);
        $this->assertSame('Live & Upcoming', $videos[1]['title']);
        $this->assertSame('2026-10-22T14:00:00Z', $videos[1]['upload_date']);
        $this->assertCount(1, RumbleParser::parseChannelVideos($this->fixture('rumble_channel_videos.html'), 1));
    }

    public function test_parses_search_results(): void
    {
        $r = RumbleParser::parseSearch($this->fixture('rumble_search.html'));
        $this->assertCount(1, $r['videos']);
        $v = $r['videos'][0];
        $this->assertSame('v7eyxz8', $v['id']);
        $this->assertSame('POTUS CHASTISES THE FAKE NEWS', $v['title']);
        $this->assertSame(3733, $v['duration']);
        $this->assertSame('RealAmericasVoice', $v['channel_slug']);
        $this->assertSame('RealAmericasVoice', $v['channel_name']);
        $this->assertSame([['slug' => 'RebelNews', 'name' => 'Rebel News']], $r['channels']);
    }
}
