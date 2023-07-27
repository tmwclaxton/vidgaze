<?php

namespace App\Http\Controllers\ApiControllers;

use App\Enums\Kind;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentResource;
use App\Models\CommentInteraction;
use App\Models\CommentModels\Comment;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorComment;
use App\Models\PodcastEpisodeModels\PodcastEpisode;
use App\Models\PodcastEpisodeModels\PodcastEpisodeComment;
use App\Models\StreamModels\Stream;
use App\Models\StreamModels\StreamComment;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentApiController extends Controller
{

    protected Kind $kind;

    // use kind to figure out which model to use
    protected array $allowedKinds = [
        Kind::Video,
        Kind::PodcastEpisode,
        Kind::Stream,
        Kind::Creator
    ];

    protected array $allowedCategories = [
        'order by',
        'best',
        'new',
        'controversial',
        'old',
        'random'
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
            'item_id' => 'integer|required',
            'item_type' => 'in:' . implode(',', $this->allowedKinds) . '|required',
            // default is order by
            'category' => 'in:' . implode(',', $this->allowedCategories) . '|nullable',
            'per_page' => 'nullable|integer',
            'comment_ids' => 'nullable|array',
            'first_comment_id' => 'nullable|integer',
            'parent_comment_id' => 'nullable|integer'
        ]);

        // get order by, limit, offset, and video id from request,
        $per_page = $request->input('per_page') ?? 10;
        $per_page = $per_page > 100 ? 100 : $per_page;
        $comment_ids = $request->comment_ids ?? [];
        $category = $request->input('category') ?? 'order by';
        $item_id = $request->input('item_id') ?? null;
        $item_type = $request->input('item_type') ?? null;
        $first_comment_id = $request->input('first_comment_id') ?? null;
        $parent_comment_id = $request->input('parent_comment_id') ?? null;


        // if commentIds is not an array, explode the ids into an array
        if (!is_array($comment_ids) ) {
            $comment_ids = explode(',', $comment_ids);
        }

        // get the comments for item by using the manythrough relationship
        $comments = null;
        $query = null;
        switch ($item_type) {
            case 'video':
                $query = Video::find($item_id)->comments();
                break;
            case 'podcast_episode':
                $query = PodcastEpisode::find($item_id)->comments();
                break;
            case 'stream':
                $query = Stream::find($item_id)->comments();
                break;
            case 'creator':
                $query = Creator::find($item_id)->comments();
                break;
            default:
                return response()->json(['error' => 'Invalid item type'], 400);
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
            // best and order by are the same
            case 'best' || 'order by':
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

        $request->validate([
            'item_id' => 'integer|required',
            'item_type' => 'in:' . implode(',', $this->allowedKinds) . '|required',
            'parent_comment_id' => 'nullable|integer|exists:comments,id',
            'body' => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/|max:10000|min:1',
        ]);

        //get info from request
        $item_type = $request->item_type;
        $item_id = $request->item_id;
        $parent_comment_id = $request->parent_comment_id ?? null;
        $body = $request->body;

        // validate item type exists
        switch ($item_type) {
            case 'video':
                $item_model = VideoComment::class;
                $collumn = 'video_id';
                $item = Video::find($item_id);
                break;
            case 'stream':
                $item_model = StreamComment::class;
                $collumn = 'stream_id';
                $item = Stream::find($item_id);
                break;
            case 'podcast_episode':
                $item_model = PodcastEpisodeComment::class;
                $collumn = 'podcast_episode_id';
                $item = PodcastEpisode::find($item_id);
                break;
            case 'creator':
                $item_model = CreatorComment::class;
                $collumn = 'creator_id';
                $item = Creator::find($item_id);
                break;
            default:
                return response()->json([
                    'toastType' => 'warning',
                    'message' => 'Unsupported item type'
                ], 400);
        }

        $comment = Comment::create([
            'creator_id' => Auth::user()->creator->id,
            'parent_comment_id' => $parent_comment_id,
            'body' => $body
        ]);


        if ($comment === null) {
            return response()->json([
                'toastType' => 'warning',
                'message' => 'Comment could not be created',
            ]);
        }

        $item_model::create([
            $collumn => $item_id,
            'comment_id' => $comment->id,
        ]);

        $item->comment_count++;
        $item->save();

        if ($parent_comment_id !== null) {
            $parent_comment = Comment::find($parent_comment_id);
            $parent_comment->reply_count++;
            $parent_comment->save();
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
        $request->validate([
            'comment_id' => 'required|integer|exists:comments,id',
            'body' => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/|max:10000|min:1',
        ]);

        $comment = Comment::find($request->comment_id);

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
            'comment_id' => 'required|integer|exists:comments,id',
            'item_type' => 'in:' . implode(',', $this->allowedKinds) . '|required',
        ]);

        $comment_id = $request->input('comment_id');
        $item_type = $request->input('item_type');

        // get comment
        $comment = Comment::find($comment_id);

        //grab the comment's item from either the video, stream, or podcast_episode comment table
        $item = $comment->hasOneThroughObject($item_type);

        if ($item === null) {
            return response()->json([
                'toastType' => 'warning',
                'message' => 'Item could not be found',
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

            $item->comment_count--;
            $item->save();
        }

        return response()->json([
            'toastType' => $success ? 'success' : 'warning',
            'message' => $message,
        ]);
    }


}
