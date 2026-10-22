<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The home page asks /api/v1/video/pinned for these category slugs. Production
 * only had the country categories, so every home row returned 422.
 *
 * Idempotent: a category is only inserted when its slug is missing, and an
 * existing row is never overwritten. Safe to run again.
 */
return new class extends Migration
{
    public const CATEGORIES = [
        'vidgaze_picks' => [
            'name' => 'VidGaze Picks',
            'tags' => ['co-operative', 'independent media', 'free speech', 'unions'],
        ],
        'music' => [
            'name' => 'Music',
            'youtube_category_id' => '10',
            'dailymotion_category_id' => 'music',
            'tags' => ['music', 'live music', 'new releases', 'concerts'],
        ],
        'crypto_currency' => [
            'name' => 'Crypto Currency',
            'tags' => ['crypto', 'bitcoin', 'monero', 'ethereum', 'blockchain'],
        ],
        'gaming' => [
            'name' => 'Gaming',
            'youtube_category_id' => '20',
            'dailymotion_category_id' => 'videogames',
            'tags' => ['gaming', 'video games', 'esports', 'game reviews'],
        ],
        'alternate_news' => [
            'name' => 'Alternate News',
            'tags' => ['independent news', 'citizen journalism', 'commentary', 'politics'],
        ],
    ];

    public function up(): void
    {
        $now = now();
        $added = 0;

        foreach (self::CATEGORIES as $slug => $data) {
            if (DB::table('categories')->where('slug', $slug)->exists()) {
                continue;
            }

            DB::table('categories')->insert([
                'slug' => $slug,
                'name' => $data['name'],
                'youtube_category_id' => $data['youtube_category_id'] ?? null,
                'dailymotion_category_id' => $data['dailymotion_category_id'] ?? null,
                'tags_json' => json_encode($data['tags']),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $added++;
        }

        // Videos the AI could not place (no matching category existed) get
        // another pass by app:categorise-videos now that these categories exist.
        if ($added > 0) {
            DB::table('videos')
                ->whereNull('category_id')
                ->where('categorised', true)
                ->update(['categorised' => false]);
        }
    }

    public function down(): void
    {
        // Leave the categories in place: videos may already reference them.
    }
};
