<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoDisinterest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoDisinterestController extends Controller
{
    private function getVideoAndDisinterest($videoId)
    {
        $video = Video::findOrFail($videoId);

        if (!$video) {
            return response()->json([
                'error' => 'Video not found'
            ], 404);
        }

        $disinterest = VideoDisinterest::where([
            'creator_id' => Auth::user()->creator->id,
            'video_id' => $video->id,
        ])->first();

        return [$video, $disinterest];
    }

    public function create(Request $request, $videoId)
    {
        [$video, $disinterest] = $this->getVideoAndDisinterest($videoId);

        if ($disinterest) {
            return response()->json([
                'error' => 'You have already disinterested this video'
            ], 400);
        }

        $disinterest = VideoDisinterest::create([
            'creator_id' => Auth::user()->creator->id,
            'video_id' => $video->id,
        ]);

        $disinterest->save();
    }

    public function destroy(Request $request, $videoId)
    {
        [$video, $disinterest] = $this->getVideoAndDisinterest($videoId);

        if (!$disinterest) {
            return response()->json([
                'error' => 'You have not disinterested this video'
            ], 400);
        }

        $disinterest->delete();
    }
}
