<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    //the 7 restful routes
    // index - show all
    // show - show one
    // create - show a page to create one of those item
    // store - when form submited persist the item
    // edit - show page to edit the item
    // update - when form submitted save the edits
    // destroy - delete one item

    public function index() {

    }

    public function create()
    {
        //
    }

    public function store() {

    }

    public function show(Video $video) {

    }

    public function edit()
    {
//        return view('studio/customise');
    }

    public function update() {

    }

    public function destroy()
    {
        //
    }
}
