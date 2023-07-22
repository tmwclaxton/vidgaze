<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CreatorCollection;
use App\Http\Resources\StreamCollection;
use App\Http\Resources\VideoCollection;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorInteraction;
use App\Models\StreamModels\Stream;
use App\Models\VideoModels\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CreatorInteractionApiController extends Controller
{

    /** get user's subscription feed
     * @return JsonResponse
     */
    public function getSubscriptionFeed()
    {
        $subscriptions = Auth::user()->creator->subscriptions;

        $creator_ids = $subscriptions->map(function ($subscription) {
            return collect($subscription)->only(['id']);
        });

        $videos = Video::whereIn('creator_id', $creator_ids)
            ->whereDate('time_published', '>=', now()->subDays(28)->setTime(0, 0, 0)->toDateTimeString())
            ->orderBy('time_published','DESC')
            ->get();

        $streams = Stream::whereIn('creator_id', $creator_ids)->orderBy('viewers')->take(5)->get();

        $subscriptions = new CreatorCollection($subscriptions);
        $videos = new VideoCollection($videos);
        $streams = new StreamCollection($streams);

        return response()->json([
            'subscriptions' => $subscriptions,
            'videos' => $videos,
            'streams' => $streams,
            'podcasts' => [],
        ]);
    }

    /** get the creators that the user is subscribed to
     * @param Request $request
     * @return JsonResponse
     */
    public function getSubscriptions(Request $request)
    {
        // grab category from request
        $category = $request->input('category');

        $subscriptions = Auth::user()->creator->subscriptions;


        if ($category === 'default') {
            $subscriptions = $subscriptions->sortBy('name');
        } elseif ($category === 'az') {
            $subscriptions = $subscriptions->sortBy('name');
        } elseif ($category === 'za') {
            $subscriptions = $subscriptions->sortByDesc('name');
        } elseif ($category === 'newest') {
            $subscriptions = $subscriptions->sortByDesc('created_at');
        } elseif ($category === 'oldest') {
            $subscriptions = $subscriptions->sortBy('created_at');
        }
        $subscriptions = new CreatorCollection($subscriptions->values());

        return response()->json([
            'subscriptions' => $subscriptions,
        ]);
    }


    /** get the creators that the user is subscribed to
     * @param int $channelId
     * @return JsonResponse
     */
    private function getChannelAndInteraction($channelId)
    {
        $channel = Creator::findOrFail($channelId);

        if (!$channel) {
            return response()->json([
                'error' => 'Channel not found'
            ], 404);
        }

        $creatorId = Auth::user()->creator->id;

        $interaction = CreatorInteraction::where([
            'viewer_id' => $creatorId,
            'creator_id' => $channel->id,
        ])->first();

        if (!$interaction) {
            $interaction = new CreatorInteraction();
            $interaction->viewer_id = $creatorId;
            $interaction->creator_id = $channel->id;
            $interaction->save();
        }

        return response()->json([$channel, $interaction]);
    }

    /** toggle subscription to a creator
     * @param int $channelId
     * @return JsonResponse
     */

    public function toggleSubscription($channelId)
    {
        [$creator, $interaction] = $this->getChannelAndInteraction($channelId);

        if(Auth::user()->creator->slug === $creator->slug) { //check your not subscribing to yourself
            return response()->json(['error', 'You cannot subscribe to yourself']);
        }

        $interaction->subscribed = !$interaction->subscribed;
        if ($interaction->subscribed) {
            $creator->subscriber_count++;
            $message = 'You have subscribed to ' . $creator->name;
            $type = 'normal';
        } else {
            $creator->subscriber_count--;
            $message = 'You have unsubscribed from ' . $creator->name;
            $type = 'undo';
        }
        $creator->save();
        $interaction->save();

        return response()->json([
            'message' => $message,
            'type' => $type,
            'subscribed' => (bool) $interaction->subscribed,
        ]);
    }

    /** toggle disinterest to a creator
     * @param int $channelId
     * @return JsonResponse
     */
    public function toggleDisinterest($channelId) {
        [$creator, $interaction] = $this->getChannelAndInteraction($channelId);
        if(Auth::user()->creator->slug === $creator->slug) { //check your not disinterest yourself
            return response()->json(['error', 'You cannot disinterest yourself']);
        }

        $interaction->disinterested = !$interaction->disinterested;
        if ($interaction->disinterested) { //check your not disinterest twice
            //$creator->disinterested_count++;
            $message = 'We will show you less of this channel in the future.';
            $type = 'normal';
        } else {
            //$creator->disinterested_count--;
            $message = 'We will show you more of this channel in the future.';
            $type = 'undo';
        }
        $creator->save();
        $interaction->save();
        return response()->json([
            'message' => $message,
            'type' => $type,
        ]);

    }

    /** toggle report to a creator
     * @param int $channelId
     * @return JsonResponse
     */
    public function toggleReport($channelId) {
        [$creator, $interaction] = $this->getChannelAndInteraction($channelId);
        if(Auth::user()->creator->slug === $creator->slug) { //check your not reporting yourself
            return response()->json(['error', 'You cannot report yourself']);
        }
        $interaction->reported = !$interaction->reported;
        if ($interaction->reported) { //check your not reporting twice
            //$creator->report_count++;
            $type = 'normal';
            $message = 'Thank you for reporting this creator. We will review it as soon as possible.';
        } else {
            //$creator->report_count--;
            $type = 'undo';
            $message = 'Thank you for removing your report.';
        }
        $creator->save();
        $interaction->save();
        return response()->json([
            'message' => $message,
            'type' => $type,
        ]);
    }
}
