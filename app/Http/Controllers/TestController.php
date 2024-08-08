<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;




class TestController extends Controller
{
    public function index()
    {
        // Scrap this: https://www.instagram.com/reel/C8wgS4Es8se

        $url = 'https://www.instagram.com/reel/C8wgS4Es8se';

        $html = file_get_contents($url);

        dd($html);

    }
}
