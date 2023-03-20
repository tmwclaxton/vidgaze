<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        Redirect::setIntendedUrl(url()->previous());
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
//            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->creator()->create([
            'name' => $request->username, //okay for duplicates because channels from different platforms have same name
            'avatar_url' => "https://api.dicebear.com/5.x/bottts-neutral/svg?seed=". generateRandomString(10) . "&scale=80&eyes=eva,frame1,frame2,robocop,roundFrame01,roundFrame02,shade01",
            'slug' => substr(strtoupper(sha1(time())), 0, 16), //may want to change this - extremely unlikely to duplicate, but would break the user account if happened
            'coins' => '25000'
        ]);

        Playlist::create([
            'creator_id' => $user->creator->id,
            'name' => 'Disliked Videos',
            'server_made' => true,
            'slug' => generateRandomString(16),
            'visibility' => 'hidden'
        ]);
        $playlistNames = ['Liked Videos', 'Watch Later', 'History'];
        foreach ($playlistNames as $playlistName) {
            Playlist::create([
                'creator_id' => $user->creator->id,
                'name' => $playlistName,
                'server_made' => true,
                'slug' => generateRandomString(16),
                'visibility' => 'private'
            ]);
        }
        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended(RouteServiceProvider::HOME)->with('success', 'Welcome to VidGaze!');
    }
}
