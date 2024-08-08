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

        dd($request->all());
        // get url and type from request
        $url = $request->input('url');
        $type = $request->input('type');

        // check if channel exists
        $channel = Channel::where('name', $channel)->first();

        if (!$channel) {
            // create channel if it doesn't exist
            $channel = Channel::create(['name' => $channel]);
        } else {
            // check if video already exists
            $video = Video::where('channel_id', $channel->id)
                ->where('url', $url)
                ->first();
            if ($video) {
                return response()->json(['error' => 1, 'message' => 'Este video ya ha sido subido en el canal de ' . $channel->name]);
            } else {
                // create video
                $video = Video::create([
                    'channel_id' => $channel->id,
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
