<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function getActiveUsers()
    {
        // Get today's active users
        $todayActiveUsers = User::where('status', 'active')
            ->whereDate('created_at', today())
            ->count();
        
        // Get yesterday's active users
        $yesterdayActiveUsers = User::where('status', 'active')
            ->whereDate('created_at', today()->subDay())
            ->count();
        
        // Calculate trend percentage
        $trend = $yesterdayActiveUsers > 0 
            ? (($todayActiveUsers - $yesterdayActiveUsers) / $yesterdayActiveUsers) * 100 
            : 100; // If no users yesterday, show 100% increase

        return response()->json([
            'active_users_count' => $todayActiveUsers,
            'trend' => round($trend, 1)
        ]);
    }

    public function getRatings()
    {
        try {
            // Get all ratings
            $ratings = Rating::where('status', 'active')
                ->select('rating', 'created_at')
                ->get();

            // Get today's ratings
            $todayRatings = $ratings->where('created_at', '>=', today());
            
            // Get yesterday's ratings
            $yesterdayRatings = $ratings->where('created_at', '>=', today()->subDay())
                ->where('created_at', '<', today());

            // Calculate current satisfaction
            $currentAverage = $todayRatings->isEmpty() ? 0 : $todayRatings->avg('rating');
            $satisfactionPercentage = ($currentAverage / 5) * 100;

            // Calculate trend
            if ($todayRatings->isNotEmpty() && $yesterdayRatings->isEmpty()) {
                $trend = 100;
            } else {
                $yesterdayAverage = $yesterdayRatings->isEmpty() ? 0 : $yesterdayRatings->avg('rating');
                $trend = $yesterdayAverage > 0 
                    ? (($currentAverage - $yesterdayAverage) / $yesterdayAverage) * 100 
                    : ($currentAverage > 0 ? 100 : 0);
            }

            return response()->json([
                'satisfaction_percentage' => round($satisfactionPercentage, 1),
                'trend' => round($trend, 1),
                'ratings' => $ratings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'satisfaction_percentage' => 0,
                'trend' => 0,
                'ratings' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getRevenueOverview()
    {
        // Get today's revenue from payments
        $todayRevenue = Payment::whereDate('created_at', today())
            ->sum('amount');
        
        // Get yesterday's revenue from payments
        $yesterdayRevenue = Payment::whereDate('created_at', today()->subDay())
            ->sum('amount');

        // Calculate average payment amount for today
        $todayPaymentsCount = Payment::whereDate('created_at', today())
            ->count();
        
        $averageOrderValue = $todayPaymentsCount > 0 
            ? $todayRevenue / $todayPaymentsCount 
            : 0;

        // Calculate revenue trend
        $revenueTrend = $yesterdayRevenue > 0 
            ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100 
            : ($todayRevenue > 0 ? 100 : 0); // Show 100% if there's revenue today but none yesterday

        return response()->json([
            'totalRevenue' => round($todayRevenue, 2),
            'averageOrderValue' => round($averageOrderValue, 2),
            'revenueTrend' => round($revenueTrend, 1),
            'revenueData' => [
                'today' => $todayRevenue,
                'yesterday' => $yesterdayRevenue
            ]
        ]);
    }

    public function getBookingsByEventType()
    {
        // Get today's bookings with their payments
        $todayBookings = Booking::with(['package', 'payments'])
            ->whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->get()
            ->groupBy('package.package_type');

        // Transform the data to include both count and revenue
        $bookingStats = $todayBookings->map(function ($bookings) {
            return [
                'count' => $bookings->count(),
                'revenue' => $bookings->sum(function ($booking) {
                    return $booking->payments->sum('amount');
                })
            ];
        });

        // Get yesterday's total bookings and revenue
        $yesterdayStats = Booking::with('payments')
            ->whereDate('created_at', today()->subDay())
            ->where('status', '!=', 'cancelled')
            ->get();
        
        $yesterdayCount = $yesterdayStats->count();
        $yesterdayRevenue = $yesterdayStats->sum(function ($booking) {
            return $booking->payments->sum('amount');
        });

        // Calculate totals and trends
        $totalBookings = $bookingStats->sum('count');
        $totalRevenue = $bookingStats->sum(function ($stat) {
            return $stat['revenue'];
        });

        // Calculate trends
        $bookingTrend = $yesterdayCount > 0 
            ? (($totalBookings - $yesterdayCount) / $yesterdayCount) * 100 
            : ($totalBookings > 0 ? 100 : 0);

        // Calculate completion rate based on payments
        $completedBookings = Booking::whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();
        
        $completionRate = $totalBookings > 0 
            ? ($completedBookings / $totalBookings) * 100 
            : 0;

        return response()->json([
            'bookingsByType' => $bookingStats,
            'totalBookings' => $totalBookings,
            'totalRevenue' => round($totalRevenue, 2),
            'bookingTrend' => round($bookingTrend, 1),
            'completionRate' => round($completionRate, 1)
        ]);
    }

    public function getMonthlyRevenue()
    {
        $monthlyRevenue = Payment::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(amount) as total')
        )
        ->whereYear('created_at', now()->year)
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // Initialize array with zeros for all months
        $revenueByMonth = array_fill(0, 12, 0);

        // Fill in actual revenue data
        foreach ($monthlyRevenue as $revenue) {
            $revenueByMonth[$revenue->month - 1] = round($revenue->total, 2);
        }

        return response()->json([
            'monthlyRevenue' => $revenueByMonth
        ]);
    }

    public function getPopularPackages()
    {
        try {
            $popularPackages = Booking::with(['package', 'payments', 'ratings'])
                ->whereDate('created_at', '>=', now()->subDays(30))  // Last 30 days
                ->where('status', '!=', 'cancelled')
                ->get()
                ->groupBy('package_id')
                ->map(function ($bookings) {
                    $package = $bookings->first()->package;
                    return [
                        'package_name' => $package->name,
                        'event_type' => $package->package_type,
                        'bookings' => $bookings->count(),
                        'revenue' => $bookings->sum(function ($booking) {
                            return $booking->payments->sum('amount');
                        }),
                        'rating' => $bookings->flatMap->ratings->avg('rating') ?? 0
                    ];
                })
                ->sortByDesc('revenue')
                ->take(3)
                ->values();

            return response()->json([
                'popular_packages' => $popularPackages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'popular_packages' => [],
                'error' => $e->getMessage()
            ]);
        }
    }
} 