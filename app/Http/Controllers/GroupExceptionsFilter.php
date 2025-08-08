<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GroupExceptionsFilter extends Controller
{


    public function filterExceptions(Request $request)
    {
        // 1. Session and Token Validation
        $access_token = session('api_token');
        if (empty($access_token)) {
            Log::warning('Session token missing in filterExceptions');
            return response()->json([
                'error' => 'Session expired',
                'redirect' => route('login')
            ], 401);
        }

        try {
            // 2. Get filter parameters

            $filters = [
                'branch' => $request->get('branch'),
                'auditor' => $request->get('auditor'),
                'status' => $request->get('status'),
                'riskRate' => $request->get('riskRate'),
                'search' => $request->get('search'),
                'dateFrom' => $request->get('dateFrom'),
                'dateTo' => $request->get('dateTo'),
                'batch' => $request->get('batch'), // Add batch filter
                'page' => $request->get('page', 1),
                'perPage' => $request->get('perPage', 15)
            ];

            Log::info('Filtering exceptions with parameters', $filters);

            // 3. API Request Setup
            $apiEndpoint = 'http://192.168.1.200:5126/Auditor/ExceptionTracker/pending-batch-exceptions';

            // 4. Make API Request with Retry Logic
            $response = Http::withToken($access_token)
                ->timeout(30)
                ->retry(3, 100, function ($exception) {
                    Log::warning('API request attempt failed', ['error' => $exception->getMessage()]);
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException;
                })
                ->get($apiEndpoint);

            // 5. Handle API Response
            if (!$response->successful()) {
                Log::error('API (pending-batch-exceptions) request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return response()->json([
                    'error' => 'Failed to fetch exceptions from server'
                ], 500);
            }

            // 6. Process Response Data
            $exceptions = $response->json();
            $loggedInUser = ExceptionController::getLoggedInUserInformation();

            if (!is_array($exceptions)) {
                Log::error('Invalid API response format', ['response' => $exceptions]);
                return response()->json([
                    'error' => 'Invalid data received from server'
                ], 500);
            }

            // 7. Process Exceptions (same logic as original method)
            if (empty($exceptions)) {
                return response()->json([
                    'data' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'total' => 0,
                        'per_page' => $filters['perPage'],
                        'last_page' => 1
                    ],
                    'filters_applied' => $filters
                ]);
            }

            // Get reference data (same as original)
            $batches = BatchController::getBatches();
            $groups = GroupController::getActivityGroups();
            $groupMembers = GroupMembersController::getGroupMembers();
            $employeeId = ExceptionController::getLoggedInUserInformation()->id;

            // Filter validation logic (same as original)
            $validBatches = collect($batches)
                ->filter(fn($batch) => $batch->active && $batch->status === 'OPEN')
                ->keyBy('id');

            $validGroups = collect($groups)
                ->filter(fn($group) => $group->active)
                ->keyBy('id');

            $employeeGroups = collect($groupMembers)
                ->where('employeeId', $employeeId)
                ->pluck('activityGroupId')
                ->unique();

            $batchGroupMap = collect($batches)
                ->pluck('activityGroupId', 'id');

            $employeeRoleId = ExceptionController::getLoggedInUserInformation()->empRoleId;
            $topManagers = [1, 2, 4];

            // 8. Filter and process exceptions
            $pendingExceptionsData = collect($exceptions)
                ->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $batchGroupMap, $topManagers, $employeeRoleId) {
                    $groupId = $batchGroupMap[$exception['exceptionBatchId']] ?? null;
                    return $validBatches->has($exception['exceptionBatchId']) &&
                        $validGroups->has($groupId) &&
                        (!in_array($exception['status'], ['DECLINED', 'PENDING'])) &&
                        ($employeeGroups->contains($groupId) || in_array($employeeRoleId, $topManagers));
                })
                ->values();

            // 9. Apply additional filters
            $filteredExceptions = $this->applyFilters($pendingExceptionsData, $filters, $batches, $groups);

            // 10. Sort by date
            $sortedExceptions = $filteredExceptions->sortByDesc('createdAt')->values();

            // 11. Manual pagination
            $total = $sortedExceptions->count();
            $perPage = (int) $filters['perPage'];
            $currentPage = (int) $filters['page'];
            $offset = ($currentPage - 1) * $perPage;

            $paginatedData = $sortedExceptions->slice($offset, $perPage)->values();

            // 12. Return JSON response
            return response()->json([
                'data' => $paginatedData->toArray(),
                'pagination' => [
                    'current_page' => $currentPage,
                    'total' => $total,
                    'per_page' => $perPage,
                    'last_page' => ceil($total / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total)
                ],
                'filters_applied' => $filters,
                'summary' => [
                    'total_exceptions' => $total,
                    'showing' => $paginatedData->count()
                ]
            ]);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP request exception in filterExceptions', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return response()->json([
                'error' => 'Connection problem with the exception service'
            ], 500);
        } catch (\Exception $e) {
            Log::critical('Unexpected error in filterExceptions', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Apply filters to the exceptions collection
     */
    private function applyFilters($exceptions, $filters, $batches, $groups)
    {
        return $exceptions->filter(function ($batchException) use ($filters, $batches, $groups) {

            // Branch filter - FIXED
            if (!empty($filters['branch'])) {
                $batchId = $batchException['exceptionBatchId'] ?? null;
                $batch = collect($batches)->firstWhere('id', $batchId);
                $group = $batch ? collect($groups)->firstWhere('id', $batch->activityGroupId) : null;
                $branchName = $group ? $group->groupName : null;

                if (!$branchName || stripos($branchName, $filters['branch']) === false) {
                    return false;
                }
            }

            // Batch filter - NEW
            if (!empty($filters['batch'])) {
                $batchId = $batchException['exceptionBatchId'] ?? null;
                $batch = collect($batches)->firstWhere('id', $batchId);
                $batchName = $batch ? $batch->batchName : null;

                if (!$batchName || stripos($batchName, $filters['batch']) === false) {
                    return false;
                }
            }

            // Auditor filter
            if (!empty($filters['auditor'])) {
                $submittedBy = $batchException['submittedBy'] ?? '';
                if (stripos($submittedBy, $filters['auditor']) === false) {
                    return false;
                }
            }

            // Status filter
            if (!empty($filters['status'])) {
                $status = strtoupper($batchException['status'] ?? '');
                if ($status !== strtoupper($filters['status'])) {
                    return false;
                }
            }

            // Risk rate filter (check within exceptions array)
            if (!empty($filters['riskRate'])) {
                $exceptions = $batchException['exceptions'] ?? [];
                $hasMatchingRisk = false;

                foreach ($exceptions as $exception) {
                    $riskRate = $exception['riskRate'] ?? '';
                    if (strcasecmp($riskRate, $filters['riskRate']) === 0) {
                        $hasMatchingRisk = true;
                        break;
                    }
                }

                if (!$hasMatchingRisk) {
                    return false;
                }
            }

            // Search filter (search across multiple fields) - FIXED
            if (!empty($filters['search'])) {
                $searchTerm = strtolower($filters['search']);

                // Search in main fields
                $searchableFields = [
                    $batchException['submittedBy'] ?? '',
                    $batchException['departmentName'] ?? '',
                    $batchException['status'] ?? ''
                ];

                // Search in exceptions
                $exceptions = $batchException['exceptions'] ?? [];
                foreach ($exceptions as $exception) {
                    $searchableFields[] = $exception['exceptionTitle'] ?? '';
                    $searchableFields[] = $exception['exception'] ?? '';
                    $searchableFields[] = $exception['riskRate'] ?? '';
                }

                // Search in branch name
                $batchId = $batchException['exceptionBatchId'] ?? null;
                $batch = collect($batches)->firstWhere('id', $batchId);
                $group = $batch ? collect($groups)->firstWhere('id', $batch->activityGroupId) : null;
                if ($group && isset($group->groupName)) {
                    $searchableFields[] = $group->groupName;
                }

                // Search in batch name
                if ($batch && isset($batch->batchName)) {
                    $searchableFields[] = $batch->batchName;
                }

                $foundMatch = false;
                foreach ($searchableFields as $field) {
                    if (is_string($field) && stripos($field, $searchTerm) !== false) {
                        $foundMatch = true;
                        break;
                    }
                }

                if (!$foundMatch) {
                    return false;
                }
            }

            // Date range filters
            $submittedAt = $batchException['submittedAt'] ?? null;
            if ($submittedAt) {
                try {
                    $submittedDate = \Carbon\Carbon::parse($submittedAt);

                    // From date filter
                    if (!empty($filters['dateFrom'])) {
                        $fromDate = \Carbon\Carbon::parse($filters['dateFrom'])->startOfDay();
                        if ($submittedDate->lt($fromDate)) {
                            return false;
                        }
                    }

                    // To date filter
                    if (!empty($filters['dateTo'])) {
                        $toDate = \Carbon\Carbon::parse($filters['dateTo'])->endOfDay();
                        if ($submittedDate->gt($toDate)) {
                            return false;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Date parsing error in filter', [
                        'submittedAt' => $submittedAt,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return true;
        });
    }

    // Fixed getFilterOptions method - Replace your existing getFilterOptions method with this:
    public function getFilterOptions(Request $request)
    {
        // Session validation
        $access_token = session('api_token');
        if (empty($access_token)) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        try {
            // Get all exceptions (same logic as main method)
            $apiEndpoint = 'http://192.168.1.200:5126/Auditor/ExceptionTracker/pending-batch-exceptions';
            $response = Http::withToken($access_token)->timeout(30)->get($apiEndpoint);

            if (!$response->successful()) {
                Log::error('Failed to fetch filter options', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return response()->json(['error' => 'Failed to fetch data'], 500);
            }

            $exceptions = $response->json();
            if (!is_array($exceptions) || empty($exceptions)) {
                return response()->json([
                    'branches' => [],
                    'auditors' => [],
                    'batches' => []
                ]);
            }

            // Get reference data
            $batches = BatchController::getBatches();
            $groups = GroupController::getActivityGroups();

            // Extract unique values
            $branches = [];
            $auditors = [];
            $batchNames = [];

            foreach ($exceptions as $exception) {
                // Add auditor
                if (!empty($exception['submittedBy'])) {
                    $auditors[] = $exception['submittedBy'];
                }

                // Add branch
                $batchId = $exception['exceptionBatchId'] ?? null;
                $batch = collect($batches)->firstWhere('id', $batchId);
                $group = $batch ? collect($groups)->firstWhere('id', $batch->activityGroupId) : null;
                if ($group && !empty($group->groupName)) {
                    $branches[] = $group->groupName;
                }

                // Add batch name
                if ($batch && !empty($batch->batchName)) {
                    $batchNames[] = $batch->batchName;
                }
            }

            return response()->json([
                'branches' => array_values(array_unique($branches)),
                'auditors' => array_values(array_unique($auditors)),
                'batches' => array_values(array_unique($batchNames)), // Add batches
                'statuses' => ['APPROVED', 'AMENDMENT', 'ANALYSIS', 'RESOLVED'],
                'riskRates' => ['High', 'Medium', 'Low']
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getFilterOptions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}
