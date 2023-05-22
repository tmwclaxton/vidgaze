<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Mail\SupportMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function home()
    {
        return Inertia::render('Viewer/Home/Homepage');
    }

    public function about()
    {
        return Inertia::render('Viewer/Landing/Landing');
    }

    public function terms()
    {
        return Inertia::render('Legal/Terms');
    }
    public function privacy()
    {
        return Inertia::render('Legal/Policy');
    }
    public function support()
    {
        return Inertia::render('Legal/Support');
    }

    public function sendSupportEmail(Request $request) {
        $request->validate([
            'subject' => 'required',
            'email' => 'required|email',
            'message' => 'required',
            'name' => 'required'
        ]);

        $data = [
            'subject' => $request->subject,
            'email' => $request->email,
            'message' => $request->message,
            'name' => $request->name
        ];

        //send message to support slack channel

        Mail::to('support@vidgaze.tv')->send(new SupportMail($data));
    }
}
