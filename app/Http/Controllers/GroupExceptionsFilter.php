<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GroupExceptionsFilter extends Controller
{

    public function openBatch(Request $request, $exceptionId, $exceptionStatus)
    {

        // dd($request->all());

        $accessToken = session('api_token');
        if (empty($accessToken)) {
            Log::warning('Session token missing in openBatch');
            return redirect()->route('login')
                ->with('toast_warning', 'Session expired, please login to continue');
        }

        $departments = ExceptionManipulationController::departmentData() ?? [];
        $batches = BatchController::getBatches() ?? [];
        $processTypes = ProcessTypeController::getProcessTypes() ?? [];
        $subProcessTypes = collect(ProcessTypeController::getSubProcessTypes() ?? []);
        $groupedSubProcessTypes = $subProcessTypes
            ->filter(function ($item) {
                return isset($item->processTypeId);
            })
            ->groupBy('processTypeId')
            ->toArray();

        $request = HTTP::withToken($accessToken)->get('http://192.168.1.200:5126/Auditor/ExceptionTracker/' . $exceptionId);
        $exceptions = $request->object();

        if (!$request->successful()) {
            Log::error('Failed to fetch batch exception', [
                'status' => $request->status(),
                'response' => $request->body()
            ]);
            return redirect()->back()
                ->with('toast_error', 'Failed to fetch batch exception. Please try again later.');
        }

        return view('exception-setup.group-exception-enquiry-view', [
            'exception' => $exceptions,
            'batchStatus' => $exceptionStatus,
            'departments' => $departments,
            'batches' => $batches,
            'processTypes' => $processTypes,
            'groupedSubProcessTypes' => $groupedSubProcessTypes,

        ]);
    }

    public function openException(Request $request, $exceptionId)
    {
        $accessToken = session('api_token');
        if (empty($accessToken)) {
            Log::warning('Session token missing in openException');
            return redirect()->route('login')
                ->with('toast_warning', 'Session expired, please login to continue');
        }

        return view('exception-setup.group-exception-status-view', [
            'pendingException' => $exceptionId,
            'batchStatus' => 'OPEN',
            'departments' => ExceptionManipulationController::departmentData() ?? [],
            'batches' => BatchController::getBatches() ?? [],
            'processTypes' => ProcessTypeController::getProcessTypes() ?? [],
            'groupedSubProcessTypes' => collect(ProcessTypeController::getSubProcessTypes() ?? [])
                ->filter(fn($item) => isset($item->processTypeId))
                ->groupBy('processTypeId')
                ->toArray(),
        ]);
    }


    public function groupExceptionStatus(Request $request)
    {

        // 1. Session and Token Validation
        $access_token = session('api_token');
        if (empty($access_token)) {
            Log::warning('Session token missing in exceptionSupList');
            return redirect()->route('login')
                ->with('toast_warning', 'Session expired, please login to continue');
        }

        try {
            // 2. API Request Setup
            $apiEndpoint = 'http://192.168.1.200:5126/Auditor/ExceptionTracker';
            Log::info('Fetching pending exceptions from API', ['endpoint' => $apiEndpoint]);

            // 3. Make API Request with Retry Logic
            $response = Http::withToken($access_token)
                ->timeout(30)
                ->retry(3, 100, function ($exception) {
                    Log::warning('API request attempt failed', ['error' => $exception->getMessage()]);
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException;
                })
                ->get($apiEndpoint);

            // 4. Handle API Response
            if (!$response->successful()) {
                Log::error('API (pending-batch-exceptions) request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return redirect()->back()
                    ->with('toast_error', 'Failed to fetch pending exceptions. Please try again later.');
            }

            // 5. Process Response Data
            $exceptions = $response->object();



            $batches = BatchController::getBatches();
            $groups = GroupController::getActivityGroups();
            $groupMembers = GroupMembersController::getGroupMembers();
            $employeeId = ExceptionManipulationController::getLoggedInUserInformation()->id;


            // Filter active batches with status 'OPEN' and map them by ID
            $validBatches = collect($batches)
                ->filter(fn($batch) => $batch->active && $batch->status === 'OPEN')
                ->keyBy('id');

            // Filter active groups and map them by ID
            $validGroups = collect($groups)
                ->filter(fn($group) => $group->active)
                ->keyBy('id');

            // Get groups where the specified employee belongs
            $employeeGroups = collect($groupMembers)
                ->where('employeeId', $employeeId)
                ->pluck('activityGroupId')
                ->unique();

            // dd($employeeGroups);

            // Map batch IDs to their corresponding activity group IDs
            $batchGroupMap = collect($batches)
                ->pluck('activityGroupId', 'id');

            $employeeRoleId = ExceptionManipulationController::getLoggedInUserInformation()->empRoleId;

            // top managers
            // 1 - Managing Director
            // 2 - Head of Internal Audit
            // 4 - Head of Internal Control & Compliance
            $topManagers = [1, 2, 4];


            $exceptionsData = collect($exceptions)
                // Filter top-level exceptions by status
                ->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $batchGroupMap, $topManagers, $employeeRoleId) {
                    $groupId = $batchGroupMap[$exception->exceptionBatchId] ?? null;
                    return $validBatches->has($exception->exceptionBatchId) &&
                        $validGroups->has($groupId) && (!in_array($exception->status, ['DECLINED'])) && $employeeGroups->contains($groupId) || (in_array($employeeRoleId, $topManagers));
                })
                ->values()
                ->all();


            $reports = $exceptionsData;

            // 7. Return View with Processed Data
            return view('exception-setup.group-exception-enquiry-list', [
                'reports' => $reports,
                'batches' => $batches,
                'groups' => $groups,
                'isEmpty' => empty($reports) ||
                    (count($reports) === 1 && $reports[0]->id === '---')
            ]);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP request exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return redirect()->back()
                ->with('toast_error', 'Connection problem with the exception service. Please try again later.');
        } catch (\Exception $e) {
            Log::critical('Unexpected error in exceptionSupList', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('toast_error', 'An unexpected error occurred. Our team has been notified.');
        }
    }


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
            // 2. Get filter parameters - REMOVED auditor and riskRate
            $filters = [
                'branch' => $request->get('branch'),
                'status' => $request->get('status'),
                'search' => $request->get('search'),
                'dateFrom' => $request->get('dateFrom'),
                'dateTo' => $request->get('dateTo'),
                'batch' => $request->get('batch'),
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
            $loggedInUser = ExceptionManipulationController::getLoggedInUserInformation();

            if (!is_array($exceptions)) {
                Log::error('Invalid API response format', ['response' => $exceptions]);
                return response()->json([
                    'error' => 'Invalid data received from server'
                ], 500);
            }

            // 7. Process Exceptions
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

            // Get reference data
            $batches = BatchController::getBatches();
            $groups = GroupController::getActivityGroups();
            $groupMembers = GroupMembersController::getGroupMembers();
            $employeeId = ExceptionManipulationController::getLoggedInUserInformation()->id;

            // Filter validation logic
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

            $employeeRoleId = ExceptionManipulationController::getLoggedInUserInformation()->empRoleId;
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
     * Enhanced applyFilters method - REMOVED auditor and riskRate filters, FIXED batch and branch filters
     */
    private function applyFilters($exceptions, $filters, $batches, $groups)
    {
        return $exceptions->filter(function ($batchException) use ($filters, $batches, $groups) {

            // FIXED Branch filter - more robust matching
            if (!empty($filters['branch'])) {
                $branchMatched = false;

                // Check from exceptionBatch object first (most reliable)
                if (isset($batchException['exceptionBatch']['activityGroupName'])) {
                    $branchName = $batchException['exceptionBatch']['activityGroupName'];
                    if (strcasecmp($branchName, $filters['branch']) === 0) {
                        $branchMatched = true;
                    }
                }

                // Fallback: check via batch and group lookup
                if (!$branchMatched) {
                    $batchId = $batchException['exceptionBatchId'] ?? null;
                    if ($batchId) {
                        $batch = collect($batches)->firstWhere('id', $batchId);
                        if ($batch && $batch->activityGroupId) {
                            $group = collect($groups)->firstWhere('id', $batch->activityGroupId);
                            if ($group && strcasecmp($group->groupName, $filters['branch']) === 0) {
                                $branchMatched = true;
                            }
                        }
                    }
                }

                if (!$branchMatched) {
                    return false;
                }
            }

            // FIXED Batch filter - more robust matching with both name and code
            if (!empty($filters['batch'])) {
                $batchMatched = false;

                // Check from exceptionBatch object (most reliable)
                if (isset($batchException['exceptionBatch']['name'])) {
                    $batchName = $batchException['exceptionBatch']['name'];
                    if (strcasecmp($batchName, $filters['batch']) === 0) {
                        $batchMatched = true;
                    }
                }

                // Also check batch code
                if (!$batchMatched && isset($batchException['exceptionBatch']['code'])) {
                    $batchCode = $batchException['exceptionBatch']['code'];
                    if (strcasecmp($batchCode, $filters['batch']) === 0) {
                        $batchMatched = true;
                    }
                }

                // Fallback: check via batch lookup
                if (!$batchMatched) {
                    $batchId = $batchException['exceptionBatchId'] ?? null;
                    if ($batchId) {
                        $batch = collect($batches)->firstWhere('id', $batchId);
                        if ($batch) {
                            if (strcasecmp($batch->batchName, $filters['batch']) === 0) {
                                $batchMatched = true;
                            }
                        }
                    }
                }

                if (!$batchMatched) {
                    return false;
                }
            }

            // Status filter (unchanged)
            if (!empty($filters['status'])) {
                $status = strtoupper($batchException['status'] ?? '');
                if ($status !== strtoupper($filters['status'])) {
                    return false;
                }
            }

            // ENHANCED GLOBAL SEARCH - Updated without auditor and risk rate fields
            if (!empty($filters['search'])) {
                $searchTerm = strtolower($filters['search']);
                $searchableFields = [];

                // 1. Batch Code (from exceptionBatch)
                if (isset($batchException['exceptionBatch']['code'])) {
                    $searchableFields[] = $batchException['exceptionBatch']['code'];
                }

                // 2. Batch Name (from exceptionBatch)
                if (isset($batchException['exceptionBatch']['name'])) {
                    $searchableFields[] = $batchException['exceptionBatch']['name'];
                }

                // 3. Branch Name (activityGroupName)
                if (isset($batchException['exceptionBatch']['activityGroupName'])) {
                    $searchableFields[] = $batchException['exceptionBatch']['activityGroupName'];
                }

                // 4. Status
                if (isset($batchException['status'])) {
                    $searchableFields[] = $batchException['status'];
                }

                // 5. Department
                if (isset($batchException['departmentName'])) {
                    $searchableFields[] = $batchException['departmentName'];
                }

                // 6. Process Type Name
                if (isset($batchException['processTypeName'])) {
                    $searchableFields[] = $batchException['processTypeName'];
                }

                // 7. Exception titles and descriptions (from individual exceptions)
                $exceptions = $batchException['exceptions'] ?? [];
                foreach ($exceptions as $exception) {
                    if (isset($exception['exceptionTitle'])) {
                        $searchableFields[] = $exception['exceptionTitle'];
                    }
                    if (isset($exception['exception'])) {
                        $searchableFields[] = $exception['exception'];
                    }
                }

                // 8. Reference Number
                if (isset($batchException['refNum'])) {
                    $searchableFields[] = $batchException['refNum'];
                }

                // Remove duplicates and empty values
                $searchableFields = array_unique(array_filter($searchableFields, function ($field) {
                    return is_string($field) && !empty(trim($field));
                }));

                // Perform the search
                $foundMatch = false;
                foreach ($searchableFields as $field) {
                    if (stripos($field, $searchTerm) !== false) {
                        $foundMatch = true;
                        break;
                    }
                }

                if (!$foundMatch) {
                    return false;
                }
            }

            // Date range filters (unchanged)
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

    /**
     * Enhanced getFilterOptions method - REMOVED auditor, batch codes, and risk rates
     */
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
                    'batches' => []
                ]);
            }

            // Get reference data
            $batches = BatchController::getBatches();
            $groups = GroupController::getActivityGroups();

            // Apply same filtering logic as main method for user permissions
            $loggedInUser = ExceptionManipulationController::getLoggedInUserInformation();
            $groupMembers = GroupMembersController::getGroupMembers();
            $employeeId = $loggedInUser->id;
            $employeeRoleId = $loggedInUser->empRoleId;
            $topManagers = [1, 2, 4];

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

            // Filter exceptions based on user permissions
            $filteredExceptions = collect($exceptions)
                ->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $batchGroupMap, $topManagers, $employeeRoleId) {
                    $groupId = $batchGroupMap[$exception['exceptionBatchId']] ?? null;
                    return $validBatches->has($exception['exceptionBatchId']) &&
                        $validGroups->has($groupId) &&
                        (!in_array($exception['status'], ['DECLINED', 'PENDING'])) &&
                        ($employeeGroups->contains($groupId) || in_array($employeeRoleId, $topManagers));
                });

            // Extract unique values
            $branches = [];
            $batchNames = [];

            foreach ($filteredExceptions as $exception) {
                // Add branch from exceptionBatch (most reliable source)
                if (isset($exception['exceptionBatch']['activityGroupName']) && !empty($exception['exceptionBatch']['activityGroupName'])) {
                    $branches[] = $exception['exceptionBatch']['activityGroupName'];
                } else {
                    // Fallback: Add branch from group lookup
                    $batchId = $exception['exceptionBatchId'] ?? null;
                    $batch = collect($batches)->firstWhere('id', $batchId);
                    $group = $batch ? collect($groups)->firstWhere('id', $batch->activityGroupId) : null;

                    if ($group && !empty($group->groupName)) {
                        $branches[] = $group->groupName;
                    }
                }

                // Add batch name from exceptionBatch (most reliable source)
                if (isset($exception['exceptionBatch']['name']) && !empty($exception['exceptionBatch']['name'])) {
                    $batchNames[] = $exception['exceptionBatch']['name'];
                } else {
                    // Fallback: Add batch name from batch lookup
                    $batchId = $exception['exceptionBatchId'] ?? null;
                    $batch = collect($batches)->firstWhere('id', $batchId);
                    if ($batch && !empty($batch->batchName)) {
                        $batchNames[] = $batch->batchName;
                    }
                }
            }

            // Sort and return unique values
            return response()->json([
                'branches' => array_values(array_unique(array_filter($branches))),
                'batches' => array_values(array_unique(array_filter($batchNames))),
                'statuses' => ['APPROVED', 'AMENDMENT', 'ANALYSIS', 'RESOLVED']
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
