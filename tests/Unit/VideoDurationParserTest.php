<?php

namespace Tests\Unit;

use App\Helpers\VideoDurationParser;
use PHPUnit\Framework\TestCase;

class VideoDurationParserTest extends TestCase
{
    public function test_scraper_row_prefers_string_duration_over_zero_duration_ms(): void
    {
        $sec = VideoDurationParser::secondsFromScraperRow([
            'durationMs' => 0,
            'duration' => '13:48',
        ]);
        $this->assertSame(13 * 60 + 48, $sec);
    }

    public function test_scraper_row_apify_style_examples(): void
    {
        $this->assertSame(29 * 60 + 54, VideoDurationParser::secondsFromScraperRow(['duration' => '29:54']));
        $this->assertSame(3 * 60 + 17, VideoDurationParser::secondsFromScraperRow(['duration' => '00:03:17']));
        $this->assertSame(3600 + 23 * 60 + 37, VideoDurationParser::secondsFromScraperRow(['duration' => '1:23:37']));
    }

    public function test_explicit_milliseconds_field(): void
    {
        $this->assertSame(125, VideoDurationParser::secondsFromScraperRow(['durationMs' => 125_000]));
    }

    public function test_explicit_length_seconds(): void
    {
        $this->assertSame(90, VideoDurationParser::secondsFromScraperRow(['lengthSeconds' => 90]));
    }

    public function test_iso8601_pt(): void
    {
        $this->assertSame(3 * 60 + 26, VideoDurationParser::secondsFromDisplayString('PT3M26S'));
    }

    public function test_mixed_numeric_string(): void
    {
        $this->assertSame(245, VideoDurationParser::secondsFromMixed('245'));
    }
}
