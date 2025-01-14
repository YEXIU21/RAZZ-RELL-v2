<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'main_image_url',
        'images_url',
        'status',
        'flag'
    ];

    protected $casts = [
        'images_url' => 'array'
    ];
}
