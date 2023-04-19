<?php

namespace App\Http\Controllers\Content;

use App\Helpers\Tokens\TokenHelper;
use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\PlaylistVideo;
use App\Models\Video;
use App\Models\VideoViewInfos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use function Deployer\Support\array_merge_alternate;

class VideoController extends Controller
{
    //the 7 restful routes
    // index - show all
    // show - show one
    // create - show a page to create one of those item
    // store - when form submited persist the item
    // edit - show page to edit the item
    // update - when form submitted save the edits
    // destroy - delete one item

    public function index()
    {
        return Inertia::render('Viewer/Videos/VideosIndex');
    }

    public function users()
    {

        return inertia('Users', [
            'name' => "Toby Claxton",
            'frameworks' => [
                'laravel','vue','inertia'
            ],
            'time' => now()->toDateTimeString(),
        ]);
    }
    public function settings()
    {
        return inertia('Settings', [
            'name' => "Toby Claxton",
            'frameworks' => [
                'laravel','vue','inertia'
            ],
        ]);
    }

    public function shorts()
    {
        return Inertia::render('Viewer/Shorts/ShortsIndex');
        //return view('shorts', [
        //    'firstShortSlug' => null,
        //]);
    }

    public function short(Request $request)
    {
        $firstShort = Video::where('slug', '=', $request->video)->first();
        //forbidden because visibility is set to private and you don't own it
        if ($firstShort->visibility == 'private' && $firstShort->creator_id != Auth::user()->creator->id) {
            abort(401);
        }
        return view('shorts', [
            'firstShortSlug' => $request->video,
            'firstShort' => $firstShort,
        ]);
    }

    public function show(Video $video, Request $request)
    {
        return $this->renderVideoView($video, null, $request->comment);
    }

    public function playlist(Video $video, Playlist $playlist)
    {
        $playlistVideos = $playlist->videos->reverse()->values();
        $key = $playlistVideos->search(function ($item) use ($video) {
            return $item->id === $video->id;
        });

        if (Auth::user() && $playlist->slug != Auth::user()->creator->getPlaylist('history', true)->slug) {
            $this->add2History($video);
        }
        $extraData = [];
        $nextVideo = $playlistVideos->values()->get($key + 1);
        if ($nextVideo != null) {
            $extraData = ['videoNext' => $nextVideo];
        }
        $extraData = array_merge($extraData,
            [
                'playlist' => $playlist,
                'playlist_videos' => $playlistVideos->all(),
                'current_video_key' => $key,
            ]
        );
//        dd($playlistVideos);
        return $this->renderVideoView($video, $playlist, null, $extraData);
    }

    public function shuffle(Video $video, Playlist $playlist)
    {
        $this->add2History($video);
        return $this->renderVideoView($video, $playlist, null, [
            'playlist' => $playlist,
            'playlist_videos' => $playlist->videos->shuffle()->all(),
        ]);
    }


    protected function renderVideoView(Video $video, $playlist = null, $commentSlug = null, $extraData = [])
    {
        if ($video->visibility == 'private' && $video->creator_id != Auth::user()->creator->id) {
            abort(401);
        }

        $this->add2History($video);
        $creatorID = Auth::user() ? Auth::user()->id : "empty";
        $webhookToken = TokenHelper::generateToken(session()->getId(), $creatorID, $video->id);

        if ($creatorID != 'empty') {
            $videoViewInfo = VideoViewInfos::where(
                [
                    ['viewer_id', '=', $creatorID],
                    ['video_id', '=', $video->id]
                ]
            )->get();

            if (!$videoViewInfo->isEmpty()) {
                //check video isn't just about to end it
                //if it is you don't need to set video view point to it
                if ($videoViewInfo->first()->view_point < ($video->duration - 20)) {
                    $extraData = array_merge($extraData, ['videoViewInfo' => $videoViewInfo->first()]);
                }
            }

        }

        return view('watch', array_merge([
            'video' => $video,
            'webhookToken' => $webhookToken,
            'external_id' => $video->getPrimarySourceID(),
            'creator' => $video->creator,
            'videoNext' => Video::inRandomOrder()->first(),
            'endScreenSuggestions' => Video::inRandomOrder()->take(6)->get(),
            'firstCommentSlug' => $commentSlug,
        ], $extraData));
    }


    public function edit(Video $video)
    {
        if ($video->visibility == 'private' && $video->creator_id != Auth::user()->creator->id) {
            abort(401);
        }
        return view('studio.video', [
            'item' => $video,
        ]);
    }

    public function add2History(Video $video)
    {

        if (Auth::user() !== null) {
            //find playlist
            $playlist = Auth::user()->creator->getPlaylist('history', true);
            //if video already in watch history bring to top by deleting old
            $playlistVideo = PlaylistVideo::where("playlist_id", $playlist->id)->where("video_id", $video->id)->get()->first();
            if ($playlistVideo) {
                $playlist->alterPlaylist($playlist, $video, 'remove');

            } else {

            }
            //and adding back
            $playlist->alterPlaylist($playlist, $video, 'add');
        }

    }
}
