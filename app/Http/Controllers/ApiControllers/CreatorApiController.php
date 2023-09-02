<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CreatorCollection;
use App\Http\Resources\CreatorResource;
use App\Models\CreatorModels\Creator;
use App\Models\PodcastModels\Podcast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreatorApiController extends Controller
{

    /** Get all creators
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        //get ids from params
        $perPage = $request->perPage ?? 20;
        $podcasters = $request->podcaster ?? false;
        $featured = $request->featured ?? false;
        $creatorIds = $request->creatorIds ?? [];
        if (!is_array($creatorIds) ) {
            //explode the ids into an array
            $creatorIds = explode(',', $creatorIds);
        }

        $query = Creator::query();

        if($podcasters){
            //get most popular podcasts and then get the creators from those podcasts
            Podcast::orderBy('view_count','desc')->take(10)->get()->each(function($podcast) use ($query){
                $query->orWhere('id','=',$podcast->creator_id);
            });
        }

        // featured creators
        if ($featured) {
            $query->where('featured', '=', true);
        }

        //don't get creatorIds
        if(count($creatorIds) > 0){
            $query->whereNotIn('id',$creatorIds);
        }

        $creators = $query->orderBy('subscriber_count','desc')->paginate($perPage);
        $creators = new CreatorCollection($creators);

        return response()->json([
            'creators' => $creators,
        ]);

    }


    /* show one creator by slug
     * @param Request $request
     * @return JsonResponse
     */
    public function show($slug) {

        $creator = Creator::where('slug','=',$slug)->first();

        if(!$creator){
            return response()->json([
                'toastType' => 'warning',
                'message' => 'Creator not found'
            ]);
        }

        return response()->json([
            'creator' => new CreatorResource($creator),
        ]);
    }

    /** Toggle the featured status of a creator
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleFeatured(Request $request)
    {

        // check token can admin
        if (!Auth::user()->tokenCan('admin')) {
            return response()->json([
                'toastType' => 'warning',
                'message' => 'You do need to be an admin to do that'
            ]);
        }

        $request->validate([
            'creator_id' => 'required|integer'
        ]);

        $creator = Creator::find($request->creator_id);

        if(!$creator){
            return response()->json([
                'toastType' => 'warning',
                'message' => 'Creator not found'
            ]);
        }

        $creator->featured = !$creator->featured;
        $creator->save();
        $message = $creator->featured ? 'Creator featured status updated to true' : 'Creator featured status updated to false';

        return response()->json([
            'toastType' => $creator->featured ? 'success' : 'warning',
            'message' => $message
        ]);
    }

    /** Update a creator
     * @param Request $request
     * @return string[]
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:60|min:5|regex:/^[a-zA-Z0-9\s]+$/',
            'bio' => 'nullable|string|max:1000|min:5',
            'contact_email' => 'nullable|email',
        ]);

        Auth::user()->creator->name = $request->name;
        Auth::user()->creator->bio = json_encode($request->bio);
        Auth::user()->creator->contact_email = $request->contact_email;
        Auth::user()->creator->save();

        return [
            'toastType' => 'success',
            'message' => 'Channel updated successfully'
        ];
    }

    public function updateProfilePicture(Request $request) {

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:4096||dimensions:min_width=98,min_height=98,max_width=1000,max_height=1000',
        ]);

        if ($request->hasFile('image')) {
            if (Auth::user()->creator->avatar_url && Storage::exists(Auth::user()->creator->avatar_url)) {
                Storage::delete(Auth::user()->creator->avatar_url);
            }

            // store the image and get the path that is available to the public
            $url = Storage::url($request->file('image')->store('public/profile_pictures'));
        } else {
            $url = "https://api.dicebear.com/5.x/bottts-neutral/svg?seed=". generateRandomString(10) . "&scale=80&eyes=eva,frame1,frame2,robocop,roundFrame01,roundFrame02,shade01";
        }

        // update the user's profile picture
        Auth::user()->creator->avatar_url = $url;
        Auth::user()->creator->save();

        return [
            'toastType' => 'success',
            'message' => 'Profile picture updated successfully'
        ];
    }

    public function updateProfileBanner(Request $request) {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|nullable|max:6144||dimensions:min_width=1024,min_height=256,max_width=4096,max_height=4096',
        ]);

        if ($request->hasFile('image')) {
            if (Auth::user()->creator->banner_url && Storage::exists(Auth::user()->creator->banner_url)) {
                Storage::delete(Auth::user()->creator->banner_url);
            }

            // store the image and get the path that is available to the public
            $url = Storage::url($request->file('image')->store('public/profile_banners'));
        } else {
            $url = null;
        }

        // update the user's profile banner
        Auth::user()->creator->banner_url = $url;
        Auth::user()->creator->save();

        return [
            'toastType' => 'success',
            'message' => 'Profile banner updated successfully'
        ];

    }

}
