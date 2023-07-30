<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;

use App\Http\Resources\UserResource;
use App\Models\PlaylistModels\Playlist;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Carbon;
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

        $privileges = $this->getTokenAbilitiesForUser($user);

        $token = $user->createToken($user->email, $privileges, $expires)->plainTextToken;

        return $token;
    }

    /*
     * Get token model from db using bearer token
    */
    private function getToken($request) {
        $token = $request->bearerToken();

        // grab id at start of token but ensure it is the user's token
        $tokenID = explode('|', $token)[0];

        // grab that token from the database
        $token = $request->user()->tokens()->where('id', $tokenID)->first();

        // if token is not found or is not the user's token
        if (!$token || $token->tokenable_id != $request->user()->id) {
            throw ValidationException::withMessages([
                'token' => ['Invalid token.']
            ]);
        }

        return $token;
    }

    /*
     * Token privileges decider
    */
    private function getTokenAbilitiesForUser($user) {
        //check if user is admin
        $adminEmails = config('admins.emails');

        $privileges = ['user']; //default privileges

        // if user's email is in the admin list and the user has verified their email, give them admin privileges
        if ($user->email !== null && in_array($user->email, $adminEmails) && $user->email_verified_at) {
            $privileges = array_merge($privileges, ['admin']);
        }
        // just check on user model for now
        //if ($user->email_verified_at) {
        //    $privileges = array_merge($privileges, ['email_verified']);
        //}
        return $privileges;
    }

    /*
     * Get Refreshed Token
    */
    public function refreshToken(Request $request) {
        $token = $this->getToken($request);
        $user = $request->user();
        // update token expiration by an extra 30 days if expiration is less than 30 days away
        $expires = Carbon::parse($token->expires_at);
        if ($expires->diffInDays(now()) < 30) {
            $token->expires_at = $expires->addDay(30);
        }
        $token->update([
            'expires_at' => $token->expires_at,
            'abilities' => $this->getTokenAbilitiesForUser($user)
        ]);

        return response()->json([
            'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString(),
            'abilities' => $token->abilities
        ]);


    }

    /*
     * Get Token privileges
    */
    public function checkTokenPrivileges(Request $request) {
        $token = $this->getToken($request);
        $privileges = $token->abilities;
        return response()->json([
            'privileges' => $privileges
        ]);
    }


    /*
     * Register new user
    */
    public function signup(Request $request) {
        $validatedData = $request->validate([
            'username' => 'required|string|max:30',
            'email' => 'required|email|max:255',
            'password' => ['required', 'min:6', 'confirmed', Rules\Password::defaults()],
            'terms' => 'required|accepted'
        ]);

        // check if user is already registered if so log them in
        $user = User::where('email', $request->email)->first();
        if ($user) {
            return $this->login($request, true);
        }

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

        //if created successfully, return user and token and valid for in seconds
        if ($user && $user->creator) {
            return response()->json([
                'access_token' => $token,
                'valid_for' => 3600 * 2,
                'user' => new UserResource($user),
            ], 201);
        }

        return response()->json(null, 404);
    }



    /*
     * Generate sanctum token on successful login
    */
    public function login(Request $request, $register_redirect = false) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember_me' => 'boolean'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            if ($register_redirect) {
                $message = ['email' => ['A user already exists with this email']];
            } else {
                $message = ['email' => ['The provided credentials are incorrect.']];
            }
            throw ValidationException::withMessages($message);
        }

        $token = $this->createToken($user, $request->remember_me);

        // return valid for in seconds 6 months if remember me, 2 hours if not
        return response()->json([
            'access_token' => $token,
            'valid_for' => $request->remember_me ? (60 * 60 * 24 * 30 * 6) : (3600 * 2),
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
        return response()->json([
            'user' => new UserResource($request->user()),
            'subscription_ids' => $request->user()->creator->subscriptions->pluck('id')->toArray(),
            'admin' => $request->user()->isAdmin()

        ]);
    }


    /*
     * Send password reset link
    */
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


    /*
     * Reset password
    */
    public function resetPassword(Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

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

    /*
     * Confirm password
    */
    public function confirmPassword(Request $request) {
        $request->validate([
            'password' => 'required',
        ]);

        $user = $request->user();

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        return response()->json(['message' => 'Password confirmed'], 200);
    }

    /*
     * Update password
    */
    public function updatePassword(Request $request) {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make($request->password)
        ])->setRememberToken(Str::random(60));

        $user->save();

        return response()->json([
            'toastType' => 'success',
            'message' => 'Password updated'
        ], 200);
    }

    /*
     * Send email verification link
    */
    public function sendEmailVerificationLink(Request $request) {
        $request->validate(['email' => 'required|email']);

        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'toastType' => 'normal',
                'message' => 'Email already verified'
            ], 200);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'toastType' => 'success',
            'message' => 'Email verification link sent'
        ], 200);
    }

    /*
     * Verify email
    */
    public function verifyEmail(Request $request)
    {
        $request->validate(['id' => 'required|integer', 'hash' => 'required|string']);

        $user = User::find($request->id);


        if ($user->hasVerifiedEmail()) {
//            return redirect()->intended(RouteServiceProvider::HOME)->with('status', 'Email already verified');
            return response()->json([
                'toastType' => 'success',
                'message' => 'Email already verified'], 200);
        }

        if (!hash_equals((string)$request->hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'toastType' => 'warning',
                'message' => 'Invalid verification link'], 400);
//            return redirect()->intended(RouteServiceProvider::HOME)->with('status', 'Invalid verification link');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'toastType' => 'success',
            'message' => 'Email verified'
        ], 200);
//        return redirect()->intended(RouteServiceProvider::HOME)->with('status', 'Email verified');


    }
}
