<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\CreatorModels\ChannelDisinterest;
use App\Models\CreatorModels\Creator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChannelDisinterestController extends Controller
{
    private function getChannelAndDisinterest($channelId)
    {
        $channel = Creator::findOrFail($channelId);

        if (!$channel) {
            return response()->json([
                'error' => 'Channel not found'
            ], 404);
        }

        $disinterest = ChannelDisinterest::where([
            'creator_id' => Auth::user()->creator->id,
            'channel_id' => $channel->id,
        ])->first();

        return [$channel, $disinterest];
    }

    public function create(Request $request, $channelId)
    {
        [$channel, $disinterest] = $this->getChannelAndDisinterest($channelId);

        if ($disinterest) {
            return response()->json([
                'error' => 'You have already disinterested this channel'
            ], 400);
        }

        $disinterest = ChannelDisinterest::create([
            'creator_id' => Auth::user()->creator->id,
            'channel_id' => $channel->id,
        ]);

        $disinterest->save();
    }

    public function destroy(Request $request, $channelId)
    {
        [$channel, $disinterest] = $this->getChannelAndDisinterest($channelId);

        if (!$disinterest) {
            return response()->json([
                'error' => 'You have not disinterested this channel'
            ], 400);
        }

        $disinterest->delete();
    }
}
