<?php

namespace Tests\Unit;

use App\Helpers\ChristianPicks;
use PHPUnit\Framework\TestCase;

class ChristianPicksTest extends TestCase
{
    public function test_recognises_christian_content(): void
    {
        $this->assertTrue(ChristianPicks::isChristian('Sunday Sermon: Romans 8', null));
        $this->assertTrue(ChristianPicks::isChristian('Live set', 'Hillsong United worship night'));
        $this->assertTrue(ChristianPicks::isChristian('Amazing Grace', null, 'Gospel Choir'));
        $this->assertTrue(ChristianPicks::isChristian('Book of John study', 'Bible teaching'));
    }

    public function test_rejects_other_content(): void
    {
        $this->assertFalse(ChristianPicks::isChristian('Bitcoin price prediction', 'crypto news'));
        $this->assertFalse(ChristianPicks::isChristian('Top 10 gaming moments', null, 'GamerChannel'));
        $this->assertFalse(ChristianPicks::isChristian('Godzilla trailer', 'monster movie'));
    }

    public function test_has_youtube_and_rumble_queries(): void
    {
        $this->assertGreaterThanOrEqual(8, count(ChristianPicks::QUERIES));
    }
}
