<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Inertia\Inertia;


use App\Models\Channel;


class IndexController extends Controller
{
    public function index()
    {
        $channels = Channel::all();

        return Inertia::render('Index', [
            'channels' => $channels
        ]);
    }
}
