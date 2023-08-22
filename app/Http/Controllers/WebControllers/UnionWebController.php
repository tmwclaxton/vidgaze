<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class UnionWebController extends Controller
{
    public function index()
    {
        return Inertia::render('Studio/Unions/Unions');

    }
}
