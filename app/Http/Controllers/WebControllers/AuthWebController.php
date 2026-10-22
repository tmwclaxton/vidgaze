<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

//use Inertia\Response;

class AuthWebController extends Controller
{
    /**
     * Display the registration view.
     */
    public function register(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Display the login view.
     */
    public function login(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Display the password reset link request view.
     */
    public function forgotPassword(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }
    /**
     * Display the password reset view.
     */
    public function resetPassword(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Display the email verification prompt.
     */
    public function verifyEmail(Request $request): RedirectResponse|Response
    {
        // the SPA authenticates with sanctum tokens, so the web request usually has no session user
        return $request->user()?->hasVerifiedEmail()
            ? redirect()->intended(RouteServiceProvider::HOME.'?verified=1')
            : Inertia::render('Auth/VerifyEmail');
    }


    /**
     * Verify the email straight from the signed link. The signed middleware already checks the
     * signature and expiry, so no session or token is needed and the link works on any device.
     */
    public function VerifyEmailRedirect($id, $hash): Response
    {
        $user = \App\Models\User::find($id);

        if (!$user || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            $status = 'invalid';
        } elseif ($user->hasVerifiedEmail()) {
            $status = 'already-verified';
        } else {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }
            $status = 'verified';
        }

        return Inertia::render('Auth/VerifyingEmail', ['status' => $status]);
    }

    /**
     * Show the confirm password view.
     */
    public function confirmPassword(): Response
    {
        return Inertia::render('Auth/ConfirmPassword');
    }

}
