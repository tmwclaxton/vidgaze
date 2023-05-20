<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jorenvh\Share\ShareFacade as Share;


class ShareController extends Controller
{
    //the 7 restful routes
    // index - show all
    // show - show one
    // create - show a page to create one of those item
    // store - when form submited persist the item
    // edit - show page to edit the item
    // update - when form submitted save the edits
    // destroy - delete one item
    public function index(Request $request) {
        $link = $request->input('link');
        $title = $request->input('title');
        //return each link as a string in an array with the key being the name of the social media
        $links = ['links' => Share::page($link,$title)
            ->facebook()
            ->twitter()
            ->pinterest()
            ->reddit()
            ->telegram()
            ->linkedin()
            ->whatsapp()
            ->getRawLinks()];

        //add vidgaze link to array
        $links['links']['vidgaze'] = $link;

        //add mailto link to array
        $links['links']['email'] = 'mailto:?subject=Check out this out on VidGaze&body='. $title .': '.$link;


        return $links;
    }


}
