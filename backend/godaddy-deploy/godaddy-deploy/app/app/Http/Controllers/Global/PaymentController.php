<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Log incoming request
            Log::info('New payment request received:', [
                'request_data' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Add validation
            $validated = $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'amount' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            // Check if booking exists first
            $booking = Booking::findOrFail($request->booking_id);
            $totalPaid = $booking->payments()->sum('amount');

            Log::info('Booking found:', [
                'booking_id' => $booking->id,
                'total_price' => $booking->total_price,
                'total_paid_so_far' => $totalPaid,
                'current_status' => $booking->status
            ]);

            // Create payment
            $payment = new Payment();
            $payment->booking_id = $validated['booking_id'];
            $payment->amount = $validated['amount'];
            $payment->payment_type = 'cash';
            $payment->notes = $validated['notes'] ?? null;
            $payment->save();

            // Calculate new total paid
            $newTotalPaid = $totalPaid + $validated['amount'];
            $remainingBalance = $booking->total_price - $newTotalPaid;

            // Update booking status based on payment
            if ($newTotalPaid >= $booking->total_price) {
                // If payment meets or exceeds total price, mark as completed
                $booking->update(['status' => 'completed']);
                Log::info('Booking marked as completed:', [
                    'booking_id' => $booking->id,
                    'total_paid' => $newTotalPaid,
                    'total_price' => $booking->total_price,
                    'overpayment' => max(0, $newTotalPaid - $booking->total_price)
                ]);
            } elseif ($totalPaid == 0) {
                // If this is the first payment, mark as confirmed
                $booking->update(['status' => 'confirmed']);
                Log::info('Booking marked as confirmed:', [
                    'booking_id' => $booking->id,
                    'first_payment' => $validated['amount']
                ]);
            }

            DB::commit();

            Log::info('Payment transaction completed successfully:', [
                'payment_id' => $payment->id,
                'booking_id' => $booking->id,
                'amount' => $payment->amount,
                'new_total_paid' => $newTotalPaid,
                'remaining_balance' => $remainingBalance,
                'new_booking_status' => $booking->fresh()->status
            ]);

            return response()->json([
                'message' => 'Payment recorded successfully',
                'payment' => $payment,
                'booking' => $booking->fresh(),
                'total_paid' => $newTotalPaid,
                'remaining_balance' => $remainingBalance,
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed:', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to record payment: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function getPayments($bookingId)
    {
        try {
            $booking = Booking::findOrFail($bookingId);
            $payments = $booking->payments()
                ->orderBy('created_at', 'desc')
                ->get();
            
            $totalPaid = $payments->sum('amount');

            return response()->json([
                'payments' => $payments,
                'total_paid' => $totalPaid,
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch payments',
                'status' => 'error'
            ], 500);
        }
    }
} 