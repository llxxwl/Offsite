<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movies extends Model
{
    use HasFactory;

    protected $table = 'movies';
    protected $fillable = [
        'id', 'title', 'en_title', 'cover', 'duration', 'release_time', 'score', 'profile',
        'rank', 'is_delete'
    ];
}
