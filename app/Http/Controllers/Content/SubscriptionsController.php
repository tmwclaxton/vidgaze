<?php

namespace App\Http\Controllers\Content;


use App\Http\Controllers\Controller;
use App\Models\Stream;
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


    public function create()
    {
        //
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


    public function destroy()
    {
        //
    }
}
