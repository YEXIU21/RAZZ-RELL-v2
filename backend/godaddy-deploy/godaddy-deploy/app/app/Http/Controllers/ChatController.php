<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $senderId = Auth::id();
        $receiverId = $request->receiver_id;

        // Check if chat is allowed based on roles
        if (!Chat::canChat($senderId, $receiverId)) {
            return response()->json([
                'message' => 'You are not allowed to chat with this user based on role restrictions.'
            ], 403);
        }

        $chat = Chat::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $request->message,
        ]);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $chat
        ]);
    }

    public function getMessages(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $currentUserId = Auth::id();
        $otherUserId = $request->user_id;

        // Check if chat is allowed based on roles
        if (!Chat::canChat($currentUserId, $otherUserId)) {
            return response()->json([
                'message' => 'You cannot view messages with this user based on role restrictions.'
            ], 403);
        }

        $messages = Chat::where(function($query) use ($currentUserId, $otherUserId) {
            $query->where('sender_id', $currentUserId)
                  ->where('receiver_id', $otherUserId);
        })->orWhere(function($query) use ($currentUserId, $otherUserId) {
            $query->where('sender_id', $otherUserId)
                  ->where('receiver_id', $currentUserId);
        })
        ->with(['sender:id,name', 'receiver:id,name'])
        ->orderBy('created_at', 'asc')
        ->get();

        return response()->json([
            'data' => $messages
        ]);
    }

    public function getUnreadCount()
    {
        $userId = Auth::id();
        
        $count = Chat::where('receiver_id', $userId)
            ->where('read', false)
            ->count();

        return response()->json([
            'count' => $count,
            'status' => 'success'
        ]);
    }
} 