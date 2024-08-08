<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Video;
use App\Models\Channel;


class VideosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $videos = Video::all();
        $channels = Channel::all();

        dd([
            'videos' => $videos,
            'channels' => $channels
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $channel)
    {
        // get url and type from request
        $url = $request->input('url');
        $type = $request->input('type');

        // check if channel exists
        $channelObj = Channel::where('name', $channel)->first();

        if (!$channelObj) {
            // create channel if it doesn't exist
            $channelObj = Channel::create(['name' => $channel]);

            // create video
            $video = Video::create([
                'channel_id' => $channelObj->id,
                'type' => $type,
                'url' => $url,
                'processed' => false
            ]);

            return response()->json(['error' => 0, 'message' => 'Video subido correctamente']);
        } else {
            // check if video already exists
            $video = Video::where('channel_id', $channelObj->id)
                ->where('url', $url)
                ->first();
            if ($video) {
                return response()->json(['error' => 1, 'message' => 'Este video ya se ha sido subido en el canal de ' . $channelObj->name . '.']);
            } else {
                // create video
                $video = Video::create([
                    'channel_id' => $channelObj->id,
                    'type' => $type,
                    'url' => $url,
                    'processed' => false
                ]);
                return response()->json(['error' => 0, 'message' => 'Video subido correctamente']);
            }
        }

        return response()->json(['error' => 0, 'message' => 'IDK']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
