<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatRoomController extends Controller
{
    // web routes
    public function globalChat(): \Inertia\Response
    {
        return Inertia::render('Chatroom/Viewer');
    }


    // api routes
    // index
    public function index()
    {
        $chatrooms = ChatRoom::all();

        // should be a resource but I'm lazy
        $chatrooms->map(function ($chatroom) {
            $chatroom->type = 'chatroom';
            return $chatroom;
        });

        return response()->json([
            'chatrooms' => $chatrooms
        ]);
    }
}
