<?php

namespace App\Helpers;

use App\Models\Category;
use App\Models\CreatorModels\Creator;
use App\Models\VideoModels\Video;
use Illuminate\Support\Str;

/**
 * Page specific title, description and share image. Passed to Inertia as the
 * "meta" prop and rendered as meta, Open Graph and Twitter tags by app.blade.php.
 */
class Seo
{
    public static function make(?string $title, ?string $description = null, ?string $image = null, string $type = 'website'): array
    {
        return [
            'title' => $title,
            'description' => $description ? Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($description))), 200) : null,
            'image' => $image ?: null,
            'type' => $type,
        ];
    }

    public static function video(string $slug): array
    {
        $video = Video::where('slug', $slug)->first(['title', 'description', 'thumbnail_url']);
        if (!$video) {
            return self::make('Watch');
        }
        return self::make(
            $video->title,
            $video->description ?: "Watch {$video->title} on VidGaze.",
            $video->thumbnail_url,
            'video.other'
        );
    }

    public static function creator(string $slug): array
    {
        $creator = Creator::where('slug', $slug)->first(['name', 'bio', 'avatar_url']);
        if (!$creator) {
            return self::make('Channel');
        }
        return self::make(
            $creator->name,
            $creator->bio ?: "Watch the latest videos from {$creator->name} on VidGaze.",
            $creator->avatar_url,
            'profile'
        );
    }

    public static function category(string $slug): array
    {
        $category = Category::where('slug', $slug)->first(['name', 'description']);
        $name = $category?->name ?? Str::headline($slug);
        return self::make($name, $category?->description ?: "The latest {$name} videos from YouTube, Rumble and more, on VidGaze.");
    }
}
