<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorInteraction;
use App\Models\StreamModels\Stream;
use App\Models\VideoModels\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CreatorInteractionController extends Controller
{
    public function index()
    {
        return Inertia::render('Viewer/Feed/Subscriptions/SubscriptionsIndex');
    }

    public function getSubscriptionFeed()
    {
        $subscriptions = Auth::user()->creator->subscriptions;

        $creator_ids = $subscriptions->map(function ($subscription) {
            return collect($subscription)->only(['id']);
        });

        return [
            'subscriptions' => $subscriptions,
            'videos' => Video::whereIn('creator_id', $creator_ids)
                ->whereDate('time_published', '>=', now()->subDays(5)->setTime(0, 0, 0)->toDateTimeString())
                ->orderBy('time_published','DESC')
                ->get(),
            'streams' => Stream::whereIn('creator_id', $creator_ids)->orderBy('viewers')->take(5)->get(),
        ];
    }

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

        return [$channel, $interaction];
    }


    public function toggleSubscription($channelId)
    {
        [$creator, $interaction] = $this->getChannelAndInteraction($channelId);

        if(Auth::user()->creator->slug === $creator->slug) { //check your not subscribing to yourself
            return response(['error', 'You cannot subscribe to yourself']);
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

        return response([
            'message' => $message,
            'type' => $type,
            'subscribed' => (bool) $interaction->subscribed,
        ]);
    }

    public function toggleDisinterest($channelId) {
        [$creator, $interaction] = $this->getChannelAndInteraction($channelId);
        if(Auth::user()->creator->slug === $creator->slug) { //check your not disinterest yourself
            return response(['error', 'You cannot disinterest yourself']);
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
        return response([
            'message' => $message,
            'type' => $type,
        ]);

    }

    public function toggleReport($channelId) {
        [$creator, $interaction] = $this->getChannelAndInteraction($channelId);
        if(Auth::user()->creator->slug === $creator->slug) { //check your not reporting yourself
            return response(['error', 'You cannot report yourself']);
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
        return response([
            'message' => $message,
            'type' => $type,
        ]);
    }
}
