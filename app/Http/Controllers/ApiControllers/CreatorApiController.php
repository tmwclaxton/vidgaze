<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CreatorCollection;
use App\Models\CreatorModels\Creator;
use App\Models\PodcastModels\Podcast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'creator' => $creator,
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
            'message' => 'Creator updated successfully'
        ];
    }

    public function updateProfilePicture() {

    }

    public function updateProfileBanner() {

    }

}
