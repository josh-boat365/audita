<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AuditeeDataManipulationController extends Controller
{
    public function auditeeExceptionList()
    {
        // Validate session token
        $access_token = session('api_token');
        if (empty($access_token)) {
            Log::warning('Session token missing in auditeeExceptionList');
            return redirect()->route('login')
                ->with('toast_warning', 'Session expired, please login to continue');
        }

        try {
            // Fetch data from API
            $response = Http::withToken($access_token)
                ->timeout(30)
                ->retry(3, 100)
                ->get('http://192.168.1.200:5126/Auditor/ExceptionTracker/pending-batch-exceptions');

            // Handle API failure
            if (!$response->successful()) {
                Log::error('API request failed', ['status' => $response->status()]);
                return view('exception-setup.auditee-exception-list', [
                    'pendingExceptions' => [],
                    'isEmpty' => true
                ]);
            }

            $exceptions = $response->json();
            if (!is_array($exceptions)) {
                return view('exception-setup.auditee-exception-list', [
                    'pendingExceptions' => [],
                    'isEmpty' => true
                ]);
            }

            // Get user and lookup data
            $loggedInUser = ExceptionManipulationController::getLoggedInUserInformation();
            $employeeId = $loggedInUser->id;
            $employeeRoleId = $loggedInUser->empRoleId;
            $topManagers = [1, 2, 4]; // MD, Head of IA, Head of IC&C

            // Get valid batches and groups
            $validBatches = collect(BatchController::getBatches())
                ->filter(fn($batch) => $batch->active && $batch->status === 'OPEN')
                ->keyBy('id');

            $validGroups = collect(GroupController::getActivityGroups())
                ->filter(fn($group) => $group->active)
                ->keyBy('id');

            // Get user's groups
            $employeeGroups = collect(GroupMembersController::getGroupMembers())
                ->where('employeeId', $employeeId)
                ->pluck('activityGroupId')
                ->unique();

            // Get batch-group mapping
            $batchGroupMap = collect(BatchController::getBatches())
                ->pluck('activityGroupId', 'id');

            // Process exceptions
            $pendingExceptions = collect($exceptions)
                ->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $batchGroupMap, $topManagers, $employeeRoleId) {
                    $batchId = $exception['exceptionBatchId'] ?? null;
                    $groupId = $batchGroupMap[$batchId] ?? null;

                    return $validBatches->has($batchId) &&
                        $validGroups->has($groupId) &&
                        $exception['status'] === 'APPROVED' &&
                        (in_array($employeeRoleId, $topManagers) || $employeeGroups->contains($groupId));
                })
                ->map(function ($exception) use ($loggedInUser) {
                    $nestedExceptions = collect($exception['exceptions'] ?? []);
                    $approvedCount = $nestedExceptions->where('status', 'APPROVED')->count();
                    $respondedCount = $nestedExceptions->where('recommendedStatus', 'RESOLVED')->count();

                    return [
                        'id' => $exception['id'],
                        'status' => $exception['status'],
                        'submittedBy' => $exception['submittedBy'] ?? 'Unknown',
                        'submittedAt' => $exception['submittedAt'] ?? now()->format('Y-m-d H:i:s'),
                        'groupName' => $exception['exceptionBatch']['activityGroupName'] ?? 'N/A',
                        'department' => $exception['departmentName'] ?? 'Unknown Department',
                        'exceptionCount' => $approvedCount,
                        'countForRespondedExceptionsByAuditee' => $respondedCount,
                        'auditorDepartmentId' => $loggedInUser->departmentId,
                    ];
                })
                ->filter(fn($exception) => $exception['exceptionCount'] > 0)
                ->values()
                ->all();

            // Always return view - let Blade handle empty state
            return view('exception-setup.auditee-exception-list', [
                'pendingExceptions' => $pendingExceptions,
                'isEmpty' => empty($pendingExceptions)
            ]);

        } catch (\Exception $e) {
            Log::critical('Error in auditeeExceptionList', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return empty view instead of redirect
            return view('exception-setup.auditee-exception-list', [
                'pendingExceptions' => [],
                'isEmpty' => true
            ]);
        }
    }

    public function auditeePendingExceptionList()
    {
        // 1. Session and Token Validation
        $access_token = session('api_token');
        if (empty($access_token)) {
            Log::warning('Session token missing in auditeePendingExceptionList');
            return redirect()->route('login')
                ->with('toast_warning', 'Session expired, please login to continue');
        }

        try {
            // 2. Fetch data from API
            $response = Http::withToken($access_token)
                ->timeout(30)
                ->retry(3, 100, function ($exception) {
                    Log::warning('API request attempt failed', ['error' => $exception->getMessage()]);
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException;
                })
                ->get('http://192.168.1.200:5126/Auditor/ExceptionTracker/pending-batch-exceptions');

            // 3. Handle API failure - return empty view
            if (!$response->successful()) {
                Log::error('API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return view('exception-setup.auditee-exception-pending-list', [
                    'pendingExceptions' => [],
                    'isEmpty' => true
                ]);
            }

            // 4. Process API response
            $exceptions = $response->json();
            if (!is_array($exceptions)) {
                Log::error('Invalid API response format', ['response' => $exceptions]);
                return view('exception-setup.auditee-exception-pending-list', [
                    'pendingExceptions' => [],
                    'isEmpty' => true
                ]);
            }

            // 5. Handle empty exceptions - return empty view
            if (empty($exceptions)) {
                Log::info('No pending exceptions found');
                return view('exception-setup.auditee-exception-pending-list', [
                    'pendingExceptions' => [],
                    'isEmpty' => true
                ]);
            }

            // 6. Get user and lookup data
            $loggedInUser = ExceptionManipulationController::getLoggedInUserInformation();
            $employeeId = $loggedInUser->id;
            $employeeRoleId = $loggedInUser->empRoleId;
            $topManagers = [1, 2, 4]; // MD, Head of IA, Head of IC&C

            // 7. Get valid batches and groups
            $validBatches = collect(BatchController::getBatches())
                ->filter(fn($batch) => $batch->active && $batch->status === 'OPEN')
                ->keyBy('id');

            $validGroups = collect(GroupController::getActivityGroups())
                ->filter(fn($group) => $group->active)
                ->keyBy('id');

            $employeeGroups = collect(GroupMembersController::getGroupMembers())
                ->where('employeeId', $employeeId)
                ->pluck('activityGroupId')
                ->unique();

            $batchGroupMap = collect(BatchController::getBatches())
                ->pluck('activityGroupId', 'id');

            // 8. Process and filter exceptions
            $pendingExceptions = collect($exceptions)
                ->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $batchGroupMap, $topManagers, $employeeRoleId) {
                    $batchId = $exception['exceptionBatchId'] ?? null;
                    $groupId = $batchGroupMap[$batchId] ?? null;

                    return $validBatches->has($batchId) &&
                        $validGroups->has($groupId) &&
                        $exception['status'] === 'ANALYSIS' &&
                        (in_array($employeeRoleId, $topManagers) || $employeeGroups->contains($groupId));
                })
                ->map(function ($exception) use ($loggedInUser) {
                    $nestedExceptions = collect($exception['exceptions'] ?? []);
                    $resolvedCount = $nestedExceptions->where('recommendedStatus', 'RESOLVED')->count();
                    $notResolvedCount = $nestedExceptions->where('status', 'NOT-RESOLVED')->count();

                    return [
                        'id' => $exception['id'] ?? null,
                        'status' => $exception['status'] ?? 'UNKNOWN',
                        'submittedBy' => $exception['submittedBy'] ?? 'Unknown',
                        'submittedAt' => $exception['submittedAt'] ?? now()->format('Y-m-d H:i:s'),
                        'groupName' => $exception['exceptionBatch']['activityGroupName'] ?? 'N/A',
                        'department' => $exception['departmentName'] ?? 'Unknown Department',
                        'exceptionCount' => $resolvedCount,
                        'countForRespondedExceptionsByAuditee' => $notResolvedCount,
                        'auditorDepartmentId' => $loggedInUser->departmentId,
                    ];
                })
                ->filter(fn($exception) => ($exception['countForRespondedExceptionsByAuditee'] ?? 0) > 0)
                ->values()
                ->all();

            // 9. Always return view - let Blade handle empty state
            return view('exception-setup.auditee-exception-pending-list', [
                'pendingExceptions' => $pendingExceptions,
                'isEmpty' => empty($pendingExceptions)
            ]);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP request exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            // Return empty view instead of redirect
            return view('exception-setup.auditee-exception-pending-list', [
                'pendingExceptions' => [],
                'isEmpty' => true
            ]);
        } catch (\Exception $e) {
            Log::critical('Unexpected error in auditeePendingExceptionList', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return empty view instead of redirect
            return view('exception-setup.auditee-exception-pending-list', [
                'pendingExceptions' => [],
                'isEmpty' => true
            ]);
        }
    }

    public function showExceptionListWithStatusForApproval($batchId, $status)
    {
        try {
            // Validate session
            $sessionValidation = $this->validateSession();
            if ($sessionValidation) {
                return $sessionValidation;
            }

            // Validate inputs
            if (empty($batchId) || empty($status)) {
                return redirect()->back()->with('toast_error', 'Invalid batch ID or status');
            }

            // Fetch exception data
            $responseObject = $this->fetchExceptionData($batchId);
            if (!is_object($responseObject) && !is_array($responseObject)) {
                return $responseObject; // This will be the redirect response if fetch failed
            }

            // Process exceptions based on status
            $processedData = $this->processExceptionsByStatus($responseObject, $status);
            if (!is_array($processedData) || array_key_exists('exception', $processedData) === false) {
                return redirect()->back()->with('toast_error', 'Failed to process exceptions');
            }

            // Prepare view data
            $viewData = $this->prepareViewData($processedData, $status);
            if (!is_array($viewData)) {
                return $viewData; // This will be the redirect response if session expired
            }

            // Return appropriate view
            return $this->renderView($status, $viewData);
        } catch (\Exception $e) {
            Log::error('Error in showExceptionListWithStatusForApproval: ' . $e->getMessage());
            return redirect()->back()->with('toast_error', 'An unexpected error occurred');
        }
    }


    public function showAuditorExceptionListForApproval($batchId, $status)
    {
        // Validate session
        $sessionValidation = $this->validateSession();
        if ($sessionValidation) {
            return $sessionValidation;
        }

        // Fetch exception data
        $responseObject = $this->fetchExceptionData($batchId);

        // Filter and clean data in one go
        $exception = collect($responseObject ?? [])
            ->filter(function ($item) use ($status) {
                return isset($item->status) &&
                    $item->status === $status &&
                    isset($item->exceptions) &&
                    !empty($item->exceptions);
            })
            ->map(function ($item) {
                // Filter nested exceptions and provide defaults
                $item->exceptions = collect($item->exceptions)
                    ->filter(function ($ex) {
                        return isset($ex->status) &&
                            ($ex->status === 'DECLINED' || $ex->status === 'PENDING') &&
                            ($ex->recommendedStatus === null || !isset($ex->recommendedStatus));
                    })
                    ->map(function ($ex) {
                        // Set defaults for null values
                        $ex->exceptionTitle = $ex->exceptionTitle ?? 'No Title';
                        $ex->exception = $ex->exception ?? 'No Description';
                        $ex->statusComment = $ex->statusComment ?? '';
                        $ex->auditorName = $ex->auditorName ?? 'Unknown';
                        $ex->processType = $ex->processType ?? 'Unknown';
                        $ex->subProcessType = $ex->subProcessType ?? 'Unknown';
                        $ex->department = $ex->department ?? 'Unknown';
                        $ex->exceptionBatch = $ex->exceptionBatch ?? 'Unknown';
                        $ex->exceptionBatchCode = $ex->exceptionBatchCode ?? 'N/A';
                        $ex->comment = $ex->comment ?? [];
                        $ex->fileAttached = $ex->fileAttached ?? [];
                        return $ex;
                    })
                    ->values()
                    ->toArray();

                // Set defaults for main item
                $item->refNum = $item->refNum ?? 'N/A';
                $item->processTypeName = $item->processTypeName ?? 'Unknown';
                $item->departmentName = $item->departmentName ?? 'Unknown';
                $item->statusComment = $item->statusComment ?? '';

                return $item;
            })
            ->filter(function ($item) {
                return !empty($item->exceptions);
            })
            ->values()
            ->toArray();

        // Get reference data with fallbacks
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

        // Return view
        return view(
            'exception-setup.auditor-exception-list-for-approval',
            compact(
                'exception',
                'departments',
                'batches',
                'processTypes',
                'subProcessTypes',
                'groupedSubProcessTypes'
            )
        );
    }


    private function validateSession()
    {
        if (empty(session('api_token'))) {
            session()->flush();
            return redirect()->route('login')->with('toast_warning', 'Session expired, login to access the application');
        }
        return null;
    }

    private function fetchExceptionData($batchId)
    {
        try {
            $access_token = session('api_token');

            if (empty($access_token)) {
                session()->flush();
                return redirect()->route('login')->with('toast_warning', 'Session expired, login to access the application');
            }

            $response = Http::withToken($access_token)
                ->get('http://192.168.1.200:5126/Auditor/ExceptionTracker/get-batch-exception/' . $batchId);

            if (!$response->successful()) {
                Log::error('Failed to fetch exception details', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Failed to fetch exception details');
            }

            $responseObject = $response->object();
            return $responseObject ?? (object)[]; // Ensure we always return an object
        } catch (\Exception $e) {
            Log::error('Error in fetchExceptionData: ' . $e->getMessage());
            return redirect()->back()->with('toast_error', 'Error fetching exception data');
        }
    }

    private function processExceptionsByStatus($responseObject, $status)
    {
        try {
            $access_token = session('api_token');

            if (empty($access_token)) {
                session()->flush();
                return redirect()->route('login')->with('toast_warning', 'Session expired, login to access the application');
            }

            // Ensure responseObject is iterable
            $responseCollection = collect($responseObject ?? []);

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

            // Process main exceptions
            $exception = $responseCollection
                ->filter(function ($item) use ($status) {

                    return is_object($item) && property_exists($item, 'status') && $item->status == $status;
                })
                ->map(function ($item) use ($status) {
                    if (!is_object($item) || !property_exists($item, 'exceptions')) {
                        return $item;
                    }


                    // Handle different filtering logic based on parent status
                    if ($status == 'ANALYSIS') {
                        // For ANALYSIS status, filter sub-exceptions with status APPROVED and recommendedStatus RESOLVED
                        $item->exceptions = collect($item->exceptions ?? [])
                            ->filter(function ($subException) {
                                return is_object($subException)
                                    && property_exists($subException, 'status')
                                    && property_exists($subException, 'recommendedStatus')
                                    && $subException->status == 'APPROVED'
                                    && $subException->recommendedStatus == 'RESOLVED'
                                    || (
                                        is_object($subException)
                                        && property_exists($subException, 'status')
                                        && $subException->status == 'NOT-RESOLVED'
                                        || $subException->status == 'RESOLVED'
                                    );
                            })
                            ->values()
                            ->toArray();
                    } else {

                        // Original logic for other statuses
                        $item->exceptions = collect($item->exceptions ?? [])
                            ->where('status', $status)
                            // ->whereNotIn('recommendedStatus', ['RESOLVED'])
                            ->values()
                            ->toArray();
                    }

                    return $item;
                })
                ->filter(function ($item) {
                    return is_object($item) && property_exists($item, 'exceptions') && !empty($item->exceptions);
                })
                ->values()
                ->toArray();

            // Initialize data containers
            $exceptionFiles = [];
            $exceptionComments = [];

            // Process sub-exceptions for APPROVED status
            if (($status == 'APPROVED' || $status == 'ANALYSIS') && $responseCollection->isNotEmpty()) {
                $firstItem = $responseCollection->first();
                $subExceptions = property_exists($firstItem, 'exceptions') ? ($firstItem->exceptions ?? []) : [];

                foreach ($subExceptions as $subException) {
                    if (is_object($subException) && property_exists($subException, 'id') && !empty($subException->id)) {
                        $preparedException = ExceptionManipulationController::getAnException($subException->id);

                        if (is_object($preparedException)) {
                            // Collect comments
                            if (property_exists($preparedException, 'comment') && !empty($preparedException->comment)) {
                                $exceptionComments = array_merge(
                                    $exceptionComments,
                                    is_array($preparedException->comment) ? $preparedException->comment : [$preparedException->comment]
                                );
                            }

                            // Collect files
                            if (property_exists($preparedException, 'fileAttached') && !empty($preparedException->fileAttached)) {
                                $exceptionFiles = array_merge(
                                    $exceptionFiles,
                                    is_array($preparedException->fileAttached) ? $preparedException->fileAttached : [$preparedException->fileAttached]
                                );
                            }
                        }
                    }
                }
            }

            return [
                'exception' => $exception[0] ?? [],
                'exceptionFiles' => $exceptionFiles,
                'exceptionComments' => $exceptionComments
            ];
        } catch (\Exception $e) {
            Log::error('Error in processExceptionsByStatus: ' . $e->getMessage());
            return [
                'exception' => [],
                'exceptionFiles' => [],
                'exceptionComments' => []
            ];
        }
    }

    private function prepareViewData($processedData, $status)
    {
        try {
            $access_token = session('api_token');

            if (empty($access_token)) {
                session()->flush();
                return redirect()->route('login')->with('toast_warning', 'Session expired, login to access the application');
            }

            $commonData = [
                'exception' => $processedData['exception'] ?? [],
                'departments' => ExceptionManipulationController::departmentData() ?? [],
                'batches' => BatchController::getBatches() ?? [],
                'processTypes' => ProcessTypeController::getProcessTypes() ?? [],
                'riskRates' => RiskRateController::getRiskRates() ?? [],
                'subProcessTypes' => collect(ProcessTypeController::getSubProcessTypes() ?? []),
            ];

            $commonData['groupedSubProcessTypes'] = collect($commonData['subProcessTypes'] ?? [])
                ->groupBy('processTypeId')
                ->toArray();

            // Add files and comments for APPROVED status
            if ($status == 'APPROVED' || $status == 'ANALYSIS') {
                $commonData['exceptionFiles'] = $processedData['exceptionFiles'] ?? [];
                $commonData['exceptionComments'] = $processedData['exceptionComments'] ?? [];
            }

            return $commonData;
        } catch (\Exception $e) {
            Log::error('Error in prepareViewData: ' . $e->getMessage());
            return redirect()->back()->with('toast_error', 'Error preparing view data');
        }
    }

    private function renderView($status, $viewData)
    {
        try {
            $access_token = session('api_token');

            if (empty($access_token)) {
                session()->flush();
                return redirect()->route('login')->with('toast_warning', 'Session expired, login to access the application');
            }

            if (!is_array($viewData)) {
                return redirect()->back()->with('toast_error', 'Invalid view data');
            }

            $viewName = ($status == 'PENDING')
                ? 'exception-setup.supervisor-exception-list-for-approval'
                : (($status == 'ANALYSIS')
                    // ? 'exception-setup.auditor-analysis-view'
                    ? 'exception-setup.auditee-exception-view'
                    : 'exception-setup.auditee-exception-view');

            return view($viewName, $viewData);
        } catch (\Exception $e) {
            Log::error('Error in renderView: ' . $e->getMessage());
            return redirect()->back()->with('toast_error', 'Error rendering view');
        }
    }
}
