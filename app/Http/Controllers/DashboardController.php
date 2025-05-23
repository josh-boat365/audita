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
    // public function index()
    // {
    //     try {
    //         // Fetch reports data from API
    //         $reports = ReportsController::getAllReports();
    //         // dd($reports);
    //         if (empty($reports)) {
    //             throw new \Exception("No exception reports found");
    //         }

    //         // Convert to collection for processing
    //         $reports = collect($reports);

    //         // 1. Status Distribution
    //         $statusData = $reports->groupBy('status')->map->count();

    //         // 2. Risk Rate Distribution
    //         $riskData = $reports->groupBy('riskRate')->map->count();

    //         // 3. Department Distribution
    //         $departmentData = $reports->groupBy('department')->map->count()->sortDesc();

    //         // 4. Monthly Trends
    //         $monthlyTrends = $reports->groupBy(function ($report) {
    //             return \Carbon\Carbon::parse($report->occurrenceDate)->format('Y-m');
    //         })->map->count()->sortKeys();

    //         // 5. Process Type Distribution
    //         $processTypeData = $reports->groupBy('processType')->map->count();

    //         // 6. Resolution Time Analysis
    //         $resolutionTimes = $reports->filter(function ($report) {
    //             return $report->status === 'RESOLVED' && $report->dateClosed;
    //         })->map(function ($report) {
    //             $occurred = \Carbon\Carbon::parse($report->occurrenceDate);
    //             $closed = \Carbon\Carbon::parse($report->dateClosed);
    //             return $closed->diffInDays($occurred);
    //         });

    //         // 7. Auditor Performance
    //         $auditorData = $reports->groupBy('auditorName')
    //             ->map->count()
    //             ->sortDesc()
    //             ->take(5);

    //         // 8. Top High-Risk Exceptions
    //         $highRiskExceptions = $reports->where('riskRate', 'High')
    //             ->sortByDesc('occurrenceDate')
    //             ->take(5)
    //             ->values();

    //         // 9. Recent Activity
    //         $recentActivity = $reports->sortByDesc('updatedAt')
    //             ->take(8)
    //             ->map(function ($report) {
    //                 return [
    //                     'type' => $report->status === 'RESOLVED' ? 'resolved' : 'new',
    //                     'message' => Str::limit($report->exception, 40) . " ({$report->department})",
    //                     'time' => \Carbon\Carbon::parse($report->updatedAt)->diffForHumans(),
    //                     'risk' => $report->riskRate
    //                 ];
    //             });

    //         // 10. SLA Compliance Rate
    //         $slaCompliance = $reports->filter(function ($report) {
    //             return $report->status === 'RESOLVED' && $report->dateClosed && $report->proposeResolutionDate;
    //         })->avg(function ($report) {
    //             $target = \Carbon\Carbon::parse($report->proposeResolutionDate);
    //             $actual = \Carbon\Carbon::parse($report->dateClosed);
    //             return $actual->lte($target) ? 100 : 0;
    //         });

    //         return view('dashboard', [
    //             // Chart Data
    //             'statusData' => $statusData,
    //             'riskData' => $riskData,
    //             'departmentData' => $departmentData,
    //             'monthlyTrends' => $monthlyTrends,
    //             'processTypeData' => $processTypeData,

    //             // Metrics
    //             'totalExceptions' => $reports->count(),
    //             'resolvedCount' => $reports->where('status', 'RESOLVED')->count(),
    //             'pendingCount' => $reports->where('status', 'PENDING')->count(),
    //             'avgResolutionDays' => $resolutionTimes->isNotEmpty() ?
    //                 round($resolutionTimes->avg(), 1) : 0,
    //             'slaComplianceRate' => round($slaCompliance, 1),

    //             // Widget Data
    //             'highRiskExceptions' => $highRiskExceptions,
    //             'recentActivity' => $recentActivity,
    //             'topAuditors' => $auditorData,

    //             // Raw Data for Tables
    //             'allReports' => $reports->sortByDesc('occurrenceDate')->take(10)
    //         ]);
    //     } catch (\Exception $e) {
    //         // Log error
    //         Log::error('Dashboard data error: ' . $e->getMessage());

    //         // Return empty data set
    //         return view('dashboard', [
    //             'statusData' => collect(),
    //             'riskData' => collect(),
    //             'departmentData' => collect(),
    //             'monthlyTrends' => collect(),
    //             'processTypeData' => collect(),
    //             'totalExceptions' => 0,
    //             'resolvedCount' => 0,
    //             'pendingCount' => 0,
    //             'avgResolutionDays' => 0,
    //             'slaComplianceRate' => 0,
    //             'highRiskExceptions' => collect(),
    //             'recentActivity' => collect(),
    //             'topAuditors' => collect(),
    //             'allReports' => collect(),
    //             'error' => 'Failed to load dashboard data. Please try again later.'
    //         ]);
    //     }
    // }


    public function index()
    {
        try {
            $reports = ReportsController::getAllReports();
            $reports = collect($reports);

            if (empty($reports)) {
                throw new \Exception("No exception reports found");
            }

            // 1. Status Distribution (Enhanced with percentages)
            $statusData = $reports->groupBy('status')->map(function ($items, $status) use ($reports) {
                return [
                    'count' => $items->count(),
                    'percentage' => round(($items->count() / $reports->count()) * 100, 1)
                ];
            });

            // 2. Risk Rate Distribution (Enhanced with severity levels)
            $riskData = $reports->groupBy('riskRate')->map(function ($items, $risk) {
                $severity = match ($risk) {
                    'High' => 3,
                    'Medium' => 2,
                    'Low' => 1,
                    default => 0
                };
                return [
                    'count' => $items->count(),
                    'severity' => $severity
                ];
            })->sortByDesc('severity');

            // Risk Data colors prepared colors
            $riskColors = $riskData->keys()->mapWithKeys(function ($key) {
                return [
                    $key => match ($key) {
                        'High' => '#dc3545',    // Red
                        'Medium' => '#ffc107',   // Yellow
                        'Low' => '#28a745',      // Green
                        default => '#6c757d'     // Grey (fallback)
                    }
                ];
            });

            // 3. Department Distribution (With trend comparison)
            $currentMonth = now()->format('Y-m');
            $prevMonth = now()->subMonth()->format('Y-m');

            $departmentData = $reports->groupBy('department')->map(function ($items) use ($reports, $currentMonth, $prevMonth) {
                $current = $items->filter(fn($r) => \Carbon\Carbon::parse($r->occurrenceDate)->format('Y-m') === $currentMonth);
                $previous = $items->filter(fn($r) => \Carbon\Carbon::parse($r->occurrenceDate)->format('Y-m') === $prevMonth);

                // Fixed trend calculation
                $trend = $previous->count() > 0
                    ? round(($current->count() - $previous->count()) / $previous->count() * 100, 1)
                    : ($current->count() > 0 ? 100 : 0);

                return [
                    'count' => $items->count(),
                    'trend' => $trend,
                    'percentage' => round(($items->count() / $reports->count()) * 100, 1)
                ];
            })->sortByDesc('count');

            // 4. Monthly Trends (12-month rolling)
            $monthlyTrends = $reports->groupBy(function ($report) {
                return \Carbon\Carbon::parse($report->occurrenceDate)->format('Y-m');
            })->map(function ($items, $month) {
                return [
                    'count' => $items->count(),
                    'resolved' => $items->where('status', 'RESOLVED')->count(),
                    'month' => \Carbon\Carbon::parse($month)->format('M Y')
                ];
            })->sortKeys()->take(-12);

            // 5. Process Type Distribution (With resolution rates)
            $processTypeData = $reports->groupBy('processType')->map(function ($items) {
                $resolved = $items->where('status', 'RESOLVED')->count();
                return [
                    'count' => $items->count(),
                    'resolution_rate' => $items->count() > 0 ? round(($resolved / $items->count()) * 100, 1) : 0
                ];
            })->sortByDesc('count');

            // 6. Resolution Time Analysis (Bucketed)
            $resolutionTimes = $reports->filter(function ($report) {
                return $report->status === 'RESOLVED' && $report->dateClosed;
            })->map(function ($report) {
                $occurred = \Carbon\Carbon::parse($report->occurrenceDate);
                $closed = \Carbon\Carbon::parse($report->dateClosed);
                return $closed->diffInDays($occurred);
            });

            $resolutionBuckets = [
                '0-1 days' => $resolutionTimes->filter(fn($days) => $days <= 1)->count(),
                '2-3 days' => $resolutionTimes->filter(fn($days) => $days > 1 && $days <= 3)->count(),
                '4-7 days' => $resolutionTimes->filter(fn($days) => $days > 3 && $days <= 7)->count(),
                '1-2 weeks' => $resolutionTimes->filter(fn($days) => $days > 7 && $days <= 14)->count(),
                '2+ weeks' => $resolutionTimes->filter(fn($days) => $days > 14)->count(),
            ];

            // 7. Auditor Performance (With resolution metrics)
            $auditorData = $reports->groupBy('auditorName')
                ->map(function ($items) {
                    $resolved = $items->where('status', 'RESOLVED')->count();
                    $avgResolution = $items->filter(fn($r) => $r->status === 'RESOLVED' && $r->dateClosed)
                        ->map(fn($r) => \Carbon\Carbon::parse($r->dateClosed)->diffInDays(\Carbon\Carbon::parse($r->occurrenceDate)))
                        ->avg();

                    return [
                        'count' => $items->count(),
                        'resolved' => $resolved,
                        'resolution_rate' => $items->count() > 0 ? round(($resolved / $items->count()) * 100, 1) : 0,
                        'avg_resolution' => round($avgResolution ?? 0, 1)
                    ];
                })
                ->sortByDesc('count')
                ->take(5);

            // 8. Top High-Risk Exceptions (With details)
            $highRiskExceptions = $reports->whereIn('riskRate', ['High', 'Critical'])
                ->sortByDesc('occurrenceDate')
                ->take(5)
                ->map(function ($report) {
                    $resolutionTime = $report->status === 'RESOLVED' && $report->dateClosed
                        ? \Carbon\Carbon::parse($report->dateClosed)->diffInDays(\Carbon\Carbon::parse($report->occurrenceDate))
                        : null;

                    return [
                        'id' => $report->id,
                        'exception' => $report->exception,
                        'department' => $report->department,
                        'occurrenceDate' => \Carbon\Carbon::parse($report->occurrenceDate)->format('M d, Y'),
                        'status' => $report->status,
                        'resolutionTime' => $resolutionTime,
                        'processType' => $report->processType,
                        'riskRate' => $report->riskRate
                    ];
                });

            // 10. SLA Compliance Rate (Detailed)
            $slaCompliance = $reports->filter(function ($report) {
                return $report->status === 'RESOLVED' && $report->dateClosed && $report->proposeResolutionDate;
            })->map(function ($report) {
                $target = \Carbon\Carbon::parse($report->proposeResolutionDate);
                $actual = \Carbon\Carbon::parse($report->dateClosed);
                return [
                    'met' => $actual->lte($target) ? 1 : 0,
                    'days' => $actual->diffInDays($target, false) // Negative = early, Positive = late
                ];
            });

            $slaMetrics = [
                'compliance_rate' => $slaCompliance->count() > 0
                    ? round(($slaCompliance->sum('met') / $slaCompliance->count()) * 100, 1)
                    : 0,
                'avg_days_early' => $slaCompliance->filter(fn($x) => $x['days'] < 0)->avg('days') ?? 0,
                'avg_days_late' => $slaCompliance->filter(fn($x) => $x['days'] > 0)->avg('days') ?? 0
            ];

            // 11. New: Exception Categories
            $exceptionCategories = $reports->groupBy('category')
                ->map(function ($items) {
                    return $items->count();
                })
                ->sortDesc()
                ->take(6);

            return view('dashboard', [
                // Chart Data (Enhanced)
                'statusData' => $statusData,
                'riskData' => $riskData,
                'riskColors' => $riskColors,
                'departmentData' => $departmentData,
                'monthlyTrends' => $monthlyTrends,
                'processTypeData' => $processTypeData,
                'resolutionBuckets' => $resolutionBuckets,
                'exceptionCategories' => $exceptionCategories,

                // Metrics (Enhanced)
                'totalExceptions' => $reports->count(),
                'resolvedCount' => $reports->where('status', 'RESOLVED')->count(),
                'pendingCount' => $reports->where('status', 'PENDING')->count(),
                'overdueCount' => $reports->filter(function ($report) {
                    if ($report->status !== 'RESOLVED' && $report->proposeResolutionDate) {
                        return \Carbon\Carbon::parse($report->proposeResolutionDate)->isPast();
                    }
                    return false;
                })->count(),
                'avgResolutionDays' => $resolutionTimes->isNotEmpty() ? round($resolutionTimes->avg(), 1) : 0,
                'slaMetrics' => $slaMetrics,

                // Widget Data (Enhanced)
                'highRiskExceptions' => $highRiskExceptions,
                // 'recentActivity' => $recentActivity,
                'topAuditors' => $auditorData,

                // Raw Data for Tables
                'allReports' => $reports->sortByDesc('occurrenceDate')->take(10),

                // New: Summary stats
                'summaryStats' => [
                    'criticalCount' => $reports->where('riskRate', 'High')->count(),
                    'newThisWeek' => $reports->filter(fn($r) => \Carbon\Carbon::parse($r->occurrenceDate)->gt(now()->subWeek()))->count(),
                    'agingExceptions' => $reports->filter(fn($r) => $r->status !== 'RESOLVED' &&
                        \Carbon\Carbon::parse($r->occurrenceDate)->lt(now()->subMonth()))->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard data error: ' . $e->getMessage());
            return view('dashboard', [
                'error' => 'Failed to load dashboard data. Please try again later.',
                // Initialize all expected variables with empty values
                'statusData' => collect(),
                'riskData' => collect(),
                'departmentData' => collect(),
                'monthlyTrends' => collect(),
                'processTypeData' => collect(),
                'resolutionBuckets' => [],
                'exceptionCategories' => collect(),
                'totalExceptions' => 0,
                'resolvedCount' => 0,
                'pendingCount' => 0,
                'overdueCount' => 0,
                'avgResolutionDays' => 0,
                'slaMetrics' => ['compliance_rate' => 0, 'avg_days_early' => 0, 'avg_days_late' => 0],
                'highRiskExceptions' => collect(),
                'recentActivity' => collect(),
                'topAuditors' => collect(),
                'allReports' => collect(),
                'summaryStats' => [
                    'criticalCount' => 0,
                    'newThisWeek' => 0,
                    'agingExceptions' => 0
                ]
            ]);
        }
    }



    public function groupDashboard($employeeId)
    {
        $access_token = session('api_token');

        if (!$access_token) {
            return redirect()->back()->with('toast_error', 'Access token is missing. Please log in again.');
        }

        try {
            // Fetch all necessary data
            $reports = collect(ReportsController::getAllReports());
            $batches = collect(BatchController::getBatches());
            $groups = collect(GroupController::getActivityGroups());
            $groupMembers = collect(GroupMembersController::getGroupMembers());

            // Filter active batches and groups
            $validBatches = $batches->filter(fn($batch) => $batch->active && $batch->status === 'OPEN')
                ->keyBy('id');

            $validGroups = $groups->filter(fn($group) => $group->active)
                ->keyBy('id');

            // Get groups the employee belongs to
            $employeeGroups = $groupMembers->where('employeeId', $employeeId)
                ->pluck('activityGroupId')
                ->unique();

            if ($employeeGroups->isEmpty()) {
                return redirect()->route('dashboard')
                    ->with('toast_warning', 'You are not a member of any active groups');
            }

            // Create batch to group mapping
            $batchGroupMap = $batches->pluck('activityGroupId', 'id');

            // Filter reports - only those belonging to employee's groups
            $filteredReports = $reports->filter(function ($report) use (
                $validBatches,
                $validGroups,
                $employeeGroups,
                $batchGroupMap
            ) {
                $groupId = $batchGroupMap[$report->exceptionBatchId] ?? null;

                return $validBatches->has($report->exceptionBatchId) &&
                    $validGroups->has($groupId) &&
                    $employeeGroups->contains($groupId);
            });

            if ($filteredReports->isEmpty()) {
                return redirect()->route('dashboard')
                    ->with('toast_info', 'No exception reports found for your groups');
            }

            // Process data for dashboard
            $statusData = $filteredReports->groupBy('status')->map->count();
            $riskData = $filteredReports->groupBy('riskRate')->map->count();
            $departmentData = $filteredReports->groupBy('department')->map->count()->sortDesc();
            $processTypeData = $filteredReports->groupBy('processType')->map->count();

            $monthlyTrends = $filteredReports->groupBy(function ($report) {
                return \Carbon\Carbon::parse($report->occurrenceDate)->format('Y-m');
            })->map->count()->sortKeys();

            // SLA Calculation
            $slaCompliance = $filteredReports->filter(function ($report) {
                return $report->status === 'RESOLVED'
                    && !empty($report->dateClosed)
                    && !empty($report->proposeResolutionDate);
            })->avg(function ($report) {
                try {
                    $resolutionDate = \Carbon\Carbon::parse($report->dateClosed);
                    $proposedDate = \Carbon\Carbon::parse($report->proposeResolutionDate);
                    return $resolutionDate->lte($proposedDate) ? 100 : 0;
                } catch (\Exception $e) {
                    return 0;
                }
            });

            // High risk exceptions
            $highRiskExceptions = $filteredReports->filter(function ($report) {
                return in_array($report->riskRate, ['High', 'Critical']);
            })->sortByDesc('occurrenceDate')
                ->take(5);

            // Recent activity
            // $recentActivity = $filteredReports->sortByDesc('updatedAt')
            //     ->take(8)
            //     ->map(function ($report) {
            //         return [
            //             'type' => $report->status === 'RESOLVED' ? 'resolved' : 'new',
            //             'message' => Str::limit($report->exception, 40),
            //             'time' => \Carbon\Carbon::parse($report->updatedAt)->diffForHumans(),
            //             'department' => $report->department ?? 'N/A'
            //         ];
            //     });

            return view('my-dashboard', [
                'statusData' => $statusData,
                'riskData' => $riskData,
                'departmentData' => $departmentData,
                'processTypeData' => $processTypeData,
                'monthlyTrends' => $monthlyTrends,
                'totalExceptions' => $filteredReports->count(),
                'resolvedCount' => $filteredReports->where('status', 'RESOLVED')->count(),
                'pendingCount' => $filteredReports->where('status', 'PENDING')->count(),
                'avgResolutionDays' => $this->calculateAvgResolution($filteredReports),
                'slaComplianceRate' => round($slaCompliance, 1),
                'highRiskExceptions' => $highRiskExceptions,
                // 'recentActivity' => $recentActivity,
                'employeeGroups' => $employeeGroups,
                'toast_success' => 'Group dashboard loaded successfully'
            ]);
        } catch (\Exception $e) {
            Log::error("Group dashboard error: {$e->getMessage()}");
            return redirect()->back()->with('toast_error', 'Failed to load group dashboard data');
        }
    }

    protected function calculateAvgResolution($reports)
    {
        $resolvedReports = $reports->filter(function ($report) {
            return $report->status === 'RESOLVED' && !empty($report->dateClosed);
        });

        return $resolvedReports->isEmpty() ? 0 : round($resolvedReports->avg(function ($report) {
            try {
                return \Carbon\Carbon::parse($report->dateClosed)
                    ->diffInDays(\Carbon\Carbon::parse($report->occurrenceDate));
            } catch (\Exception $e) {
                return 0;
            }
        }), 1);
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
