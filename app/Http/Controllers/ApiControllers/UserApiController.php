<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserApiController extends Controller
{


    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
        ]);
        // do everything manually to avoid mass assignment
        $request->user()->first_name = $request->first_name;
        $request->user()->last_name = $request->last_name;
        $request->user()->email = $request->email;

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return response()->json([
            'toastType' => 'success',
            'message' => 'Profile updated successfully.',
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        // destroy all users tokens
        $user->tokens()->delete();

        $user->delete();

        return response()->json([
            'toastType' => 'success',
            'message' => 'Account deleted successfully.',
        ]);
    }



}
