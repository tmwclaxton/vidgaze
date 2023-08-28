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
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(RouteServiceProvider::HOME.'?verified=1')
            : Inertia::render('Auth/VerifyEmail');
    }


    /**
     * show verifying email page
     */
    public function VerifyEmailRedirect($id,$hash): Response
    {
        return Inertia::render('Auth/VerifyingEmail', [
            'id' => $id,
            'hash' => $hash,
        ]);
    }

    /**
     * Show the confirm password view.
     */
    public function confirmPassword(): Response
    {
        return Inertia::render('Auth/ConfirmPassword');
    }

}
