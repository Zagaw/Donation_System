<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Donation;
use App\Models\Request as ReceiverRequest;
use App\Models\Matches;
use App\Models\Interest;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Get overview statistics
     */
    public function getOverview()
    {
        try {
            $totalUsers = User::count();
            $totalDonations = Donation::count();
            $totalRequests = ReceiverRequest::count();
            $totalMatches = Matches::count();
            
            // Calculate changes (compare with previous month)
            $lastMonth = now()->subMonth();
            
            $previousUsers = User::where('created_at', '<', $lastMonth)->count();
            $previousDonations = Donation::where('created_at', '<', $lastMonth)->count();
            $previousRequests = ReceiverRequest::where('created_at', '<', $lastMonth)->count();
            $previousMatches = Matches::where('created_at', '<', $lastMonth)->count();

            return response()->json([
                'total_users' => $totalUsers,
                'total_donations' => $totalDonations,
                'total_requests' => $totalRequests,
                'total_matches' => $totalMatches,
                'user_change' => $this->calculateChange($previousUsers, $totalUsers),
                'donation_change' => $this->calculateChange($previousDonations, $totalDonations),
                'request_change' => $this->calculateChange($previousRequests, $totalRequests),
                'match_change' => $this->calculateChange($previousMatches, $totalMatches),
                'active_users' => User::where('created_at', '>=', now()->subDays(30))->count(),
                'successful_matches' => Matches::where('status', 'completed')->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get monthly activity data
     */
    public function getMonthlyActivity(Request $request)
    {
        try {
            $months = $request->get('months', 6);
            
            $data = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $startOfMonth = $date->copy()->startOfMonth();
                $endOfMonth = $date->copy()->endOfMonth();
                
                $data[] = [
                    'month' => $date->format('M Y'),
                    'donations' => Donation::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                    'requests' => ReceiverRequest::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                    'matches' => Matches::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                ];
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get category distribution
     */
    public function getCategoryDistribution()
    {
        try {
            $donationsByCategory = Donation::select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->get();

            $requestsByCategory = ReceiverRequest::select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->get();

            // Merge categories
            $categories = [];
            foreach ($donationsByCategory as $item) {
                $cat = $item->category ?? 'Other';
                $categories[$cat] = ($categories[$cat] ?? 0) + $item->count;
            }
            foreach ($requestsByCategory as $item) {
                $cat = $item->category ?? 'Other';
                $categories[$cat] = ($categories[$cat] ?? 0) + $item->count;
            }

            // Format for chart
            $result = [
                'labels' => array_keys($categories),
                'data' => array_values($categories)
            ];

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get user growth data
     */
    public function getUserGrowth(Request $request)
    {
        try {
            $months = $request->get('months', 6);
            
            $data = [];
            $cumulative = 0;
            
            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $startOfMonth = $date->copy()->startOfMonth();
                $endOfMonth = $date->copy()->endOfMonth();
                
                $monthlyNew = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
                $cumulative += $monthlyNew;
                
                $data[] = [
                    'month' => $date->format('M Y'),
                    'new_users' => $monthlyNew,
                    'total_users' => $cumulative,
                    'active_users' => User::where('created_at', '<=', $endOfMonth)->count(),
                ];
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get donation statistics
     */
    public function getDonationStats(Request $request)
    {
        try {
            $dateRange = $request->get('range', 'month');
            
            $startDate = $this->getStartDate($dateRange);
            
            $stats = [
                'total' => Donation::where('created_at', '>=', $startDate)->count(),
                'pending' => Donation::where('status', 'pending')->where('created_at', '>=', $startDate)->count(),
                'approved' => Donation::where('status', 'approved')->where('created_at', '>=', $startDate)->count(),
                'matched' => Donation::where('status', 'matched')->where('created_at', '>=', $startDate)->count(),
                'executed' => Donation::where('status', 'executed')->where('created_at', '>=', $startDate)->count(),
                'completed' => Donation::where('status', 'completed')->where('created_at', '>=', $startDate)->count(),
                'rejected' => Donation::where('status', 'rejected')->where('created_at', '>=', $startDate)->count(),
                'avg_quantity' => Donation::where('created_at', '>=', $startDate)->avg('quantity') ?? 0,
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get request statistics
     */
    public function getRequestStats(Request $request)
    {
        try {
            $dateRange = $request->get('range', 'month');
            
            $startDate = $this->getStartDate($dateRange);
            
            $stats = [
                'total' => ReceiverRequest::where('created_at', '>=', $startDate)->count(),
                'pending' => ReceiverRequest::where('status', 'pending')->where('created_at', '>=', $startDate)->count(),
                'approved' => ReceiverRequest::where('status', 'approved')->where('created_at', '>=', $startDate)->count(),
                'matched' => ReceiverRequest::where('status', 'matched')->where('created_at', '>=', $startDate)->count(),
                'executed' => ReceiverRequest::where('status', 'executed')->where('created_at', '>=', $startDate)->count(),
                'completed' => ReceiverRequest::where('status', 'completed')->where('created_at', '>=', $startDate)->count(),
                'rejected' => ReceiverRequest::where('status', 'rejected')->where('created_at', '>=', $startDate)->count(),
                'avg_quantity' => ReceiverRequest::where('created_at', '>=', $startDate)->avg('quantity') ?? 0,
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get match statistics
     */
    public function getMatchStats(Request $request)
    {
        try {
            $dateRange = $request->get('range', 'month');
            
            $startDate = $this->getStartDate($dateRange);
            
            $stats = [
                'total' => Matches::where('created_at', '>=', $startDate)->count(),
                'approved' => Matches::where('status', 'approved')->where('created_at', '>=', $startDate)->count(),
                'executed' => Matches::where('status', 'executed')->where('created_at', '>=', $startDate)->count(),
                'completed' => Matches::where('status', 'completed')->where('created_at', '>=', $startDate)->count(),
                'interest_based' => Matches::where('matchType', 'interest')->where('created_at', '>=', $startDate)->count(),
                'manual' => Matches::where('matchType', 'manual')->where('created_at', '>=', $startDate)->count(),
                'success_rate' => $this->calculateSuccessRate($startDate),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get feedback statistics
     */
    public function getFeedbackStats(Request $request)
    {
        try {
            $dateRange = $request->get('range', 'month');
            
            $startDate = $this->getStartDate($dateRange);
            
            $avgRating = Feedback::where('created_at', '>=', $startDate)->avg('rating') ?? 0;
            $totalFeedback = Feedback::where('created_at', '>=', $startDate)->count();
            
            $ratingDistribution = [
                5 => Feedback::where('rating', 5)->where('created_at', '>=', $startDate)->count(),
                4 => Feedback::where('rating', 4)->where('created_at', '>=', $startDate)->count(),
                3 => Feedback::where('rating', 3)->where('created_at', '>=', $startDate)->count(),
                2 => Feedback::where('rating', 2)->where('created_at', '>=', $startDate)->count(),
                1 => Feedback::where('rating', 1)->where('created_at', '>=', $startDate)->count(),
            ];

            return response()->json([
                'avg_rating' => round($avgRating, 1),
                'total_feedback' => $totalFeedback,
                'rating_distribution' => $ratingDistribution,
                'satisfaction_rate' => $totalFeedback > 0 
                    ? round(($ratingDistribution[5] + $ratingDistribution[4]) / $totalFeedback * 100, 1)
                    : 0
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate comprehensive report
     */
    public function generateReport(Request $request)
    {
        try {
            $type = $request->get('type', 'overview');
            $range = $request->get('range', 'month');
            
            $startDate = $this->getStartDate($range);
            
            $report = [
                'generated_at' => now()->toDateTimeString(),
                'date_range' => $range,
                'report_type' => $type,
                'data' => []
            ];

            switch ($type) {
                case 'overview':
                    $report['data'] = [
                        'users' => $this->getUserStats($startDate),
                        'donations' => $this->getDonationStats($request),
                        'requests' => $this->getRequestStats($request),
                        'matches' => $this->getMatchStats($request),
                        'feedback' => $this->getFeedbackStats($request),
                    ];
                    break;
                case 'donations':
                    $report['data'] = $this->getDetailedDonationReport($startDate);
                    break;
                case 'requests':
                    $report['data'] = $this->getDetailedRequestReport($startDate);
                    break;
                case 'users':
                    $report['data'] = $this->getDetailedUserReport($startDate);
                    break;
            }

            return response()->json($report);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get saved reports
     */
    public function getSavedReports()
    {
        try {
            // This could be stored in a database table
            // For now, return mock data
            $reports = [
                [
                    'id' => 1,
                    'title' => 'Monthly Performance Report',
                    'date' => now()->subDays(5)->format('Y-m-d'),
                    'type' => 'PDF',
                    'size' => '2.4 MB',
                    'url' => '#'
                ],
                [
                    'id' => 2,
                    'title' => 'User Activity Analysis',
                    'date' => now()->subDays(10)->format('Y-m-d'),
                    'type' => 'Excel',
                    'size' => '1.8 MB',
                    'url' => '#'
                ],
                [
                    'id' => 3,
                    'title' => 'Donation Category Report',
                    'date' => now()->subDays(15)->format('Y-m-d'),
                    'type' => 'PDF',
                    'size' => '3.2 MB',
                    'url' => '#'
                ],
                [
                    'id' => 4,
                    'title' => 'Quarterly Summary',
                    'date' => now()->subDays(20)->format('Y-m-d'),
                    'type' => 'PDF',
                    'size' => '4.1 MB',
                    'url' => '#'
                ],
            ];

            return response()->json($reports);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Helper methods
    private function calculateChange($previous, $current)
    {
        if ($previous == 0) return '+100%';
        $change = (($current - $previous) / $previous) * 100;
        return ($change >= 0 ? '+' : '') . round($change, 1) . '%';
    }

    private function getStartDate($range)
    {
        switch ($range) {
            case 'week':
                return now()->subDays(7);
            case 'month':
                return now()->subDays(30);
            case 'quarter':
                return now()->subDays(90);
            case 'year':
                return now()->subDays(365);
            default:
                return now()->subDays(30);
        }
    }

    private function calculateSuccessRate($startDate)
    {
        $total = Matches::where('created_at', '>=', $startDate)->count();
        if ($total == 0) return 0;
        
        $completed = Matches::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->count();
        
        return round(($completed / $total) * 100, 1);
    }

    private function getUserStats($startDate)
    {
        return [
            'total' => User::where('created_at', '>=', $startDate)->count(),
            'donors' => User::where('role', 'donor')->where('created_at', '>=', $startDate)->count(),
            'receivers' => User::where('role', 'receiver')->where('created_at', '>=', $startDate)->count(),
            'admins' => User::where('role', 'admin')->where('created_at', '>=', $startDate)->count(),
        ];
    }

    private function getDetailedDonationReport($startDate)
    {
        return Donation::with('donor.user')
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($donation) {
                return [
                    'id' => $donation->donationId,
                    'item' => $donation->itemName,
                    'quantity' => $donation->quantity,
                    'category' => $donation->category,
                    'status' => $donation->status,
                    'donor' => $donation->donor->user->name ?? 'Unknown',
                    'created_at' => $donation->created_at->format('Y-m-d'),
                ];
            });
    }

    private function getDetailedRequestReport($startDate)
    {
        return ReceiverRequest::with('receiver.user')
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->requestId,
                    'item' => $request->itemName,
                    'quantity' => $request->quantity,
                    'category' => $request->category,
                    'status' => $request->status,
                    'receiver' => $request->receiver->user->name ?? 'Unknown',
                    'created_at' => $request->created_at->format('Y-m-d'),
                ];
            });
    }

    private function getDetailedUserReport($startDate)
    {
        return User::where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->userId,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at->format('Y-m-d'),
                ];
            });
    }

    /**
     * Download report as CSV
     */
    public function downloadCSV(Request $request)
    {
        try {
            $type = $request->get('type', 'overview');
            $range = $request->get('range', 'month');
            
            $startDate = $this->getStartDate($range);
            
            // Generate filename
            $filename = $type . '-report-' . now()->format('Y-m-d-His') . '.csv';
            
            // Create CSV content
            $callback = function() use ($type, $startDate) {
                $file = fopen('php://output', 'w');
                
                // Add UTF-8 BOM for Excel compatibility
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                switch ($type) {
                    case 'donations':
                        $this->writeDonationsCSV($file, $startDate);
                        break;
                    case 'requests':
                        $this->writeRequestsCSV($file, $startDate);
                        break;
                    case 'users':
                        $this->writeUsersCSV($file, $startDate);
                        break;
                    default:
                        $this->writeOverviewCSV($file, $startDate);
                }
                
                fclose($file);
            };
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Write overview CSV
     */
    private function writeOverviewCSV($file, $startDate)
    {
        // Summary Section
        fputcsv($file, ['COMMUNITYCONNECT SYSTEM REPORT']);
        fputcsv($file, ['Generated:', now()->format('Y-m-d H:i:s')]);
        fputcsv($file, ['Date Range:', $startDate->format('Y-m-d') . ' to ' . now()->format('Y-m-d')]);
        fputcsv($file, []);
        
        // Statistics
        fputcsv($file, ['SUMMARY STATISTICS']);
        fputcsv($file, ['Metric', 'Value']);
        fputcsv($file, ['Total Users', User::count()]);
        fputcsv($file, ['Total Donations', Donation::count()]);
        fputcsv($file, ['Total Requests', ReceiverRequest::count()]);
        fputcsv($file, ['Total Matches', Matches::count()]);
        fputcsv($file, ['Completed Matches', Matches::where('status', 'completed')->count()]);
        fputcsv($file, ['Average Rating', round(Feedback::avg('rating') ?? 0, 1) . '/5']);
        fputcsv($file, []);
        
        // Status Breakdown
        fputcsv($file, ['DONATION STATUS']);
        fputcsv($file, ['Status', 'Count']);
        fputcsv($file, ['Pending', Donation::where('status', 'pending')->count()]);
        fputcsv($file, ['Approved', Donation::where('status', 'approved')->count()]);
        fputcsv($file, ['Matched', Donation::where('status', 'matched')->count()]);
        fputcsv($file, ['Executed', Donation::where('status', 'executed')->count()]);
        fputcsv($file, ['Completed', Donation::where('status', 'completed')->count()]);
        fputcsv($file, ['Rejected', Donation::where('status', 'rejected')->count()]);
        fputcsv($file, []);
        
        fputcsv($file, ['REQUEST STATUS']);
        fputcsv($file, ['Status', 'Count']);
        fputcsv($file, ['Pending', ReceiverRequest::where('status', 'pending')->count()]);
        fputcsv($file, ['Approved', ReceiverRequest::where('status', 'approved')->count()]);
        fputcsv($file, ['Matched', ReceiverRequest::where('status', 'matched')->count()]);
        fputcsv($file, ['Executed', ReceiverRequest::where('status', 'executed')->count()]);
        fputcsv($file, ['Completed', ReceiverRequest::where('status', 'completed')->count()]);
        fputcsv($file, ['Rejected', ReceiverRequest::where('status', 'rejected')->count()]);
        fputcsv($file, []);
        
        // Recent Donations
        fputcsv($file, ['RECENT DONATIONS']);
        fputcsv($file, ['ID', 'Item', 'Quantity', 'Category', 'Donor', 'Status', 'Date']);
        
        $recentDonations = Donation::with('donor.user')
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
        
        foreach ($recentDonations as $donation) {
            fputcsv($file, [
                $donation->donationId,
                $donation->itemName,
                $donation->quantity,
                $donation->category ?? 'N/A',
                $donation->donor?->user?->name ?? 'Unknown',
                $donation->status,
                $donation->created_at->format('Y-m-d H:i')
            ]);
        }
        fputcsv($file, []);
        
        // Recent Requests
        fputcsv($file, ['RECENT REQUESTS']);
        fputcsv($file, ['ID', 'Item', 'Quantity', 'Category', 'Receiver', 'Status', 'Date']);
        
        $recentRequests = ReceiverRequest::with('receiver.user')
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
        
        foreach ($recentRequests as $request) {
            fputcsv($file, [
                $request->requestId,
                $request->itemName,
                $request->quantity,
                $request->category ?? 'N/A',
                $request->receiver?->user?->name ?? 'Unknown',
                $request->status,
                $request->created_at->format('Y-m-d H:i')
            ]);
        }
    }

    /**
     * Write donations CSV
     */
    private function writeDonationsCSV($file, $startDate)
    {
        fputcsv($file, ['DONATIONS REPORT']);
        fputcsv($file, ['Generated:', now()->format('Y-m-d H:i:s')]);
        fputcsv($file, ['Date Range:', $startDate->format('Y-m-d') . ' to ' . now()->format('Y-m-d')]);
        fputcsv($file, []);
        
        fputcsv($file, ['ID', 'Item', 'Quantity', 'Category', 'Donor', 'Donor Email', 'Status', 'Created Date', 'NRC Number']);
        
        $donations = Donation::with('donor.user')
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->get();
        
        foreach ($donations as $donation) {
            fputcsv($file, [
                $donation->donationId,
                $donation->itemName,
                $donation->quantity,
                $donation->category ?? 'N/A',
                $donation->donor?->user?->name ?? 'Unknown',
                $donation->donor?->user?->email ?? 'N/A',
                $donation->status,
                $donation->created_at->format('Y-m-d H:i'),
                $donation->nrcNumber ?? 'N/A'
            ]);
        }
    }

    /**
     * Write requests CSV
     */
    private function writeRequestsCSV($file, $startDate)
    {
        fputcsv($file, ['REQUESTS REPORT']);
        fputcsv($file, ['Generated:', now()->format('Y-m-d H:i:s')]);
        fputcsv($file, ['Date Range:', $startDate->format('Y-m-d') . ' to ' . now()->format('Y-m-d')]);
        fputcsv($file, []);
        
        fputcsv($file, ['ID', 'Item', 'Quantity', 'Category', 'Receiver', 'Receiver Email', 'Status', 'Created Date', 'NRC Number']);
        
        $requests = ReceiverRequest::with('receiver.user')
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->get();
        
        foreach ($requests as $request) {
            fputcsv($file, [
                $request->requestId,
                $request->itemName,
                $request->quantity,
                $request->category ?? 'N/A',
                $request->receiver?->user?->name ?? 'Unknown',
                $request->receiver?->user?->email ?? 'N/A',
                $request->status,
                $request->created_at->format('Y-m-d H:i'),
                $request->nrcNumber ?? 'N/A'
            ]);
        }
    }

    /**
     * Write users CSV
     */
    private function writeUsersCSV($file, $startDate)
    {
        fputcsv($file, ['USERS REPORT']);
        fputcsv($file, ['Generated:', now()->format('Y-m-d H:i:s')]);
        fputcsv($file, ['Date Range:', $startDate->format('Y-m-d') . ' to ' . now()->format('Y-m-d')]);
        fputcsv($file, []);
        
        fputcsv($file, ['ID', 'Name', 'Email', 'Role', 'Phone', 'Address', 'Joined Date', 'Status']);
        
        $users = User::where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->get();
        
        foreach ($users as $user) {
            fputcsv($file, [
                $user->userId,
                $user->name,
                $user->email,
                $user->role,
                $user->phone ?? 'N/A',
                $user->address ?? 'N/A',
                $user->created_at->format('Y-m-d H:i'),
                'Active'
            ]);
        }
    }
}