<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;

class RateController extends Controller
{
    public function addRating(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'package_id' => 'required|exists:packages,id',
                'user_id' => 'required|exists:users,id',
                'booking_id' => 'required|exists:bookings,id',
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'required|string'
            ]);

            // Create new rating
            $rating = new Rating();
            $rating->package_id = $request->package_id;
            $rating->user_id = $request->user_id;
            $rating->booking_id = $request->booking_id;
            $rating->rating = $request->rating;
            $rating->review = $request->review;
            $rating->status = 'active';
            $rating->save();

            // Get updated package data with new rating
            $package = Package::with(['ratings' => function($query) {
                $query->where('status', 'active');
            }])->find($request->package_id);

            // Calculate and save the average rating and review count
            $avgRating = $package->ratings->avg('rating') ?? 0;
            $package->rating = round($avgRating, 1);
            $package->reviews_count = $package->ratings->count();
            $package->save();

            // Get the latest review with user data
            $latestReview = Rating::with(['user:id,first_name,last_name,avatar'])
                ->where('id', $rating->id)
                ->first();

            return response()->json([
                'message' => 'Rating added successfully',
                'package' => $package,
                'review' => [
                    'id' => $latestReview->id,
                    'rating' => (int)$latestReview->rating,
                    'review' => $latestReview->review,
                    'user_name' => $latestReview->user->first_name . ' ' . $latestReview->user->last_name,
                    'user_avatar' => $latestReview->user->avatar ? asset('storage/' . $latestReview->user->avatar) : asset('storage/avatars/defaultAvatar.png'),
                    'created_at' => $latestReview->created_at->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error adding rating: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error adding rating',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllRatings($id)
    {
        $ratings = Rating::where('user_id', $id)->get();
        return response()->json(['ratings' => $ratings]);
    }

    public function getPackageReviews($packageId)
    {
        try {
            // Get all active reviews for the package
            $reviews = Rating::where('package_id', $packageId)
                ->where('status', 'active')
                ->with(['user:id,first_name,last_name,avatar'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Map reviews to include user data
            $mappedReviews = $reviews->map(function($review) {
                return [
                    'id' => $review->id,
                    'rating' => (int)$review->rating,
                    'review' => $review->review,
                    'user_name' => $review->user->first_name . ' ' . $review->user->last_name,
                    'user_avatar' => $review->user->avatar ? asset('storage/' . $review->user->avatar) : asset('storage/avatars/defaultAvatar.png'),
                    'created_at' => $review->created_at->format('Y-m-d H:i:s')
                ];
            });

            // Calculate rating distribution
            $ratingDistribution = [
                5 => 0,
                4 => 0,
                3 => 0,
                2 => 0,
                1 => 0
            ];

            foreach ($reviews as $review) {
                $rating = (int)$review->rating;
                if (isset($ratingDistribution[$rating])) {
                    $ratingDistribution[$rating]++;
                }
            }

            // Calculate average rating
            $averageRating = $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : 0;

            return response()->json([
                'reviews' => $mappedReviews,
                'distribution' => $ratingDistribution,
                'total' => $reviews->count(),
                'average' => $averageRating
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting package reviews: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error getting package reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
