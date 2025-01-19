<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'booking_id',
        'content',
        'attachment',
        'attachment_type',
        'is_system_message',
        'is_read'
    ];

    protected $casts = [
        'is_system_message' => 'boolean',
        'is_read' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
} 