<?php

namespace Tests\Feature;

use App\Models\SiteEvent;
use App\Support\Stats\PublicStats;
use App\Support\Stats\SiteEvents;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicStatsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('email'); $t->string('first_name')->nullable(); $t->timestamps();
        });
        Schema::create('categories', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->timestamps();
        });
        Schema::create('creators', function (Blueprint $t) {
            $t->id(); $t->string('slug'); $t->string('name'); $t->unsignedBigInteger('user_id')->nullable(); $t->timestamps();
        });
        Schema::create('videos', function (Blueprint $t) {
            $t->id(); $t->string('slug'); $t->string('title'); $t->unsignedBigInteger('creator_id');
            $t->string('preferred_source'); $t->unsignedBigInteger('category_id')->nullable();
            $t->string('visibility')->default('public'); $t->integer('view_count')->default(0);
            $t->timestamp('time_published')->nullable(); $t->timestamps();
        });
        Schema::create('video_interactions', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('video_id'); $t->string('liked')->nullable(); $t->timestamps();
        });
        Schema::create('comments', function (Blueprint $t) {
            $t->id(); $t->string('body'); $t->timestamps();
        });
        (include base_path('database/migrations/2026_10_22_200000_create_site_events_table.php'))->up();

        $now = Carbon::now();
        DB::table('users')->insert(['email' => 'secret.person@example.com', 'first_name' => 'Secretname', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('categories')->insert(['id' => 1, 'name' => 'Gaming']);
        DB::table('creators')->insert([
            ['id' => 1, 'slug' => 'public-channel', 'name' => 'Public Channel', 'user_id' => null],
            ['id' => 2, 'slug' => 'private-person', 'name' => 'Private Person', 'user_id' => 1],
        ]);
        DB::table('videos')->insert([
            ['id' => 1, 'slug' => 'a', 'title' => 'Video A', 'creator_id' => 1, 'preferred_source' => 'youtube', 'category_id' => 1, 'view_count' => 5, 'time_published' => $now, 'created_at' => $now],
            ['id' => 2, 'slug' => 'b', 'title' => 'Video B', 'creator_id' => 1, 'preferred_source' => 'rumble', 'category_id' => null, 'view_count' => 2, 'time_published' => $now, 'created_at' => $now],
            ['id' => 3, 'slug' => 'c', 'title' => 'Private upload', 'creator_id' => 2, 'preferred_source' => 'vidgaze', 'category_id' => null, 'view_count' => 0, 'time_published' => $now, 'created_at' => $now],
        ]);
        DB::table('video_interactions')->insert(['video_id' => 1, 'liked' => 'like', 'created_at' => $now, 'updated_at' => $now]);

        foreach ([1, 1, 2, 3, 3, 3] as $id) {
            SiteEvent::create(['type' => SiteEvent::WATCH, 'page' => 'video', 'video_id' => $id]);
        }
        SiteEvent::create(['type' => SiteEvent::PAGE_VIEW, 'page' => 'home']);
        foreach (['minecraft', 'minecraft', 'minecraft', 'rare term'] as $term) {
            SiteEvent::create(['type' => SiteEvent::SEARCH, 'page' => 'search', 'term' => $term]);
        }
    }

    public function test_builds_aggregate_stats(): void
    {
        $s = (new PublicStats())->build();

        $this->assertSame(3, $s['totals']['videos']);
        $this->assertSame(1, $s['totals']['channels']);
        $this->assertSame(1, $s['totals']['users']);
        $this->assertSame(7, $s['totals']['playerViews']);
        $this->assertSame(1, $s['totals']['likes']);
        $this->assertSame(6, $s['totals']['watches']);
        $this->assertSame(4, $s['totals']['searches']);
        $this->assertSame(1, $s['live']['day']['pageViews']);
        $this->assertSame(1, $s['live']['week']['signups']);
        $this->assertCount(PublicStats::DAYS, $s['daily']['dates']);
        $this->assertSame(3, array_sum($s['daily']['newVideos']));
        $this->assertSame(3, array_sum($s['publishedByMonth']['counts']));
        $this->assertSame('YouTube', collect($s['videosByPlatform'])->firstWhere('label', 'YouTube')['label']);
        $this->assertNotNull(collect($s['videosByCategory'])->firstWhere('label', 'Not categorised yet'));
        $this->assertSame(['label' => 'Video A', 'slug' => 'a', 'channel' => 'Public Channel', 'count' => 2], $s['mostWatchedVideos'][0]);
        $this->assertSame([['label' => 'minecraft', 'count' => 3]], $s['topSearches']);
    }

    public function test_never_exposes_private_users_or_personal_data(): void
    {
        $json = json_encode((new PublicStats())->build());

        $this->assertStringNotContainsString('secret.person', $json);
        $this->assertStringNotContainsString('Secretname', $json);
        $this->assertStringNotContainsString('Private Person', $json);
        $this->assertStringNotContainsString('Private upload', $json);
        $this->assertStringNotContainsString('rare term', $json);
    }

    public function test_stats_are_cached(): void
    {
        Cache::flush();
        $first = PublicStats::cached();
        SiteEvent::create(['type' => SiteEvent::PAGE_VIEW, 'page' => 'home']);
        $this->assertSame($first['totals']['pageViews'], PublicStats::cached()['totals']['pageViews']);
        $this->assertGreaterThanOrEqual(300, PublicStats::CACHE_SECONDS);
        $this->assertLessThanOrEqual(900, PublicStats::CACHE_SECONDS);
    }

    public function test_search_terms_are_cleaned(): void
    {
        $this->assertSame('lofi beats', SiteEvents::cleanTerm('  LoFi   Beats '));
        $this->assertNull(SiteEvents::cleanTerm('me@example.com'));
        $this->assertNull(SiteEvents::cleanTerm('https://youtube.com/watch?v=x'));
        $this->assertNull(SiteEvents::cleanTerm('call 07700 900123'));
        $this->assertNull(SiteEvents::cleanTerm('@someone'));
        $this->assertNull(SiteEvents::cleanTerm('one two three four five six seven'));
        $this->assertNull(SiteEvents::cleanTerm('x'));
    }

    public function test_site_events_store_no_personal_columns(): void
    {
        $cols = Schema::getColumnListing('site_events');
        sort($cols);
        $this->assertSame(['created_at', 'id', 'page', 'term', 'type', 'video_id'], $cols);
    }

    public function test_bots_are_not_counted(): void
    {
        $this->assertTrue(SiteEvents::isBot(\Illuminate\Http\Request::create('/', 'GET', server: ['HTTP_USER_AGENT' => 'Googlebot/2.1'])));
        $this->assertFalse(SiteEvents::isBot(\Illuminate\Http\Request::create('/', 'GET', server: ['HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh) Safari/605'])));
    }

    public function test_stats_route_renders_page(): void
    {
        $this->assertTrue(Route::has('stats'));
        $this->withoutVite();
        $views = sys_get_temp_dir() . '/vidgaze-test-views';
        @mkdir($views, 0777, true);
        config(['view.compiled' => $views]);
        $this->get('/stats', ['User-Agent' => 'Mozilla/5.0 Safari'])
            ->assertOk()
            ->assertSee('Stats\/Stats', false);
    }
}
