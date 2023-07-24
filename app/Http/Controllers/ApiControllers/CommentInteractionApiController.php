<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\CommentModels\Comment;
use App\Models\CommentModels\CommentInteraction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentInteractionApiController extends Controller
{



    /** formats the response for the like and dislike methods
     * @param Comment $comment
     * @param $liked
     * @param $message
     * @return JsonResponse
     */
    private function formatLikeAndDislikeResponse(Comment $comment, $liked, $message)
    {
        return response()->json([
            //'toastType' => 'success', // 'success', 'info', 'warning'
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
    public function toggleLike(int $commentId) {

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
    public function toggleDislike(int $commentId) {

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
        $comments = null;

        // get type of item (video / stream / podcast) and id of item
        $itemType = $request->input('item_type');
        $itemId = $request->input('item_id');

        // get all comments for item and has user's creator id
        if ($itemType == 'video') {
            //$comments = CommentInteraction::query()->where([['video_id', '=', $itemId],['creator_id', '=', Auth::user()->creator->id]])->get();
            $commentInteractions = Comment::query()->join('comment_interactions', 'comments.id', '=', 'comment_interactions.comment_id')->where([['comments.video_id', '=', $itemId],['comment_interactions.creator_id', '=', Auth::user()->creator->id]])->get();
            //return $commentInteractions;
            // format so it return the comment id and the interaction
            $commentInteractions = $commentInteractions->map(function($commentInteraction) {
                return [
                    'comment_id' => $commentInteraction->comment_id,
                    'liked' => $commentInteraction->liked,
                ];
            });
        }

        if (!$commentInteractions) {
            return response()->json([
                'error' => 'No comment interactions found',
            ], 404);
        }

        return response()->json([
            'result' => $commentInteractions,
        ], 200);

    }


}
