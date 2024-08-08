<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Channel;

class Video extends Model
{
    use HasFactory;

    protected $fillable = ['channel_id', 'type', 'url', 'processed'];


    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}
