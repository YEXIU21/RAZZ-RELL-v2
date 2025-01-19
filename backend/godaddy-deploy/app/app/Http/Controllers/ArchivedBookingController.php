<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ArchivedBooking;
use Illuminate\Http\Request;

class ArchivedBookingController extends Controller
{
    public function archive($id)
    {
        try {
            $booking = Booking::findOrFail($id);
            
            // Create archived booking record
            ArchivedBooking::create([
                'original_booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'package_id' => $booking->package_id,
                'email' => $booking->email,
                'phone' => $booking->phone,
                'full_name' => $booking->full_name,
                'event_date' => $booking->event_date,
                'event_time' => $booking->event_time,
                'venue_name' => $booking->venue_name,
                'special_requests' => $booking->special_requests,
                'event_duration' => $booking->event_duration,
                'payment_method' => $booking->payment_method,
                'total_price' => $booking->total_price,
                'terms_accepted' => $booking->terms_accepted,
                'status' => $booking->status
            ]);

            // Delete original booking
            $booking->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Booking archived successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Archive booking error: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Error archiving booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $archivedBooking = ArchivedBooking::findOrFail($id);
            
            // Restore to original bookings table
            Booking::create([
                'user_id' => $archivedBooking->user_id,
                'package_id' => $archivedBooking->package_id,
                'email' => $archivedBooking->email,
                'phone' => $archivedBooking->phone,
                'full_name' => $archivedBooking->full_name,
                'event_date' => $archivedBooking->event_date,
                'event_time' => $archivedBooking->event_time,
                'venue_name' => $archivedBooking->venue_name,
                'special_requests' => $archivedBooking->special_requests,
                'event_duration' => $archivedBooking->event_duration,
                'payment_method' => $archivedBooking->payment_method,
                'total_price' => $archivedBooking->total_price,
                'terms_accepted' => $archivedBooking->terms_accepted,
                'status' => $archivedBooking->status
            ]);

            // Delete from archived bookings
            $archivedBooking->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Booking restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error restoring booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $archivedBookings = ArchivedBooking::orderBy('created_at', 'desc')->get();
            return response()->json($archivedBookings);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error fetching archived bookings: ' . $e->getMessage()
            ], 500);
        }
    }
} 