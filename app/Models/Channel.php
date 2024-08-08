<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Video;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function unprocessedVideos()
    {
        return $this->videos()->where('processed', false);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }
}
