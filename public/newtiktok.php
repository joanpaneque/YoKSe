<?php

// Configuración
$store_locally = true; // Cambia a false si no deseas almacenar videos localmente

// Funciones

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getContent($url, $geturl = false)
{
    $ch = curl_init();
    $options = array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
        CURLOPT_ENCODING       => "utf-8",
        CURLOPT_AUTOREFERER    => false,
        CURLOPT_COOKIEJAR      => 'cookie.txt',
        CURLOPT_COOKIEFILE     => 'cookie.txt',
        CURLOPT_REFERER        => 'https://www.tiktok.com/',
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_MAXREDIRS      => 10,
    );
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

function getKey($playable)
{
    $ch = curl_init();
    $headers = [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
        'Accept-Encoding: gzip, deflate, br',
        'Accept-Language: en-US,en;q=0.9',
        'Range: bytes=0-200000'
    ];

    $options = array(
        CURLOPT_URL            => $playable,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
        CURLOPT_ENCODING       => "utf-8",
        CURLOPT_AUTOREFERER    => false,
        CURLOPT_COOKIEJAR      => 'cookie.txt',
        CURLOPT_COOKIEFILE     => 'cookie.txt',
        CURLOPT_REFERER        => 'https://www.tiktok.com/',
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_MAXREDIRS      => 10,
    );
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

function escape_sequence_decode($str)
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

// Controlador de API

header('Content-Type: application/json');

if (isset($_GET['url']) && !empty($_GET['url'])) {
    $url = trim($_GET['url']);
    $resp = getContent($url);
    $check = explode('"downloadAddr":"', $resp);

    if (count($check) > 1) {
        $contentURL = explode("\"", $check[1])[0];
        $contentURL = escape_sequence_decode($contentURL);
        $thumb = explode("\"", explode('"dynamicCover":"', $resp)[1])[0];
        $thumb = escape_sequence_decode($thumb);
        $username = explode('"', explode('uniqueId":"', $resp)[1])[0];
        $create_time = explode('"', explode('"createTime":"', $resp)[1])[0];
        $dt = new DateTime("@$create_time");
        $create_time = $dt->format("d M Y H:i:s A");
        $videoKey = getKey($contentURL);
        $cleanVideo = "https://api2-16-h2.musical.ly/aweme/v1/play/?video_id=$videoKey&vr_type=0&is_play_url=1&source=PackSourceEnum_PUBLISH&media_type=4";
        $cleanVideo = getContent($cleanVideo, true);

        $response = [
            'status' => 'success',
            'data' => [
                'thumbnail' => $thumb,
                'username' => $username,
                'created_at' => $create_time,
                'watermarked_video' => $store_locally ? 'user_videos/' . generateRandomString() . '.mp4' : $contentURL,
                'watermark_free_video' => $cleanVideo
            ]
        ];

        if ($store_locally) {
            // Guardar el video localmente
            $videoContent = getContent($contentURL, false);
            $filename = "user_videos/" . generateRandomString() . ".mp4";
            file_put_contents($filename, $videoContent);
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Unable to extract video information. Please check the URL and try again.'
        ];
    }

    echo json_encode($response);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No URL provided.'
    ]);
}
?>
