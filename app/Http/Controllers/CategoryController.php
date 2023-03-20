<?php

namespace App\Http\Controllers;


use App\Models\Category;
use App\Models\Creator;
use App\Models\Stream;
use App\Models\Union;
use App\Models\Video;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{

    public function index()
    {

    }


    public function create()
    {
        //
    }


    public function store()
    {

    }

    public function show(Category $category) {
//        dd($category->id);
        return view('category', [
                'category' => $category,
            ]
        );
    }


    public function edit()
    {
    }


    public function update()
    {

    }


    public function destroy()
    {
        //
    }
}
