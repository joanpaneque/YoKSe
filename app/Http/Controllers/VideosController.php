<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Video;
use App\Models\Channel;
use DateTime;


class VideosController extends Controller
{

    private $store_locally = true;
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

            $info = $this->getVideoInfo($url);

            // return response()->  json(['error' => 0, 'message' => json_encode($info['data']['thumbnail'])]);

            // create video
            $video = Video::create([
                'channel_id' => $channelObj->id,
                'type' => $type,
                'url' => $url,
                'processed' => false,
                'username' => $info['data']['username'],
                'watermarked_video' => $info['data']['watermarked_video'],
                'thumbnail' => $info['data']['thumbnail'],
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
                $info = $this->getVideoInfo($url);

                // return response()->json(['error' => 0, 'message' => json_encode($info['data']['thumbnail'])]);

                // create video
                $video = Video::create([
                    'channel_id' => $channelObj->id,
                    'type' => $type,
                    'url' => $url,
                    'processed' => false,
                    'username' => $info['data']['username'],
                    'watermarked_video' => $info['data']['watermarked_video'],
                    'thumbnail' => $info['data']['thumbnail'],
                ]);
                return response()->json(['error' => 0, 'message' => 'Video subido correctamente']);
            }
        }

        return response()->json(['error' => 0, 'message' => 'IDK']);
    }

    public function getVideoInfo($url)
    {
        if (!$url) {
            return response()->json([
                'status' => 'error',
                'message' => 'No URL provided.'
            ]);
        }

        $resp = $this->getContent($url);
        $check = explode('"downloadAddr":"', $resp);

        if (count($check) > 1) {
            $contentURL = explode('"', $check[1])[0];
            $contentURL = $this->escapeSequenceDecode($contentURL);
            $thumb = explode('"', explode('"dynamicCover":"', $resp)[1])[0];
            $thumb = $this->escapeSequenceDecode($thumb);
            $username = explode('"', explode('uniqueId":"', $resp)[1])[0];
            $create_time = explode('"', explode('"createTime":"', $resp)[1])[0];
            $dt = new DateTime("@$create_time");
            $create_time = $dt->format("d M Y H:i:s A");
            $videoKey = $this->getKey($contentURL);
            $cleanVideo = "https://api2-16-h2.musical.ly/aweme/v1/play/?video_id=$videoKey&vr_type=0&is_play_url=1&source=PackSourceEnum_PUBLISH&media_type=4";
            $cleanVideo = $this->getContent($cleanVideo, true);


            $randomString = $this->generateRandomString();

            $response = [
                'status' => 'success',
                'data' => [
                    'thumbnail' => $thumb,
                    'username' => $username,
                    'created_at' => $create_time,
                    'watermarked_video' => $this->store_locally ? 'user_videos/' . $randomString . '.mp4' : $contentURL,
                    'watermark_free_video' => $cleanVideo
                ]
            ];

            if ($this->store_locally) {
                // Guardar el video localmente
                $videoContent = $this->getContent($contentURL, false);
                $filename = "user_videos/" . $randomString . ".mp4";
                Storage::disk('public')->put($filename, $videoContent);
            }
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Unable to extract video information. Please check the URL and try again.'
            ];
        }

        return $response;
    }


    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function getContent($url, $geturl = false)
    {
        $ch = curl_init();
        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
            CURLOPT_ENCODING       => "utf-8",
            CURLOPT_AUTOREFERER    => false,
            CURLOPT_COOKIEJAR      => storage_path('app/cookie.txt'),
            CURLOPT_COOKIEFILE     => storage_path('app/cookie.txt'),
            CURLOPT_REFERER        => 'https://www.tiktok.com/',
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_MAXREDIRS      => 10,
        ];
        curl_setopt_array($ch, $options);
        if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($geturl === true) {
            return curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        }
        curl_close($ch);
        return strval($data);
    }

    private function getKey($playable)
    {
        $ch = curl_init();
        $headers = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: en-US,en;q=0.9',
            'Range: bytes=0-200000'
        ];

        $options = [
            CURLOPT_URL            => $playable,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
            CURLOPT_ENCODING       => "utf-8",
            CURLOPT_AUTOREFERER    => false,
            CURLOPT_COOKIEJAR      => storage_path('app/cookie.txt'),
            CURLOPT_COOKIEFILE     => storage_path('app/cookie.txt'),
            CURLOPT_REFERER        => 'https://www.tiktok.com/',
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_MAXREDIRS      => 10,
        ];
        curl_setopt_array($ch, $options);
        if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $tmp = explode("vid:", $data);
        if (count($tmp) > 1) {
            $key = trim(explode("%", $tmp[1])[0]);
            $key = trim(explode(".", $key)[0]);
        } else {
            $key = "";
        }
        return $key;
    }

    private function escapeSequenceDecode($str)
    {
        $regex = '/\\\u([dD][89abAB][\da-fA-F]{2})\\\u([dD][c-fC-F][\da-fA-F]{2})
                  |\\\u([\da-fA-F]{4})/sx';

        return preg_replace_callback($regex, function ($matches) {
            if (isset($matches[3])) {
                $cp = hexdec($matches[3]);
            } else {
                $lead = hexdec($matches[1]);
                $trail = hexdec($matches[2]);

                $cp = ($lead << 10) + $trail + 0x10000 - (0xD800 << 10) - 0xDC00;
            }

            if ($cp > 0xD7FF && 0xE000 > $cp) {
                $cp = 0xFFFD;
            }

            if ($cp < 0x80) {
                return chr($cp);
            } else if ($cp < 0xA0) {
                return chr(0xC0 | $cp >> 6) . chr(0x80 | $cp & 0x3F);
            }

            return html_entity_decode('&#' . $cp . ';');
        }, $str);
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
