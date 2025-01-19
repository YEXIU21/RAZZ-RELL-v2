<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'full_name',
        'event_date',
        'event_time',
        'venue_name',
        'email',
        'phone',
        'special_requests',
        'payment_method',
        'total_price',
        'terms_accepted',
        'event_duration',
        'status',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'event_date' => 'date',
        'event_time' => 'datetime',
        'terms_accepted' => 'boolean'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'event_date',
        'cancelled_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getRemainingBalanceAttribute()
    {
        return $this->total_price - $this->total_paid;
    }

    public function isFullyPaid()
    {
        return $this->total_paid >= $this->total_price;
    }
}
