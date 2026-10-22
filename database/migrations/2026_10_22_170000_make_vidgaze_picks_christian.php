<?php

use App\Helpers\ChristianPicks;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * VidGaze Picks becomes the Christian row. Retag it and release any video in
 * it that is not Christian content so the AI can place it elsewhere.
 */
return new class extends Migration
{
    public function up(): void
    {
        $category = DB::table('categories')->where('slug', ChristianPicks::SLUG)->first();
        if (!$category) {
            return;
        }

        DB::table('categories')->where('id', $category->id)->update([
            'tags_json' => json_encode(ChristianPicks::TAGS),
            'updated_at' => now(),
        ]);

        DB::table('videos')->where('category_id', $category->id)->orderBy('id')
            ->select(['id', 'title', 'description'])
            ->chunk(500, function ($videos) {
                foreach ($videos as $video) {
                    if (!ChristianPicks::isChristian($video->title, $video->description)) {
                        DB::table('videos')->where('id', $video->id)
                            ->update(['category_id' => null, 'categorised' => false]);
                    }
                }
            });
    }

    public function down(): void
    {
    }
};
