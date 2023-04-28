<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\videoReport;
use Illuminate\Support\Facades\Auth;

class VideoReportController extends Controller
{
    private function getVideoAndReport($videoId)
    {
        $video = Video::findOrFail($videoId);

        if (!$video) {
            return response()->json([
                'error' => 'Video not found'
            ], 404);
        }

        $report = VideoReport::where([
            'creator_id' => Auth::user()->creator->id,
            'video_id' => $video->id,
        ])->first();

        return [$video, $report];
    }

    public function create(Request $request, $videoId)
    {
        [$video, $report] = $this->getVideoAndReport($videoId);

        if ($report) {
            return response()->json([
                'error' => 'You have already reported this video'
            ], 200);
        }

        $report = VideoReport::create([
            'creator_id' => Auth::user()->creator->id,
            'video_id' => $video->id,
        ]);

        $report->save();
    }

    public function destroy(Request $request, $videoId)
    {
        [$video, $report] = $this->getVideoAndReport($videoId);

        if (!$report) {
            return response()->json([
                'error' => 'You have not reported this video'
            ], 200);
        }

        $report->delete();
    }
}
