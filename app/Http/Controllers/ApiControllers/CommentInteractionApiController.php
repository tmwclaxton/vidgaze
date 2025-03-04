<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentInteractionCollection;
use App\Models\CommentModels\Comment;
use App\Models\CommentModels\CommentInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentInteractionApiController extends Controller
{

    // validation rules for the comment interaction
    protected array $rules = [
        'item_id' => 'required|integer',
        'item_type' => 'required|in:video,podcast,stream,creator,chatroom',
        'comment_id' => 'required|integer',
    ];

    /** formats the response for the like and dislike methods
     * @param Comment $comment
     * @param $liked
     * @param $message
     * @return JsonResponse
     */
    private function formatLikeAndDislikeResponse(Comment $comment, $liked, $message)
    {
        return response()->json([// 'success' or 'undo'
            'toastType' => $liked ? 'success' : 'undo',
            'message' => $message,
            'result' => $liked,
            'like_count' => $comment->like_count,
            'dislike_count' => $comment->dislike_count,
        ], 200);
    }

    /** gets the interaction for the comment
     * @param Comment $comment
     * @return CommentInteraction
     */
    private function getInteraction(Comment $comment) {

        return CommentInteraction::firstOrCreate([
            'creator_id' => Auth::user()->creator->id,
            'comment_id' => $comment->id,
        ]);
    }

    /** gets the interaction for the comment
     * @param int $commentId
     * @return JsonResponse
     */
    public function toggleLike(Request $request) {
        $request->validate($this->rules);
        $itemType = $request->input('item_type');
        $itemId = $request->input('item_id');
        $commentId = $request->input('comment_id');

        $comment = Comment::findOrFail($commentId);

        $commentInteraction = $this->getInteraction($comment);

        $message = 'Liked successfully';

        //if they change their rating from dislike to like
        if ($commentInteraction->liked == 'dislike') {
            $comment->dislike_count--;
        }
        if ($commentInteraction->liked != 'like') {
            $commentInteraction->liked = 'like';
            $comment->like_count++;
            $liked = 'like';
        } else {
            // Have they already liked it
            $commentInteraction->liked = null;
            $comment->like_count--;
            $liked = null;
            $message = 'Like removed successfully';
        }
        $commentInteraction->save();
        $comment->save();

        return $this->formatLikeAndDislikeResponse($comment, $liked, $message);


    }

    /** gets the interaction for the comment
     * @param int $commentId
     * @return JsonResponse
     */
    public function toggleDislike(Request $request) {
        $request->validate($this->rules);

        $itemType = $request->input('item_type');
        $itemId = $request->input('item_id');
        $commentId = $request->input('comment_id');

        $comment = Comment::findOrFail($commentId);

        $commentInteraction = $this->getInteraction($comment);

        $message = 'Disliked successfully';

        //if they change their rating from like to dislike
        if ($commentInteraction->liked == 'like') {
            $comment->like_count--;
        }
        if ($commentInteraction->liked != 'dislike') {
            $commentInteraction->liked = 'dislike';
            $comment->dislike_count++;
            $liked = 'dislike';
        } else {
            // Have they already disliked it
            $commentInteraction->liked = null;
            $comment->dislike_count--;
            $liked = null;
            $message = 'Dislike removed successfully';
        }
        $commentInteraction->save();
        $comment->save();

        return $this->formatLikeAndDislikeResponse($comment, $liked, $message);
    }

    /** gets the interaction for the comment
     * @param Request $request
     * @return JsonResponse
     */
    public function getInteractionsByItem(Request $request) {
        $request->validate(
            [
                'item_id' => 'required|integer',
                'item_type' => 'required|in:video,podcast,stream,creator,chatroom',
            ]
        );

        // get type of item (video / stream / podcast) and id of item
        $itemType = $request->input('item_type');
        $itemId = $request->input('item_id');

        if ($itemType == "video") {
            // join comment interactions, comments and video comments to get the interactions for the video
            $commentInteractions = CommentInteraction::join('comments', 'comments.id', '=', 'comment_interactions.comment_id')
                ->join('video_comments', 'video_comments.comment_id', '=', 'comments.id')
                ->where('video_comments.video_id', '=', $itemId)
                ->where('comment_interactions.creator_id', '=', Auth::user()->creator->id)
                ->get();
        } else if ($itemType == "stream") {
            // join comment interactions, comments and stream comments to get the interactions for the stream
            $commentInteractions = CommentInteraction::join('comments', 'comments.id', '=', 'comment_interactions.comment_id')
                ->join('stream_comments', 'stream_comments.comment_id', '=', 'comments.id')
                ->where('stream_comments.stream_id', '=', $itemId)
                ->where('comment_interactions.creator_id', '=', Auth::user()->creator->id)
                ->get();
        } else if ($itemType == "podcast") {
            // join comment interactions, comments and podcast comments to get the interactions for the podcast

        } else if ($itemType == "chatroom") {
            // join comment interactions, comments and chatroom comments to get the interactions for the chatroom
            $commentInteractions = CommentInteraction::join('comments', 'comments.id', '=', 'comment_interactions.comment_id')
                ->join('chat_room_comments', 'chat_room_comments.comment_id', '=', 'comments.id')
                ->where('chat_room_comments.chatroom_id', '=', $itemId)
                ->where('comment_interactions.creator_id', '=', Auth::user()->creator->id)
                ->get();
        } else {
            return response()->json([
                'error' => 'Invalid item type',
            ], 400);
        }

        if ($commentInteractions != null && !$commentInteractions->isEmpty() ) {
            $commentInteractions = new CommentInteractionCollection($commentInteractions);
        }

        return response()->json([
            'interactions' => $commentInteractions,
        ], 200);

    }


}
