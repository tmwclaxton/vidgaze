<?php

namespace App\Http\Controllers\ApiControllers;

use App\Enums\Kind;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentResource;
use App\Models\CommentInteraction;
use App\Models\CommentModels\Comment;
use App\Models\VideoModels\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentApiController extends Controller
{

    protected array $rules = [
        'body' => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/|max:10000|min:1',
        'parent_comment_id' => 'nullable|exists:comments,id',
        'item_id' => 'required|integer',
        'item_type' => 'required|in:video,podcast,stream',
   ];



    private function verifyUserPermissions(Comment $comment): void
    {

        if ( (Auth::user() === null || $comment->creator_id !== Auth::user()->creator->id) && !Auth::user()->isAdmin() ) {
            response()->json(['error' => 'You do not have permission to interact with this comment'], 403);
        }

    }


    /**
     * Get all comments for a video
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) {

        $request->validate([
            'item_id' => 'required|integer',
            'item_type' => 'required|in:video,podcast,stream',
            'category' => 'required|in:order by,best,new,controversial,old',
            'per_page' => 'nullable|integer',
            'comment_ids' => 'nullable|array',
            'first_comment_id' => 'nullable|integer',
            'parent_comment_id' => 'nullable|integer'
        ]);

        // get order by, limit, offset, and video id from request,
        $per_page = $request->input('per_page') ?? 10;
        $per_page = $per_page > 100 ? 100 : $per_page;
        $comment_ids = $request->comment_ids ?? [];
        $category = $request->input('category') ?? 'Order By';
        $item_id = $request->input('item_id') ?? null;
        $item_type = $request->input('item_type') ?? null;
        $first_comment_id = $request->input('first_comment_id') ?? null;
        $parent_comment_id = $request->input('parent_comment_id') ?? null;


        // if commentIds is not an array, explode the ids into an array
        if (!is_array($comment_ids) ) {
            $comment_ids = explode(',', $comment_ids);
        }
        switch ($item_type) {
            case 'video':
                // get the query
                $query = Comment::query()
                    ->where([['parent_comment_id', '=', $parent_comment_id], ['video_id', '=', $item_id]]);
            break;
            default:
                return response()->json([
                    'toastType' => 'warning',
                    'message' => 'Unsupported item type'
                ], 400);
        }

        // if firstCommentId is not null, add where clause to query
        if ($first_comment_id !== null) {
            $query->where('id', '!=', $first_comment_id);
        }

        // if commentIds is not empty, add where clause to query
        if (!empty($comment_ids)) {
            $query->whereNotIn('id', $comment_ids);
        }

        // order the query by the orderByMethod passed in
        switch ($category) {
            case 'order by':
            case 'best':
                $query->orderBy('like_count', 'DESC')
                    ->orderBy('dislike_count', 'ASC')
                    ->orderBy('created_at', 'DESC');
                break;
            case 'new':
                $query->orderBy('created_at', 'DESC');
                break;
            case 'controversial':
                $query->orderBy('dislike_count', 'DESC');
                break;
            case 'old':
                $query->orderBy('created_at', 'ASC');
                break;
        }

        // get the comments
        $comments = new CommentCollection($query->limit($per_page)->get());

        // if firstCommentId is not null, add it to the beginning of the comments array
        if ($first_comment_id !== null) {
            $comments->prepend(new CommentResource(Comment::find($first_comment_id)));
        }

        // return the comments
        return response()->json([
            'comments' => $comments,
        ]);
    }

    /**
     * Store a newly created comment in database.
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {

        $request->validate($this->rules);

        //get info from request
        $item_type = $request->item_type;
        $item_id = $request->item_id;
        $parent_comment_id = $request->parent_comment_id ?? null;
        $body = $request->body;



        if (!(isset(Auth::user()->creator->id) )) {
            return response()->json([
                'toastType' => 'warning',
                'message' => 'Comment could not be created',
            ]);
        }

        switch ($item_type) {
            case 'video':
                // grab video
                $video = Video::find($item_id);

                if ($video === null) {
                    return response()->json([
                        'toastType' => 'warning',
                        'message' => 'Video could not be found',
                    ]);
                }

                $comment = Comment::create([
                    'creator_id' => Auth::user()->creator->id,
                    'video_id' => $item_id,
                    'parent_comment_id' => $parent_comment_id,
                    'body' => $body
                ]);

                $video->comment_count++;
                $video->save();

                break;
        }

        if ($parent_comment_id !== null) {
            $parent_comment = Comment::find($parent_comment_id);
            $parent_comment->reply_count++;
            $parent_comment->save();
        }

        if ($comment === null) {
            return response()->json([
                'toastType' => 'warning',
                'message' => 'Comment could not be created',
            ]);
        }


        return response()->json([
            'toastType' => 'success',
            'message' => 'Comment created successfully',
            'comment' => new CommentResource($comment),
        ]);


    }

    /**
     * Update the specified comment in db.
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request) {
        $request->validate($this->rules);

        $comment = Comment::find($request->item_id);

        if ($comment === null) {
            return response()->json([
                'toastType' => 'warning',
                'message' => 'Comment could not be found',
            ]);
        }

        $body = $request->body ?? null;
        $this->verifyUserPermissions($comment);

        $comment->body = $body;
        $comment->save();

        return response()->json([
            'toastType' => 'success',
            'message' => 'Comment updated successfully',
            'comment' => new CommentResource($comment),
        ]);
    }

    /**
     * Remove the specified comment from db.
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'item_type' => 'required|in:video,podcast,stream',
            'comment_id' => 'required|integer',
        ]);
        $item_type = $request->input('item_type'); // for future use
        $item_id = $request->input('item_id');
        $comment_id = $request->input('comment_id');

        $comment = Comment::find($comment_id);

        $video = Video::find($item_id);


        if ($comment === null) {
            return response()->json([
                'toastType' => 'warning',
                'message' => 'Comment could not be found',
            ]);
        }

        $this->verifyUserPermissions($comment);

        $parent_comment_id = $comment->parent_comment_id;

        if ($success = $comment->delete() === false) {
            $message = 'Comment could not be deleted';
        } else {
            $message = 'Comment deleted successfully';

            // if comment is a reply, decrement reply count
            if ($parent_comment_id !== null) {
                $parent_comment = Comment::find($parent_comment_id);
                $parent_comment->reply_count--;
                $parent_comment->save();
            }

            $video->comment_count--;
            $video->save();
        }

        return response()->json([
            'toastType' => $success ? 'success' : 'warning',
            'message' => $message,
        ]);
    }


}
