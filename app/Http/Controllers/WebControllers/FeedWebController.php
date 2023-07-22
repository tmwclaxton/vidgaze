<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CreatorCollection;
use App\Http\Resources\StreamCollection;
use App\Http\Resources\VideoCollection;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorInteraction;
use App\Models\StreamModels\Stream;
use App\Models\VideoModels\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FeedWebController extends Controller
{
    public function subscriptions_index()
    {
        return Inertia::render('Viewer/Feed/Subscriptions/SubscriptionsIndex');
    }
    public function channels_index()
    {
        return Inertia::render('Viewer/Feed/Subscriptions/ChannelsIndex');
    }

}
