<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\CommentInteraction;
use App\Models\CommentModels\Comment;
use App\Models\VideoModels\Video;
use App\Services\MixPanelTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{

    protected array $rules = [
        'body' => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/|max:10000|min:1',
        'video_id' => 'required|exists:videos,id',
        'parent_id' => 'nullable|exists:comments,id',
   ];

    private function verifyUserPermissions(Comment $comment): void
    {
        // check if user is authenticated
        if (!Auth::user()) {
            response()->json([
                'error' => 'You are not authenticated'
            ], 401);
        }

        if ( (Auth::user() === null || $comment->creator_id !== Auth::user()->creator->id) && !Auth::user()->isAdmin() ) {
            response()->json(['error' => 'You do not have permission to interact with this comment'], 403);
        }

    }

    //the 7 restful routes
    // index - show all
    // show - show one
    // create - show a page to create one of those item
    // store - when form submited persist the item
    // edit - show page to edit the item
    // update - when form submitted save the edits
    // destroy - delete one item

    public function infinite(Request $request) {
        // get order by, limit, offset, and video id from request,
        $perPage = $request->perPage ?? 20;
        $commentIds = $request->commentIds ?? [];
        $orderByMethod = $request->input('category') ?? 'Order By';
        $videoId = $request->input('videoId') ?? null;
        $firstCommentId = $request->input('firstCommentId') ?? null;
        $parentId = $request->input('parentId') ?? null;


        // if commentIds is not an array, explode the ids into an array
        if (!is_array($commentIds) ) {
            $commentIds = explode(',', $commentIds);
        }

        // get the query
        $query = Comment::query()
            ->where([['parent_comment_id', '=', $parentId], ['video_id', '=', $videoId]]);

        // if firstCommentId is not null, add where clause to query
        if ($firstCommentId !== null) {
            $query->where('id', '!=', $firstCommentId);
        }

        // if commentIds is not empty, add where clause to query
        if (!empty($commentIds)) {
            $query->whereNotIn('id', $commentIds);
        }

        // order the query by the orderByMethod passed in
        switch ($orderByMethod) {
            case 'Order By':
            case 'Best':
                $query->orderBy('like_count', 'DESC')
                    ->orderBy('dislike_count', 'ASC');
                break;
            case 'New':
                $query->orderBy('created_at', 'DESC');
                break;
            case 'Controversial':
                $query->orderBy('dislike_count', 'DESC');
                break;
            case 'Old':
                $query->orderBy('created_at', 'ASC');
                break;
        }

        // get the comments
        $comments = $query->limit($perPage)->get();

        // if firstCommentId is not null, add it to the beginning of the comments array
        if ($firstCommentId !== null) {
            $comments->prepend(Comment::find($firstCommentId));
        }

        // return the comments
        return response()->json([
            'success' => true,
            'comments' => $comments,
        ]);
    }

    public function store(Request $request)
    {

        if (!(isset(Auth::user()->creator->id) && $this->validate($request, $this->rules))) {
            return response()->json([
                'success' => false,
                'message' => 'Comment could not be created',
            ]);
        }
        $comment = Comment::create([
            'creator_id' => Auth::user()->creator->id,
            'video_id' => $this->video->id,
            'parent_comment_id' => $this->comment_id,
            'body' => $this->body,
        ]);

        $this->video->comment_count++;
        $this->video->save();

        return response()->json([
            'success' => true,
            'message' => 'Comment created successfully',
            'comment' => $comment,
        ]);


    }

    public function update(Request $request) {
        $comment = Comment::find($request->commentId);
        $body = $request->body ?? null;
        $this->verifyUserPermissions($comment);

        if ( !$this->validate( request(), $this->rules ) ) {
            return response()->json([
                'success' => false,
                'message' => 'Comment could not be updated',
            ]);
        }

        $comment->body = $body;
        $comment->save();

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
        ]);
    }

    public function destroy(Request $request)
    {
        $comment = Comment::find($request->commentId);
        $this->verifyUserPermissions($comment);

        if ($success = $comment->delete() === false) {
            $message = 'Comment could not be deleted';
        } else {
            $message = 'Comment deleted successfully';
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }


}
