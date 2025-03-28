<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Fetch reports data from API
            $reports = ReportsController::getAllReports();
            // dd($reports);
            if (empty($reports)) {
                throw new \Exception("No exception reports found");
            }

            // Convert to collection for processing
            $reports = collect($reports);

            // 1. Status Distribution
            $statusData = $reports->groupBy('status')->map->count();

            // 2. Risk Rate Distribution
            $riskData = $reports->groupBy('riskRate')->map->count();

            // 3. Department Distribution
            $departmentData = $reports->groupBy('department')->map->count()->sortDesc();

            // 4. Monthly Trends
            $monthlyTrends = $reports->groupBy(function ($report) {
                return \Carbon\Carbon::parse($report->occurrenceDate)->format('Y-m');
            })->map->count()->sortKeys();

            // 5. Process Type Distribution
            $processTypeData = $reports->groupBy('processType')->map->count();

            // 6. Resolution Time Analysis
            $resolutionTimes = $reports->filter(function ($report) {
                return $report->status === 'RESOLVED' && $report->dateClosed;
            })->map(function ($report) {
                $occurred = \Carbon\Carbon::parse($report->occurrenceDate);
                $closed = \Carbon\Carbon::parse($report->dateClosed);
                return $closed->diffInDays($occurred);
            });

            // 7. Auditor Performance
            $auditorData = $reports->groupBy('auditorName')
                ->map->count()
                ->sortDesc()
                ->take(5);

            // 8. Top High-Risk Exceptions
            $highRiskExceptions = $reports->where('riskRate', 'High')
                ->sortByDesc('occurrenceDate')
                ->take(5)
                ->values();

            // 9. Recent Activity
            $recentActivity = $reports->sortByDesc('updatedAt')
                ->take(8)
                ->map(function ($report) {
                    return [
                        'type' => $report->status === 'RESOLVED' ? 'resolved' : 'new',
                        'message' => Str::limit($report->exception, 40) . " ({$report->department})",
                        'time' => \Carbon\Carbon::parse($report->updatedAt)->diffForHumans(),
                        'risk' => $report->riskRate
                    ];
                });

            // 10. SLA Compliance Rate
            $slaCompliance = $reports->filter(function ($report) {
                return $report->status === 'RESOLVED' && $report->dateClosed && $report->proposeResolutionDate;
            })->avg(function ($report) {
                $target = \Carbon\Carbon::parse($report->proposeResolutionDate);
                $actual = \Carbon\Carbon::parse($report->dateClosed);
                return $actual->lte($target) ? 100 : 0;
            });

            return view('dashboard', [
                // Chart Data
                'statusData' => $statusData,
                'riskData' => $riskData,
                'departmentData' => $departmentData,
                'monthlyTrends' => $monthlyTrends,
                'processTypeData' => $processTypeData,

                // Metrics
                'totalExceptions' => $reports->count(),
                'resolvedCount' => $reports->where('status', 'RESOLVED')->count(),
                'pendingCount' => $reports->where('status', 'PENDING')->count(),
                'avgResolutionDays' => $resolutionTimes->isNotEmpty() ?
                    round($resolutionTimes->avg(), 1) : 0,
                'slaComplianceRate' => round($slaCompliance, 1),

                // Widget Data
                'highRiskExceptions' => $highRiskExceptions,
                'recentActivity' => $recentActivity,
                'topAuditors' => $auditorData,

                // Raw Data for Tables
                'allReports' => $reports->sortByDesc('occurrenceDate')->take(10)
            ]);
        } catch (\Exception $e) {
            // Log error
            Log::error('Dashboard data error: ' . $e->getMessage());

            // Return empty data set
            return view('dashboard', [
                'statusData' => collect(),
                'riskData' => collect(),
                'departmentData' => collect(),
                'monthlyTrends' => collect(),
                'processTypeData' => collect(),
                'totalExceptions' => 0,
                'resolvedCount' => 0,
                'pendingCount' => 0,
                'avgResolutionDays' => 0,
                'slaComplianceRate' => 0,
                'highRiskExceptions' => collect(),
                'recentActivity' => collect(),
                'topAuditors' => collect(),
                'allReports' => collect(),
                'error' => 'Failed to load dashboard data. Please try again later.'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
