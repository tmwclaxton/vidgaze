<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\CommentInteraction;
use App\Models\CommentModels\Comment;
use App\Models\VideoModels\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{

    protected $rules = [
        'body' => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/|max:10000|min:3',
        'video_id' => 'required|exists:videos,id',
        'parent_id' => 'nullable|exists:comments,id',
   ];

    //the 7 restful routes
    // index - show all
    // show - show one
    // create - show a page to create one of those item
    // store - when form submited persist the item
    // edit - show page to edit the item
    // update - when form submitted save the edits
    // destroy - delete one item

    public function infinite(Request $request) {
        // get order by, limit, offset, and video id from request
        $perPage = $request->perPage ?? 20;
        $commentIds = $request->commentIds ?? [];
        $orderByMethod = $request->input('category') ?? 'Order By';
        $videoId = $request->input('videoId') ?? null;




    }

    public function create()
    {
        //
    }

    public function store() {

    }

    public function show(Video $video) {

    }

    public function edit(Comment $comment)
    {

    }

    public function update(Comment $comment) {


    }

    public function destroy(Comment $comment)
    {
        if ( (Auth::user() === null || $comment->creator_id !== Auth::user()->creator->id) && !Auth::user()->isAdmin() ) {
            return response()->json(['error' => 'You do not have permission to delete this comment'], 403);
        }

        if ($success = $comment->delete() === false) {
            $message = 'Comment could not be deleted';
        } else {
            $message = 'Comment deleted successfully';
        }

        return ['data' => [
            'success' => $success,
            'message' => $message,
        ]];
    }


}
