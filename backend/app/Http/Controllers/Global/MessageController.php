<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Booking;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function getMessages($bookingId)
    {
        try {
            $messages = Message::with('user')
                ->where('booking_id', $bookingId)
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'messages' => $messages,
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get messages: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'content' => 'required|string',
                'attachment' => 'nullable|file|max:5120', // 5MB max
                'attachment_type' => 'nullable|string|in:payment_receipt,image'
            ]);

            $message = new Message([
                'user_id' => auth()->id(),
                'booking_id' => $request->booking_id,
                'content' => $request->content,
                'is_system_message' => false
            ]);

            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('chat_attachments', 'public');
                $message->attachment = $path;
                $message->attachment_type = $request->attachment_type;
            }

            $message->save();

            // Load the user relationship for the response
            $message->load('user');

            return response()->json([
                'message' => $message,
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send message: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        try {
            $request->validate([
                'message_ids' => 'required|array',
                'message_ids.*' => 'exists:messages,id'
            ]);

            Message::whereIn('id', $request->message_ids)
                ->where('user_id', '!=', auth()->id())
                ->update(['is_read' => true]);

            return response()->json([
                'message' => 'Messages marked as read',
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark messages as read: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function getUnreadCount($bookingId)
    {
        try {
            $count = Message::where('booking_id', $bookingId)
                ->where('user_id', '!=', auth()->id())
                ->where('is_read', false)
                ->count();

            return response()->json([
                'count' => $count,
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get unread count: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
} 