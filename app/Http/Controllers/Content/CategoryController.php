<?php

namespace App\Http\Controllers\Content;


use App\Http\Controllers\Controller;
use App\Models\Category;

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
