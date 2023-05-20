<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCreatorRequest;
use App\Http\Requests\UpdateCreatorRequest;
use App\Models\CreatorModels\Creator;
use App\Models\PodcastModels\Podcast;
use Composer\DependencyResolver\Request;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CreatorController extends Controller
{

    public function index()
    {

    }

    public function infinite(Request $request)
    {
        $perPage = $request->perPage ?? 20;
        //get ids from params
        $creatorIds = $request->creatorIds ?? [];
        $podcaster = $request->input('podcaster') ?? false;

        $query = Creator::query();
        if($podcaster){
            //get most popular podcasts and then get the creators from those podcasts
            Podcast::orderBy('views','desc')->take(10)->get()->each(function($podcast) use ($query){
                $query->orWhere('id','=',$podcast->creator_id);
            });
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCreatorRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCreatorRequest $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param Creator $creator
     * @return Application|Factory|View
     */
    public function show(Creator $creator)
    {
        //moved to livewire channel content
//        $twitch_source = $creator->sources()->where('source_name','=', Platforms::Twitch->name)->first();
//        if($twitch_source){
//                SearchResultDTO::createStreamModelFromResultDTO(
//                    Twitch::getChannelStream($twitch_source->external_channel_id),
//                    $creator
//            );
//        }
        return view('channel.index', [
            'creator'=> $creator,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Creator $creator
     * @return Application|Factory|View
     */
    public function edit(Creator $creator)
    {
        return view('studio/customise');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return RedirectResponse
     */
    public function update()
    {
        // dd(request()->all());
        request()->validate([
            'name' => 'required|max:50',
            'bio' => 'nullable|max:1000',
            'contact_email' => 'email|nullable|max:320',
            'avatar_url' => 'image|mimes:jpeg,png,jpg,svg,webp|nullable|max:4096||dimensions:min_width=98,min_height=98,max_width=1000,max_height=1000',
            'banner_url' => 'image|mimes:jpeg,png,jpg,svg,webp|nullable|max:6144||dimensions:min_width=2048,min_height=1152'
        ]);
        $attributes = request()->all();

        //profile picture
        if(isset($attributes['avatar_url'])) {
            //delete old file if it exists
            if (Auth::User()->creator->avatar_url != null) {
                if (file_exists(public_path() . Auth::User()->creator->avatar_url)) {
                    unlink(public_path() . Auth::User()->creator->avatar_url);
                }
            }
            //create thumbnail name
            $imageName = time() . '-' . Auth::User()->creator->id . '-' . 'avatar' . '.' . request()->avatar_url->extension();
            //this is not private, but outsider would have to guess url
            request()->avatar_url->storePubliclyAs('profile_pictures', $imageName); //remember to symlink
            //add url to record
            Auth::User()->creator->update(['avatar_url' => '/storage/profile_pictures/' . $imageName]);

        } elseif (isset($attributes['removeProfilePicture'] )) { //if user wants profile picture removed

            //delete old file if it exists
            if (Auth::User()->creator->avatar_url != null) {
                if (file_exists(public_path() . Auth::User()->creator->avatar_url)) {
                    if (is_file(public_path() . Auth::User()->creator->avatar_url)) {
                        unlink(public_path() . Auth::User()->creator->avatar_url);
                    }
                }
                //reset creator avatar url to null
                Auth::User()->creator()->update(['avatar_url' => null]);
            }
        }

        //banner picture
        if(isset($attributes['banner_url'])) {
            //delete old file if it exists
            if (Auth::User()->creator->banner_url != null) {
                if (file_exists(public_path() . Auth::User()->creator->banner_url)) {
                    unlink(public_path() . Auth::User()->creator->banner_url);
                }
            }
            //create banner name
            $imageName = time() . '-' . Auth::User()->creator->id . '-' . 'banner' . '.' . request()->banner_url->extension();
            //this is not private, but outsider would have to guess url
            request()->banner_url->storePubliclyAs('profile_banners', $imageName); //remember to symlink
            //add url to record
            Auth::User()->creator->update(['banner_url' => '/storage/profile_banners/' . $imageName]);

        } elseif (isset($attributes['removeBannerPicture'] )) { //if user wants profile banner removed

            //delete old file if it exists
            if (Auth::User()->creator->avatar_url != null) {

                if (file_exists(public_path() . Auth::User()->creator->banner_url)) {
                    if (is_file(public_path() . Auth::User()->creator->banner_url)) {
                        unlink(public_path() . Auth::User()->creator->banner_url);
                    }
                }
                //reset creator banner url to null
                Auth::User()->creator()->update(['banner_url' => null]);
            }
        }

        //keep biography consistent format
        $attributes['bio'] = json_encode($attributes['bio']);
        Auth::User()->creator()->update([
            'name' =>  $attributes['name'],
            'bio' =>  $attributes['bio'],
            'contact_email' =>  $attributes['contact_email']
        ]);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Creator $creator
     * @return \Illuminate\Http\Response
     */
    public function destroy(Creator $creator)
    {
        //
    }
}
