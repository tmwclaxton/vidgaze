<?php

namespace App\Http\Controllers\Tools;


use App\Http\Controllers\Controller;
use App\Models\CreatorModels\Creator;
use App\Models\Union;

class UnionController extends Controller
{

    public function index()
    {
        return view('studio/unionise', [
            'unions' => Union::all()
        ]);
    }


    public function create()
    {
        //
    }


    public function store()
    {

    }


    public function show(Creator $creator)
    {

    }


    public function edit(Creator $creator)
    {
    }


    public function update()
    {

    }


    public function destroy(Creator $creator)
    {
        //
    }
}
