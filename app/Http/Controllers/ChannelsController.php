<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Channel;
use Inertia\Inertia;

class ChannelsController extends Controller
{
    public function show(Channel $channel)
    {
        return Inertia::render('Channel', [
            'channel' => $channel,
            'videos' => $channel->unprocessedVideos()->get()
        ]);
    }
}
