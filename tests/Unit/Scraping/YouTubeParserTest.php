<?php

namespace Tests\Unit\Scraping;

use App\Helpers\Scraping\YouTubeParser;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class YouTubeParserTest extends TestCase
{
    private function fixture(string $name): string
    {
        return file_get_contents(__DIR__ . '/../../Fixtures/scraping/' . $name);
    }

    public function test_parses_search_page(): void
    {
        Carbon::setTestNow('2026-10-22 12:00:00');
        $r = YouTubeParser::parseSearch($this->fixture('youtube_search.html'));
        $this->assertCount(2, $r);
        $this->assertSame('moy3waNDQa8', $r[0]['videoId']);
        $this->assertSame('Websites taken down {braces} "quoted"', $r[0]['title']);
        $this->assertSame(117, $r[0]['duration']);
        $this->assertFalse($r[0]['is_live']);
        $this->assertSame('UC16niRr50-MSBwiO3YDb3RA', $r[0]['channelId']);
        $this->assertSame('https://yt3.ggpht.com/bbc=s68', $r[0]['channelAvatar']);
        $this->assertSame('Websites have been taken down', $r[0]['description']);
        $this->assertSame('2026-10-21 21:00:00', $r[0]['publish_time']->format('Y-m-d H:i:s'));
        $this->assertTrue($r[1]['is_live']);
        $this->assertCount(1, YouTubeParser::parseSearch($this->fixture('youtube_search.html'), 1));
        Carbon::setTestNow();
    }

    public function test_parses_watch_page(): void
    {
        $v = YouTubeParser::parseWatch($this->fixture('youtube_watch.html'));
        $this->assertSame('dQw4w9WgXcQ', $v['videoId']);
        $this->assertSame(213, $v['duration']);
        $this->assertSame('UCuAXFkgsw1L7xaCfnd5JJOw', $v['channelId']);
        $this->assertSame('https://yt3.ggpht.com/rick=s176', $v['channelAvatar']);
        $this->assertSame('2009-10-25', $v['publish_time']->utc()->format('Y-m-d'));
        $this->assertNull(YouTubeParser::parseWatch('<html>consent</html>'));
    }

    public function test_parses_rss_feed(): void
    {
        $f = YouTubeParser::parseFeed($this->fixture('youtube_feed.xml'));
        $this->assertSame('MrBeast', $f['name']);
        $this->assertCount(2, $f['videos']);
        $this->assertSame('RE3_PGwDwhw', $f['videos'][0]['videoId']);
        $this->assertSame('Will this couple', $f['videos'][0]['description']);
        $this->assertNull(YouTubeParser::parseFeed('not xml'));
    }
}
