<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\CommentInteraction;
use App\Models\CommentModels\Comment;
use Illuminate\Support\Facades\Auth;

class CommentInteractionController extends Controller
{

    private function commentUserChecks(Comment $comment) {
        // check if user is authenticated
        if (!Auth::user()) {
            return response()->json([
                'error' => 'You are not authenticated'
            ], 401);
        }

        // can't like your own comment
        //if (Auth::user()->creator->id === $comment->creator_id) {
        //    return response()->json(['error' => 'You cannot like your own comment'], 403);
        //}

    }

    // formats the response for the like and dislike methods
    private function formatLikeAndDislikeResponse(Comment $comment, $liked, $message): void
    {
        response()->json([
            'message' => $message,
            'result' => $liked,
            'like_count' => $comment->like_count,
            'dislike_count' => $comment->dislike_count,
        ], 200);
    }

    private function getInteraction(Comment $comment) {

        $commentInteraction = CommentInteraction::firstOrCreate([
            'creator_id' => Auth::user()->creator->id,
            'comment_id' => $comment->id,
        ]);

        return $commentInteraction;
    }

    public function toggleLike(Comment $comment) {

        $this->commentUserChecks($comment);

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

        $this->formatLikeAndDislikeResponse($comment, $liked, $message);


    }

    public function toggleDislike(Comment $comment) {

        $this->commentUserChecks($comment);

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

        $this->formatLikeAndDislikeResponse($comment, $liked, $message);
    }


}
