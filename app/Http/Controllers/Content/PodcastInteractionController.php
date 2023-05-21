<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\PodcastModels\Podcast;
use App\Models\PodcastModels\PodcastInteraction;
use Illuminate\Support\Facades\Auth;

class PodcastInteractionController extends Controller
{

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

        return [$podcast, $interaction];
    }

    public function toggleLike($podcastId)
    {
        [$podcast, $interaction] = $this->getPodcastAndInteraction($podcastId);

        if (!isset(Auth::user()->creator)) {
            return response()->json(['message' => 'You are not authenticated.'], 401);
        }


        //if they change their rating from dislike to like
        $message = 'Liked successfully';


        if ($interaction->liked != 'like') {
            $interaction->liked = 'like';
            $type = 'normal';
            $podcast->like_count++;
        } else {
            // Have they already liked it
            $interaction->liked = null;
            $type = 'undo';
            $podcast->like_count--;
            $message = 'Like removed successfully';
        }

        $interaction->save();
        $podcast->save();
        return response()->json([
            'message' => $message,
            'type' => $type,
            'result' => $interaction->liked,
        ], 200);
    }

    public function getInteraction($podcastId)
    {
        [$podcast, $interaction] = $this->getPodcastAndInteraction($podcastId);

        if (!isset(Auth::user()->creator)) {
            return response()->json(['message' => 'You are not authenticated.'], 401);
        }

        return response()->json([
            'liked' => $interaction->liked,
            'episode_id' => $podcast->id,
            'reported' => $interaction->reported,
            'disinterest' => $interaction->disinterest,
        ], 200);
    }

}
