<?php

namespace App\Http\Controllers\Content;


use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Models\Stream;
use App\Models\Subscription;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;

class SubscriptionsController extends Controller
{

    public function index()
    {
        $subscriptions = Auth::user()->creator->subscriptions;

        $creator_ids = $subscriptions->map(function ($subscription) {
            return collect($subscription)->only(['id']);
        });

        return view('feed/subscriptions', [
            'subscriptions' => $subscriptions,
            'videos' => Video::whereIn('creator_id', $creator_ids)
                ->whereDate('time_published', '>=', now()->subDays(5)->setTime(0, 0, 0)->toDateTimeString())
                ->orderBy('time_published','DESC')
                ->get(),
            'streams' => Stream::whereIn('creator_id', $creator_ids)->orderBy('viewers')->take(5)->get(),
        ]);
    }


    public function create($channelId)
    {
        $creator = Creator::findOrFail($channelId);
        if(Auth::user()->creator->slug === $creator->slug) { //check your not subscribing to yourself
            return redirect()->back()->with('error', 'You cannot subscribe to yourself');
        }
        if (Auth::user()->creator->subscriptions->contains('id', $creator->id)) { //check your not subscribing twice
            return redirect()->back()->with('error', 'You are already subscribed to this creator');
        } else {
            Subscription::firstOrCreate([
                'subscriber_id' => Auth::user()->creator->id,
                'creator_id' => $creator->id,
            ]);
            $creator->subscriber_count++;
            $creator->save();
        }
        return(['success' => 'You have subscribed to this creator']);
    }

    public function destroy($channelId) {
        $creator = Creator::findOrFail($channelId);
        $subscription = Subscription::query()
            ->where([
                [
                    'subscriber_id', '=', Auth::user()->creator->id,
                ],
                [
                    'creator_id', '=', $creator->id
                ]
            ]);
        if ($subscription->count() > 0) {
            $subscription->delete();
            $creator->subscriber_count--;
            $creator->save();
        }

        return (['success' => 'You have unsubscribed from this creator']);
    }



    public function store()
    {

    }


    public function show()
    {

    }


    public function edit()
    {
    }


    public function update()
    {

    }



}
