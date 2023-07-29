<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SearchWebController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->q;
        return inertia('Viewer/Search/Search', [
            'searchQuery' => $searchQuery,
        ]);
    }

}
