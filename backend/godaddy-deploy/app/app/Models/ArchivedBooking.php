<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivedBooking extends Model
{
    protected $fillable = [
        'original_booking_id',
        'user_id',
        'package_id',
        'email',
        'phone',
        'full_name',
        'event_date',
        'event_time',
        'venue_name',
        'special_requests',
        'event_duration',
        'payment_method',
        'total_price',
        'terms_accepted',
        'status'
    ];
} 