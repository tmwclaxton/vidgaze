<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\VideoModels\Video;
use Illuminate\Http\Request;

class PinController extends Controller
{

    public function getPinStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        // grab video id

        $request->validate([
            'video_id' => 'required|integer|exists:videos,id'
        ]);

        $video = Video::find($request->video_id);

        return response()->json([
            'category_id' => $video->category_id,
            'pinned' => $video->pinned,
            'pin_duration' => $video->pin_expires_at ? now()->diffInSeconds($video->pin_expires_at) : null
        ]);
    }

    public function pinVideo(Request $request): \Illuminate\Http\JsonResponse
    {
        // grab video id, category id, duration

        $request->validate([
            'video_id' => 'required|integer|exists:videos,id',
            'duration' => 'required|integer'
        ]);

        $video = Video::find($request->video_id);

        // set pinned to true, set pin_expires_at to current time + duration
        $video->pinned = true;
        $video->pin_expires_at = now()->addSeconds($request->duration);

        $video->save();

        return response()->json([
            'message' => 'Video pinned successfully'
        ]);
    }

    public function unpinVideo(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'video_id' => 'required|integer|exists:videos,id',
        ]);

        $video = Video::find($request->video_id);

        // set pinned to false, set pin_expires_at to null
        $video->pinned = false;
        $video->pin_expires_at = null;

        $video->save();

        return response()->json([
            'message' => 'Video unpinned successfully'
        ]);
    }


    public function resetPinStatuses(): \Illuminate\Http\JsonResponse
    {
        // set all videos where pin_expires_at is in the past to pinned = false, pin_expires_at = null
        Video::where('pin_expires_at', '<', now())->update([
            'pinned' => false,
            'pin_expires_at' => null
        ]);

        return response()->json([
            'message' => 'Pin statuses reset successfully'
        ]);
    }
}
