<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\StreamInteractionResource;
use App\Models\CreatorModels\CreatorInteraction;
use App\Models\StreamModels\Stream;
use App\Models\StreamModels\StreamInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StreamInteractionApiController extends Controller
{

    /** get stream and interaction
     * @param $stream_id
     * @return array
     */
    private function getStreamAndInteraction($stream_id)
    {
        $stream = Stream::findOrFail($stream_id);

        if (!$stream) {
            return null;
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



    /** get stream interaction
     * @param $stream_id
     * @return JsonResponse
     */
    public function getStreamInteraction($stream_id) {

        $creatorId = Auth::user()->creator->id;
        // check if user has liked video
        $VideoViewInfo = StreamInteraction::where('viewer_id', $creatorId)->where('stream_id', $stream_id)->first() ?? null;

        $VideoViewInfo = new StreamInteractionResource($VideoViewInfo);

        return response()->json([
            'interaction' => $VideoViewInfo,
        ]);
    }

    /** toggle stream report
     * @param $stream_id
     * @return JsonResponse
     */
    public function toggleReport($stream_id)
    {

        [$stream, $interaction] = $this->getStreamAndInteraction($stream_id);

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
            'toastType' => $type,
        ]);
    }

    /** toggle stream disinterest
     * @param $stream_id
     * @return JsonResponse
     */
    public function toggleDisinterest($stream_id)
    {
        [$stream, $interaction] = $this->getStreamAndInteraction($stream_id);


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
            'toastType' => $type,
            'message' => $message,
        ]);
    }


    /** get stream interaction details
     * @param $stream_id
     * @return JsonResponse
     */
    public function modalDetails($stream_id)
    {
        [$stream, $interaction] = $this->getStreamAndInteraction($stream_id);

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
