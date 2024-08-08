<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Http;


use App\Models\Channel;

class Video extends Model
{
    use HasFactory;

    protected $fillable = ['channel_id', 'type', 'url', 'processed', 'thumbnail', 'username', 'watermarked_video', 'watermark_free_video'];


    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}
