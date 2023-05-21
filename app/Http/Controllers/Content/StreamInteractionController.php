<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\CreatorModels\CreatorInteraction;
use App\Models\StreamModels\Stream;
use App\Models\StreamModels\StreamInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StreamInteractionController extends Controller
{

    public function getStreamInteraction($streamId) {
        // check if user is authenticated
        if (!Auth::user()) {
            return response()->json([
                'error' => 'You are not authenticated'
            ], 401);
        }

        $creatorId = Auth::user()->creator->id;
        // check if user has liked video
        $VideoViewInfo = StreamInteraction::where('viewer_id', $creatorId)->where('stream_id', $streamId)->first() ?? null;
        return [
            'reported' => $VideoViewInfo['reported'] ?? null,
            'disinterested' => $VideoViewInfo['disinterested'] ?? null,
        ];
    }

    public function toggleReport(Request $request, $streamId)
    {

        [$stream, $interaction] = $this->getStreamAndInteraction($streamId);
        $interaction->reported = !$interaction->reported;
        if ($interaction->reported) {
            $stream->increment('report_count');
            $type = 'normal';
            $message = 'Thank you for reporting this stream. We will review it as soon as possible.';
        } else {
            $stream->decrement('report_count');
            $type = 'undo';
            $message = 'Report removed successfully.';
        }
        $interaction->save();

        return response()->json([
            'message' => $message,
            'type' => $type,
        ]);
    }
    public function toggleDisinterest(Request $request, $streamId)
    {
        [$stream, $interaction] = $this->getStreamAndInteraction($streamId);
        $interaction->disinterested = !$interaction->disinterested;
        $interaction->save();
        if ($interaction->disinterested) {
            $type = 'normal';
            $message = 'Got it, we will show you less streams like this';
        } else {
            $type = 'undo';
            $message =  'Got it, we will show you more streams like this';
        }
        return response()->json([
            'type' => $type,
            'message' => $message,
        ]);
    }
    private function getStreamAndInteraction($streamId)
    {
        $stream = Stream::findOrFail($streamId);

        if (!$stream) {
            return response()->json([
                'error' => 'Stream not found'
            ], 404);
        }

        $creatorId = Auth::user()->creator->id;

        $interaction = StreamInteraction::where([
            'viewer_id' => $creatorId,
            'stream_id' => $stream->id,
        ])->first();

        if (!$interaction) {
            $interaction = new StreamInteraction();
            $interaction->viewer_id = $creatorId;
            $interaction->stream_id = $stream->id;
            $interaction->save();
        }

        return [$stream, $interaction];
    }

    public function modalDetails($streamId)
    {
        [$stream, $interaction] = $this->getStreamAndInteraction($streamId);


        $creatorId = Auth::user()->creator->id;



        // check if user has disinterested video
        $streamDisinterested = (bool)$interaction->disinterested;
        $streamReported = (bool)$interaction->reported;

        // check if user has disinterested channel
        $channelDisinterested = CreatorInteraction::where('viewer_id', $creatorId)->where('creator_id', $stream->creator->id)->where('disinterested', '=', true)->exists();



        return response()->json([
            'streamDisinterest' => $streamDisinterested,
            'reportedContent' => $streamReported,
            'channelDisinterest' => $channelDisinterested,
        ], 200);
    }


}
