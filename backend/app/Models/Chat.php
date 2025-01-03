<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'read'
    ];

    protected $attributes = [
        'read' => false
    ];

    protected $casts = [
        'read' => 'boolean'
    ];

    // Define relationships
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Static method to check if chat is allowed between users based on their roles
    public static function canChat($senderId, $receiverId)
    {
        $sender = User::find($senderId);
        $receiver = User::find($receiverId);

        if (!$sender || !$receiver) {
            return false;
        }

        // Get roles
        $senderRole = $sender->role;
        $receiverRole = $receiver->role;

        // Define allowed chat combinations
        $allowedCombinations = [
            // User can chat with Admin and Staff
            ['sender' => 'user', 'receiver' => 'admin'],
            ['sender' => 'user', 'receiver' => 'staff'],
            
            // Admin can chat with Staff and User
            ['sender' => 'admin', 'receiver' => 'staff'],
            ['sender' => 'admin', 'receiver' => 'user'],
            
            // Staff can chat with Admin and User
            ['sender' => 'staff', 'receiver' => 'admin'],
            ['sender' => 'staff', 'receiver' => 'user'],
        ];

        // Check if the combination is allowed
        foreach ($allowedCombinations as $combination) {
            if ($senderRole === $combination['sender'] && $receiverRole === $combination['receiver']) {
                return true;
            }
        }

        return false;
    }
} 