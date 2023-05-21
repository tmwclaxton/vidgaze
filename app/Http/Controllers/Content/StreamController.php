<?php

namespace App\Http\Controllers\Content;


use App\Helpers\Tokens\TokenHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\StreamCollection;
use App\Models\Category;
use App\Models\StreamModels\Stream;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Request;

class StreamController extends Controller
{

    public function index()
    {
        $categories = Category::query()
            ->where('thumbnail_url', '!=', null)
            ->where('tags_json', '!=', null)
            ->where('twitch_category_id', '!=', null)
            ->inRandomOrder()
            ->take(8)
            ->get();



        //return view('livestreams',[
        //    'stream'=> $stream,
        //    'webhookToken' => $webhookToken,
        //    'creator' => $stream->creator,
        //    'external_id' => $stream->getPrimarySourceID(),
        //    'categories' => $categories,
        //]);
        return Inertia::render('Viewer/Streams/StreamsIndex');

    }

    public function topStreams()
    {
        $streams = new StreamCollection( Stream::orderBy('viewers', 'DESC')->where('visibility', '=','public')
            ->where('streams.is_live', '=',true)
            ->take(6)->get() );
        return $streams;

    }



    public function create()
    {
        //
    }


    public function store()
    {

    }


    public function show(Stream $stream) {

        //forbidden if visibility is set to private and you don't own it
        if ($stream->visibility == 'private' && $stream->creator_id != Auth::user()->creator->id) {
            abort(401);
        }
        $creatorID = Auth::user() ? Auth::user()->id : "empty";

        $webhookToken = TokenHelper::generateToken(session()->getId(), $creatorID, $stream->id);

        return view('stream', [
            'stream'=> $stream,
            'webhookToken' => $webhookToken,
            'creator' => $stream->creator,
            'external_id' => $stream->getPrimarySourceID()
            ]);
    }


    public function edit(Stream $stream)
    {
        //forbidden if visibility is set to private and you don't own it
        if ($stream->visibility == 'private' && $stream->creator_id != Auth::user()->creator->id) {
            abort(401);
        }
        return view('studio.stream', [
            'item'=> $stream,
        ]);
    }


    public function update()
    {

    }


    public function destroy()
    {
        //
    }


}
