<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Package;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['package'])->get();
        return response()->json($bookings);
    }

    public function addBooking(Request $request)
    {
        try {
            \Log::info('Incoming booking request:', $request->all());
            
            // Get the package
            $package = Package::findOrFail($request->package_id);
            
            // Validate the request
            $request->validate([
                'user_id' => 'required|integer',
                'package_id' => 'required|integer',
                'full_name' => 'required|string',
                'event_date' => 'required|date',
                'event_time' => 'required',
                'venue_name' => 'required|string',
                'email' => 'required|email',
                'phone_number' => 'required',
                'payment_method' => 'required|string',
                'special_requests' => 'nullable|string',
                'packs' => [
                    'required',
                    'integer',
                    'min:' . $package->packs,
                    'max:500'
                ],
                'event_duration' => 'required|integer|min:1|max:7'
            ]);

            // Calculate price with additional guests
            $basePrice = $package->package_price;
            $basePacks = $package->packs;
            $requestedPacks = $request->packs;
            $eventDuration = $request->event_duration;
            
            // Calculate additional guest pricing
            $subtotal = $basePrice;
            if ($requestedPacks > $basePacks) {
                $additionalPacks = $requestedPacks - $basePacks;
                $additionalPricePercentage = $package->additional_price_percentage ?? 10; // Default to 10% if not set
                
                // Calculate price increase per additional guest (percentage of base price)
                $priceIncreasePerGuest = $basePrice * ($additionalPricePercentage / 100);
                
                // Add the additional guest charges to subtotal
                $subtotal = $basePrice + ($priceIncreasePerGuest * $additionalPacks);
            }

            // Apply event duration multiplier
            $totalPrice = $subtotal * $eventDuration;

            // Log the calculation details
            \Log::info('Price calculation:', [
                'basePrice' => $basePrice,
                'basePacks' => $basePacks,
                'requestedPacks' => $requestedPacks,
                'additionalPacks' => $requestedPacks - $basePacks,
                'priceIncreasePerGuest' => $priceIncreasePerGuest ?? 0,
                'subtotal' => $subtotal,
                'eventDuration' => $eventDuration,
                'totalPrice' => $totalPrice
            ]);

            // Create the booking
            $booking = Booking::create([
                'user_id' => $request->user_id,
                'package_id' => $request->package_id,
                'full_name' => $request->full_name,
                'event_date' => $request->event_date,
                'event_time' => $request->event_time,
                'venue_name' => $request->venue_name,
                'email' => $request->email,
                'phone' => $request->phone_number,
                'special_requests' => $request->special_requests,
                'payment_method' => $request->payment_method,
                'total_price' => $totalPrice,
                'status' => $request->status,
                'packs' => $request->packs,
                'event_duration' => $eventDuration
            ]);

            return response()->json([
                'message' => 'Booking created successfully',
                'booking' => $booking,
                'price_details' => [
                    'base_price' => $basePrice,
                    'additional_guest_charge' => $subtotal - $basePrice,
                    'duration_multiplier' => $eventDuration,
                    'total_price' => $totalPrice
                ],
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Booking creation error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create booking: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function getBookingById($id)
    {
        try {
            $booking = Booking::with(['package'])->findOrFail($id);
            
            return response()->json([
                'id' => $booking->id,
                'package_name' => $booking->package->package_name,
                'package_price' => $booking->package->package_price,
                'package_description' => $booking->package->package_description,
                'packs' => $booking->package->packs,
                'additional_price_percentage' => $booking->package->additional_price_percentage,
                'package_image' => $booking->package->package_image,
                'status' => $booking->status,
                'cancellation_reason' => $booking->cancellation_reason,
                'cancelled_at' => $booking->cancelled_at,
                'event_date' => $booking->event_date,
                'event_time' => $booking->event_time,
                'venue_name' => $booking->venue_name,
                'full_name' => $booking->full_name,
                'email' => $booking->email,
                'phone' => $booking->phone,
                'total_price' => $booking->total_price,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching booking details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateBooking(Request $request)
    {
        try {
            $booking = Booking::findOrFail($request->id);
            
            // Update booking with cancellation details
            $booking->update([
                'status' => $request->status,
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_at' => $request->status === 'cancelled' ? now() : null
            ]);

            // Log the update for debugging
            \Log::info('Booking updated:', [
                'id' => $booking->id,
                'status' => $booking->status,
                'reason' => $booking->cancellation_reason,
                'cancelled_at' => $booking->cancelled_at
            ]);

            return response()->json([
                'message' => 'Booking updated successfully',
                'booking' => $booking,
                'status' => 200,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating booking: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update booking',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function getBookingByUserId($id)
    {
        $bookings = Booking::where('user_id', $id)->with(['package'])->get();
        return response()->json([
            'bookings' => $bookings,
            'status' => 200
        ]);
    }

    public function deleteBooking($id)
    {
        try {
            $booking = Booking::find($id);
            
            if (!$booking) {
                return response()->json([
                    'message' => 'Booking not found',
                    'status' => 404
                ], 404);
            }

            // Delete associated payments
            $booking->payments()->delete();
            
            // Delete the booking
            $booking->delete();

            return response()->json([
                'message' => 'Booking deleted successfully',
                'status' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting booking: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
}
