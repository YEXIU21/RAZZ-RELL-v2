<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DashboardControler extends Controller
{
    /**
     * Get event type statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEventTypeStats()
    {
        try {
            $stats = \DB::table('bookings')
                ->join('packages', 'bookings.package_id', '=', 'packages.id')
                ->select('packages.package_type', \DB::raw('count(*) as count'))
                ->groupBy('packages.package_type')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->package_type => $item->count];
                });

            $eventTypes = [
                'Wedding' => $stats['Wedding'] ?? 0,
                'Debut' => $stats['Debut'] ?? 0,
                'Christening' => $stats['Christening'] ?? 0, 
                'Party' => $stats['Party'] ?? 0,
                'Other' => $stats['Other'] ?? 0
            ];

            return response()->json([
                'status' => 200,
                'data' => [
                    'labels' => array_keys($eventTypes),
                    'values' => array_values($eventTypes)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error retrieving event type statistics',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get monthly revenue data for current year
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonthlyRevenue()
    {
        try {
            $currentYear = date('Y');
            
            $monthlyRevenue = \DB::table('bookings')
                ->join('packages', 'bookings.package_id', '=', 'packages.id')
                ->select(
                    \DB::raw('MONTH(bookings.event_date) as month'),
                    \DB::raw('SUM(packages.package_price) as total_revenue')
                )
                ->whereIn('bookings.status', ['pending', 'confirmed', 'completed'])
                ->whereYear('bookings.event_date', $currentYear)
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Initialize array with 0 revenue for all months
            $revenueData = array_fill(1, 12, 0);

            // Fill in actual revenue data
            foreach ($monthlyRevenue as $revenue) {
                $revenueData[$revenue->month] = (float)$revenue->total_revenue;
            }

            // Convert to array values to ensure sequential array
            $revenueData = array_values($revenueData);

            return response()->json([
                'data' => $revenueData,
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching monthly revenue: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * Get satisfaction rate based on ratings
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSatisfactionRate()
    {
        try {
            $currentMonth = date('m');
            $lastMonth = date('m', strtotime('-1 month'));
            $currentYear = date('Y');

            // Get current month ratings
            $currentMonthRatings = \DB::table('ratings')
                ->where('status', '=', 'active')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->avg('rating');

            // Get last month ratings
            $lastMonthRatings = \DB::table('ratings')
                ->where('status', '=', 'active') 
                ->whereMonth('created_at', $lastMonth)
                ->whereYear('created_at', $currentYear)
                ->avg('rating');

            // Calculate satisfaction rate (assuming rating is out of 5)
            $currentRate = $currentMonthRatings ? ($currentMonthRatings / 5) * 100 : 0;
            $lastRate = $lastMonthRatings ? ($lastMonthRatings / 5) * 100 : 0;
            
            // Calculate trend percentage
            $trend = $lastRate ? (($currentRate - $lastRate) / $lastRate) * 100 : 0;

            return response()->json([
                'status' => 200,
                'data' => [
                    'satisfactionRate' => round($currentRate, 1),
                    'trend' => round($trend, 1)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error retrieving satisfaction rate',
                'error' => $e->getMessage()
            ]);
        }
    }
}
