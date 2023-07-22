<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\PodcastModels\Podcast;
use App\Models\PodcastModels\PodcastInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PodcastInteractionApiController extends Controller
{

    /** get the podcast and interaction
     * @param $podcastId
     * @return JsonResponse
     */
    private function getPodcastAndInteraction($podcastId)
    {
        $podcast = Podcast::findOrFail($podcastId);

        if (!$podcast) {
            return response()->json([
                'error' => 'Podcast not found'
            ], 404);
        }

        $creatorId = Auth::user()->creator->id;

        $interaction = PodcastInteraction::where([
            'viewer_id' => $creatorId,
            'podcast_id' => $podcast->id,
        ])->first();

        if (!$interaction) {
            $interaction = new PodcastInteraction();
            $interaction->viewer_id = $creatorId;
            $interaction->podcast_id = $podcast->id;
            $interaction->save();
        }

        return response()->json([$podcast, $interaction]);
    }

    /** toggle the like
     * @param $podcastId
     * @return JsonResponse
     */
    public function toggleLike($podcastId)
    {
        [$podcast, $interaction] = $this->getPodcastAndInteraction($podcastId);


        //if they change their rating from dislike to like
        $message = 'Following ' . $podcast->title . ' podcast';


        if ($interaction->liked != 'like') {
            $interaction->liked = 'like';
            $type = 'normal';
            $podcast->like_count++;
        } else {
            // Have they already liked it
            $interaction->liked = null;
            $type = 'undo';
            $podcast->like_count--;
            $message = 'Follow removed successfully';
        }

        $interaction->save();
        $podcast->save();
        return response()->json([
            'toastMessage' => $message,
            'toastType' => $type,
            'result' => $interaction->liked,
        ], 200);
    }

    /** toggle the dislike
     * @param $podcastId
     * @return JsonResponse
     */
    public function getInteraction($podcastId)
    {
        [$podcast, $interaction] = $this->getPodcastAndInteraction($podcastId);

        if (!isset(Auth::user()->creator)) {
            return response()->json(['error' => 'You are not authenticated.'], 401);
        }

        return response()->json([
            'liked' => $interaction->liked,
            'episode_id' => $podcast->id,
            'reported' => $interaction->reported,
            'disinterest' => $interaction->disinterest,
        ], 200);
    }

}
