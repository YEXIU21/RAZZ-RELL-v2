<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_name',
        'package_description',
        'package_price',
        'price_per_day',
        'price_increase_per_day',
        'packs',
        'package_image',
        'package_type',
        'package_inclusion',
        'status',
        'flag',
        'additional_price_percentage',
        'rating',
        'reviews_count'
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
