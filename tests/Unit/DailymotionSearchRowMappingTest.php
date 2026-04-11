<?php

namespace Tests\Unit;

use App\Helpers\PlatformAPIs\Dailymotion;
use Carbon\Carbon;
use Tests\TestCase;

class DailymotionSearchRowMappingTest extends TestCase
{
    private function baseRow(): array
    {
        return [
            'id' => 'x8test1',
            'title' => 'Sample video',
            'description' => 'Desc',
            'thumbnail_720_url' => 'https://dmcdn.net/thumb.jpg',
            'duration' => 120,
            'views_total' => 100,
            'likes_total' => 5,
            'created_time' => Carbon::parse('2026-01-10 12:00:00')->timestamp,
            'channel' => 'music',
            'channel.name' => 'Music category',
        ];
    }

    public function test_owner_fallback_sets_creator_id_name_avatar_and_banner_when_claimer_empty(): void
    {
        $row = $this->baseRow();
        $row['claimer.id'] = null;
        $row['owner.id'] = 'x2owner9';
        $row['owner.screenname'] = 'Display Owner';
        $row['owner.username'] = 'user99';
        $row['owner.avatar_720_url'] = 'https://dmcdn.net/avatar720.jpg';
        $row['owner.cover_url'] = 'https://dmcdn.net/cover.jpg';

        $dto = Dailymotion::resultDtoFromFlattenedVideoSearchRow($row);

        $this->assertSame('x2owner9', $dto->creator->id);
        $this->assertSame('Display Owner', $dto->creator->name);
        $this->assertSame('https://dmcdn.net/avatar720.jpg', $dto->creator->avatar_url);
        $this->assertSame('https://dmcdn.net/cover.jpg', $dto->creator->banner_url);
        $this->assertSame('x2owner9', $dto->content->creator_id);
    }

    public function test_does_not_use_category_channel_slug_as_creator_id_when_owner_present(): void
    {
        $row = $this->baseRow();
        $row['claimer.id'] = null;
        $row['owner.id'] = 'x5realuser';
        $row['owner.screenname'] = 'Real';
        $row['owner.avatar_480_url'] = 'https://dmcdn.net/a480.jpg';
        $row['channel'] = 'music';

        $dto = Dailymotion::resultDtoFromFlattenedVideoSearchRow($row);

        $this->assertSame('x5realuser', $dto->creator->id);
        $this->assertNotSame('music', $dto->creator->id);
        $this->assertSame('https://dmcdn.net/a480.jpg', $dto->creator->avatar_url);
    }

    public function test_claimer_avatar_and_cover_take_precedence_over_owner(): void
    {
        $row = $this->baseRow();
        $row['claimer.id'] = 'xc1';
        $row['claimer.screenname'] = 'Claim Co';
        $row['claimer.avatar_720_url'] = 'https://dmcdn.net/c-av.jpg';
        $row['claimer.cover_url'] = 'https://dmcdn.net/c-cover.jpg';
        $row['owner.id'] = 'xo1';
        $row['owner.avatar_720_url'] = 'https://dmcdn.net/o-av.jpg';
        $row['owner.cover_url'] = 'https://dmcdn.net/o-cover.jpg';

        $dto = Dailymotion::resultDtoFromFlattenedVideoSearchRow($row);

        $this->assertSame('xc1', $dto->creator->id);
        $this->assertSame('Claim Co', $dto->creator->name);
        $this->assertSame('https://dmcdn.net/c-av.jpg', $dto->creator->avatar_url);
        $this->assertSame('https://dmcdn.net/c-cover.jpg', $dto->creator->banner_url);
    }

    public function test_falls_back_to_channel_slug_only_when_no_claimer_or_owner_id(): void
    {
        $row = $this->baseRow();
        $row['claimer.id'] = null;
        $row['owner.id'] = null;
        $row['channel'] = 'sport';

        $dto = Dailymotion::resultDtoFromFlattenedVideoSearchRow($row);

        $this->assertSame('sport', $dto->creator->id);
    }
}
