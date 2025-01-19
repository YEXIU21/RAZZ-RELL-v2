<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guest;
use App\Models\User;
use App\Models\Booking;

class GuestController extends Controller
{
    public function index()
    {
        $guests = Guest::all();
        return response()->json($guests);
    }

    public function getGuestById($id)
    {
        $guest = Guest::find($id);
        return response()->json($guest);
    }

    public function updateGuest(Request $request)
    {
        $guest = Guest::find($request->id);
        $guest->update($request->all());
        return response()->json($guest);
    }

    public function deleteGuest($id)
    {
        try {
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                    'status' => 404
                ], 404);
            }

            // Delete all bookings associated with this user first
            Booking::where('user_id', $id)->delete();

            // Then delete the user
            $user->delete();

            return response()->json([
                'message' => 'User and associated bookings deleted successfully',
                'status' => 200
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting user and bookings: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error deleting user and bookings: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function addGuest(Request $request)
    {
        $guest = Guest::create([
            'guest_count' => $request->guestCount,
            'guest_price' => $request->price,
        ]);

        if($guest){
            return response()->json([
                'message' => 'Guest added successfully',
                'guest' => $guest,
                'status' => 200,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Guest not added',
                'status' => 400
            ], 400);
        }
    }
}
