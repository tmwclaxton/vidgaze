<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\CommentModels\CommentAward;
use App\Models\StreamModels\StreamAward;
use App\Models\VideoModels\VideoAward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AwardController extends Controller
{
    // Fetch all awards
    public function index()
    {
        // Get all awards, modify the query as needed
        return response()->json(Award::orderBy('coin_price', 'DESC')->get());
    }

    // Handle awarding process
    public function award(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'award_id' => 'required|exists:awards,id',
            'type' => 'required|in:comment,video,stream',
            'object_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $award_id = $request->input('award_id');
        $type = $request->input('type');
        $object_id = $request->input('object_id');

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $award = Award::find($award_id);

        if (!$award) {
            return response()->json(['error' => 'Award not found'], 404);
        }

        if ($user->creator->coins < $award->coin_price) {
            return response()->json(['error' => 'Not enough VidCoins'], 400);
        }

        // Deduct coins
        $user->creator->coins -= $award->coin_price;
        $user->creator->save();

        // Create award entry based on type
        switch ($type) {
            case 'comment':
                CommentAward::create([
                    'comment_id' => $object_id,
                    'giver_id' => $user->creator->id,
                    'award_id' => $award_id,
                ]);
                break;
            case 'video':
                VideoAward::create([
                    'video_id' => $object_id,
                    'giver_id' => $user->creator->id,
                    'award_id' => $award_id,
                ]);
                break;
            case 'stream':
                StreamAward::create([
                    'stream_id' => $object_id,
                    'giver_id' => $user->creator->id,
                    'award_id' => $award_id,
                ]);
                break;
        }

        return response()->json(['success' => 'Award given successfully']);
    }
}
