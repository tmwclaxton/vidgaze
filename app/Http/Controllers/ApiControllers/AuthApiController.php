<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;

use App\Http\Resources\UserResource;
use App\Models\PlaylistModels\Playlist;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Validation\Rules;

class AuthApiController extends Controller
{

    /*
 * Generate sanctum token
*/
    private function createToken($user, $remember_me = false) {
        if ($remember_me) {
            $expires = now()->addYear();
        } else {
            $expires = now()->addDay(30);
        }



        //check if user is admin
        $adminEmails = config('admins.emails');
        // if user's email is in the admin list and the user has verified their email, give them admin privileges
        if (in_array($user->email, $adminEmails) && $user->email_verified_at) {
            $token = $user->createToken($user->email, ['user', 'admin'], $expires)->plainTextToken;
        } else {
            $token = $user->createToken($user->email, ['user'], $expires)->plainTextToken;
        }

        return $token;
    }

    /*
     * Register new user
    */
    public function signup(Request $request) {
        $validatedData = $request->validate([
            'username' => 'required|string|max:30',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'min:6', 'confirmed', Rules\Password::defaults()],
            'terms' => 'required|accepted'
        ]);


        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create([
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

        $token = $this->createToken($user, false);

        //if created successfully, return user and token
        if ($user && $user->creator) {
            return response()->json([
                'user' => new UserResource($user),
                'access_token' => $token,
            ], 201);
        }

        return response()->json(null, 404);
    }




    /*
     * Generate sanctum token on successful login
    */
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember_me' => 'boolean'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $this->createToken($user, $request->remember_me);

        return response()->json([
            'access_token' => $token,
            'valid_for' => $request->remember_me ? "1 year" : "30 days",
            'user' => new UserResource($user),
        ], 200);
    }


    /*
     * Revoke token; only remove token that is used to perform logout (i.e. will not revoke all tokens)
    */
    public function logout(Request $request) {

        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();
        //$request->user->tokens()->delete(); // use this to revoke all tokens (logout from all devices)
        return response()->json(null, 200);
    }


    /*
     * Get authenticated user details
    */
    public function getAuthenticatedUser(Request $request) {
        return new UserResource($request->user());
    }


    public function sendPasswordResetLinkEmail(Request $request) {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)], 200);
        } else {
            throw ValidationException::withMessages([
                'email' => __($status)
            ]);
        }
    }
    public function resetPassword(Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if($status == Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 200);
        } else {
            throw ValidationException::withMessages([
                'email' => __($status)
            ]);
        }
    }
}
