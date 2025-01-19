<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function getMessages(Request $request)
    {
        try {
            $messages = Message::with('user')
                ->where(function($query) {
                    $query->where('sender_id', Auth::id())
                        ->orWhere('receiver_id', Auth::id());
                })
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'messages' => $messages,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching messages: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'content' => 'required|string',
                'receiver_id' => 'required|exists:users,id'
            ]);

            $message = Message::create([
                'content' => $request->content,
                'sender_id' => Auth::id(),
                'receiver_id' => $request->receiver_id,
                'is_read' => false
            ]);

            $message->load('user');

            return response()->json([
                'message' => $message,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error sending message: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function getUnreadCount()
    {
        try {
            $count = Message::where('receiver_id', Auth::id())
                ->where('is_read', false)
                ->count();

            return response()->json([
                'count' => $count,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error getting unread count: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        try {
            Message::where('receiver_id', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'message' => 'Messages marked as read',
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error marking messages as read: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
} 