<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ExceptionController;

class ExceptionApprovalController extends Controller
{
    /**
     * Retrieves pending exceptions for supervisor approval
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function exceptionSupList()
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
            $apiEndpoint = 'http://192.168.1.200:5126/Auditor/ExceptionTracker/pending-batch-exceptions';
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
            $exceptions = $response->json();

            if (!is_array($exceptions)) {
                Log::error('Invalid API response format', ['response' => $exceptions]);
                throw new \RuntimeException('Invalid data received from server');
            }

            // 6. Process Exceptions or Create Empty Dataset
            if (empty($exceptions)) {
                Log::info('No pending exceptions found');
                $pendingExceptions = [[
                    'id' => '---',
                    'status' => '---',
                    'submittedBy' => '---',
                    'submittedAt' => '---',
                    'groupName' => '---',
                    'department' => '---',
                    'exceptionCount' => '---',
                ]];
            } else {
                $pendingExceptions = collect($exceptions)
                    ->filter(function ($exception) {
                        if (!isset($exception['status'])) {
                            Log::warning('Invalid exception structure - missing status', ['exception' => $exception]);
                            return false;
                        }
                        return ($exception['status'] ?? null) === 'PENDING';
                    })
                    ->map(function ($exception) {
                        $pendingCount = collect($exception['exceptions'] ?? [])
                            ->where('status', 'PENDING')
                            ->count();

                        return [
                            'id' => $exception['id'] ?? null,
                            'status' => $exception['status'] ?? 'PENDING',
                            'submittedBy' => $exception['submittedBy'] ?? 'Unknown',
                            'submittedAt' => $exception['submittedAt'] ?? now()->format('Y-m-d H:i:s'),
                            'groupName' => $exception['exceptionBatch']['activityGroupName'] ?? 'N/A',
                            'department' => $exception['departmentName'] ?? 'Unknown Department',
                            'exceptionCount' => $pendingCount,
                        ];
                    })
                    ->filter(function ($exception) {
                        return ($exception['exceptionCount'] ?? 0) > 0;
                    })
                    ->values()
                    ->all();
            }

            // 7. Return View with Processed Data
            return view('exception-setup.supervisor-approval-list', [
                'pendingExceptions' => $pendingExceptions,
                'isEmpty' => empty($pendingExceptions) ||
                    (count($pendingExceptions) === 1 && $pendingExceptions[0]['id'] === '---')
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




    /**
     * Retrieves pending exceptions for auditee response
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */













    public function supervisorApproveOrDeclineSingleException(Request $request)
    {
        $access_token = session('api_token');

        if (empty($access_token)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired, please login to access the application'
                ], 401);
            }
            return redirect()->route('login')->with('toast_warning', 'Session expired, login to access the application');
        }

        try {
            $request->validate([
                'batchExceptionId' => 'required|integer',
                'singleExceptionId' => 'required|integer',
                'status' => 'required|string|in:AMENDMENT,DECLINED',
                'statusComment' => 'nullable|string|max:255',
            ]);

            $exceptionId = $request->input('singleExceptionId');
            $data = [
                'batchExceptionId' => $request->input('batchExceptionId'),
                'singleExceptionId' => $exceptionId,
                'status' => $request->input('status'),
            ];

            if ($request->status == 'DECLINED') {
                $data['statusComment'] = $request->input('statusComment');
            }

            // dd($data);

            $response = Http::withToken($access_token)
                ->put('http://192.168.1.200:5126/Auditor/ExceptionTracker/update-single-exception-status/', $data);

            if ($response->successful()) {
                $actionMessage = $request->status == 'AMENDMENT' ? 'pushed for amendment' : 'declined';
                $message = "Exception has been $actionMessage successfully";

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message
                    ]);
                }

                return redirect()->back()->with('toast_success', $message);
            }

            $errorData = [
                'status' => $response->status(),
                'response' => $response->body(),
                'exceptionId' => $exceptionId
            ];

            if ($response->status() == 404) {
                Log::error('Single exception not found', $errorData);
                $message = 'Exception not found for ID: ' . $exceptionId;
            } elseif ($response->status() == 422) {
                Log::error('Validation error while updating single exception status', $errorData);
                $message = 'Validation error for ID: ' . $exceptionId;
            } else {
                Log::error('Failed to update single exception status', $errorData);
                $message = 'Failed to update exception status for ID: ' . $exceptionId;
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => $response->status() == 422 ? $response->json()['errors'] : null
                ], $response->status());
            }

            return redirect()->back()->with('toast_error', $message);
        } catch (\Exception $e) {
            Log::error('Exception in supervisorApproveOrDeclineSingleException', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Server error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('toast_error', 'An unexpected error occurred');
        }
    }

    public function supervisorActionOnBatchException(Request $request)
    { //Edit or Approved or Declined

        // dd($request->all());

        $access_token = session('api_token');

        if (empty($access_token)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired, please login to access the application'
                ], 401);
            }
            return redirect()->route('login')->with('toast_warning', 'Session expired, login to access the application');
        }

        try {
            $request->validate([
                'batchExceptionId' => 'required|integer',
                // 'singleExceptionId' => 'required|integer',
                'status' => 'required|string|in:APPROVED,AMENDMENT,ANALYSIS,DECLINED,PENDING,RESOLVED',
                'statusComment' => 'nullable|string|max:255',
            ]);

            $exceptionId = $request->input('batchExceptionId');
            $data = [
                'batchExceptionId' => $request->input('batchExceptionId'),
                'status' => $request->input('status'),
            ];

            if ($request->status == 'DECLINED') {
                $data['statusComment'] = $request->input('statusComment');
            }

            // dd($data);

            $response = Http::withToken($access_token)
                ->put('http://192.168.1.200:5126/Auditor/ExceptionTracker/update-batch-exception-status/', $data);

            if ($response->successful()) {
                // Define status messages mapping
                $statusMessages = [
                    'PENDING' => 'supervisor for review',
                    'APPROVED' => 'approved',
                    'AMENDMENT' => 'amendment',
                    'ANALYSIS' => 'analysis',
                    'RESOLVED' => 'resolved',
                ];

                // Get action message with fallback
                $actionMessage = $statusMessages[$request->status] ?? 'declined';
                $message = "Exception has been pushed to $actionMessage successfully";

                // Handle AJAX/JSON responses
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message
                    ]);
                }
                //auditor.analysis.exception
                // Handle regular HTTP responses based on status
                // Handle regular HTTP responses based on status
                return $request->status === 'ANALYSIS'
                    ? redirect()->back()->with('toast_success', $message)
                    : ($request->status === 'PENDING'
                        ? redirect()->route('exception.auditor.list')->with('toast_success', $message)
                        : ($request->status === 'RESOLVED'
                            ? redirect()->route('auditor.analysis.exception')->with('toast_success', $message)
                            : redirect()->route('exception.supervisor.list')->with('toast_success', $message)));
            }

            $errorData = [
                'status' => $response->status(),
                'response' => $response->body(),
                'exceptionId' => $exceptionId
            ];

            if ($response->status() == 404) {
                Log::error('Batch exception not found', $errorData);
                $message = 'Batch Exception not found for ID: ' . $exceptionId;
            } elseif ($response->status() == 422) {
                Log::error('Validation error while updating single exception status', $errorData);
                $message = 'Validation error for ID: ' . $exceptionId;
            } else {
                Log::error('Failed to update batch exception status', $errorData);
                $message = 'Failed to update batch exception status for ID: ' . $exceptionId;
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => $response->status() == 422 ? $response->json()['errors'] : null
                ], $response->status());
            }

            return redirect()->back()->with('toast_error', $message);
        } catch (\Exception $e) {
            Log::error('Exception in supervisorActionOnBatchException', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Server error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('toast_error', 'An unexpected error occurred');
        }
    }



    /**
     * Show the form for creating a new resource.
     */
    public function exceptionAuditorList(Request $request)
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
            $apiEndpoint = 'http://192.168.1.200:5126/Auditor/ExceptionTracker/pending-batch-exceptions';
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
            $exceptions = $response->json();

            if (!is_array($exceptions)) {
                Log::error('Invalid API response format', ['response' => $exceptions]);
                throw new \RuntimeException('Invalid data received from server');
            }

            // 6. Process Exceptions or Create Empty Dataset
            if (empty($exceptions)) {
                Log::info('No pending exceptions found');
                $pendingExceptions = [[
                    'id' => '---',
                    'status' => '---',
                    'submittedBy' => '---',
                    'submittedAt' => '---',
                    'groupName' => '---',
                    'department' => '---',
                    'exceptionCount' => '---',
                ]];
            } else {
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

                $pendingExceptions = collect($exceptions)
                    // Filter top-level exceptions by status
                    ->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $batchGroupMap, $topManagers, $employeeRoleId) {
                        $groupId = $batchGroupMap[$exception['exceptionBatchId']] ?? null;
                        return $validBatches->has($exception['exceptionBatchId']) &&
                            $validGroups->has($groupId) && (in_array($exception['status'], ['DECLINED', 'AMENDMENT', 'APPROVED'])) && $employeeGroups->contains($groupId) || (in_array($employeeRoleId, $topManagers));
                    })
                    // Transform and count nested exceptions
                    ->map(function ($exception) {
                        $pendingCount = collect($exception['exceptions'] ?? [])
                            ->whereIn('status', ['DECLINED', 'PENDING'])
                            ->count();

                        return [
                            'id' => $exception['id'] ?? null,
                            'status' => $exception['status'] ?? '---',
                            'submittedBy' => $exception['submittedBy'] ?? 'Unknown',
                            'submittedAt' => $exception['submittedAt'] ?? now()->format('Y-m-d H:i:s'),
                            'groupName' => $exception['exceptionBatch']['activityGroupName'] ?? 'N/A',
                            'department' => $exception['departmentName'] ?? 'Unknown Department',
                            'exceptionCount' => $pendingCount, // Count of DECLINED/AMENDMENT nested exceptions
                        ];
                    })
                    // Only include if at least 1 nested exception matches
                    ->filter(function ($exception) {
                        return ($exception['exceptionCount'] ?? 0) > 0;
                    })
                    ->values()
                    ->all();
            }

            // 7. Return View with Processed Data
            return view('exception-setup.auditor-approval-list', [
                'pendingExceptions' => $pendingExceptions,
                'isEmpty' => empty($pendingExceptions) ||
                    (count($pendingExceptions) === 1 && $pendingExceptions[0]['id'] === '---')
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

    public function auditorPushForAnalysis(Request $request)
    {
        // Validate session
        $access_token = session('api_token');
        if (empty($access_token)) {
            Log::warning('Session token missing in auditorPushForAnalysis');
            return redirect()->route('login')
                ->with('toast_warning', 'Session expired, please login to continue');
        }

        // Validate request data
        $request->validate([
            'batchExceptionId' => 'required|integer',
            'status' => 'required|string|in:ANALYSIS',
        ]);

        // dd($request->all());
    }


    public function auditorAnalysisExceptionList()
    {
        // 1. Session and Token Validation
        $access_token = session('api_token');
        if (empty($access_token)) {
            Log::warning('Session token missing in auditorAnalysisExceptionList');
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

                return view('exception-setup.auditor-analysis-list', [
                    'pendingExceptions' => [],
                    'isEmpty' => true
                ]);
            }

            // 4. Process API response
            $exceptions = $response->json();
            if (!is_array($exceptions)) {
                Log::error('Invalid API response format', ['response' => $exceptions]);
                return view('exception-setup.auditor-analysis-list', [
                    'pendingExceptions' => [],
                    'isEmpty' => true
                ]);
            }

            // 5. Handle empty exceptions - return empty view
            if (empty($exceptions)) {
                Log::info('No pending exceptions found');
                return view('exception-setup.auditor-analysis-list', [
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
                ->map(function ($exception) {
                    $nestedExceptions = collect($exception['exceptions'] ?? []);
                    $pendingCount = $nestedExceptions
                        ->whereIn('status', ['APPROVED', 'RESOLVED'])
                        ->where('recommendedStatus', 'RESOLVED')
                        ->count();

                    return [
                        'id' => $exception['id'] ?? null,
                        'status' => $exception['status'] ?? 'UNKNOWN',
                        'submittedBy' => $exception['submittedBy'] ?? 'Unknown',
                        'submittedAt' => $exception['submittedAt'] ?? now()->format('Y-m-d H:i:s'),
                        'groupName' => $exception['exceptionBatch']['activityGroupName'] ?? 'N/A',
                        'department' => $exception['departmentName'] ?? 'Unknown Department',
                        'exceptionCount' => $pendingCount,
                    ];
                })
                ->filter(fn($exception) => ($exception['exceptionCount'] ?? 0) > 0)
                ->values()
                ->all();

            // 9. Always return view - let Blade handle empty state
            return view('exception-setup.auditor-analysis-list', [
                'pendingExceptions' => $pendingExceptions,
                'isEmpty' => empty($pendingExceptions)
            ]);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('HTTP request exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            // Return empty view instead of redirect
            return view('exception-setup.auditor-analysis-list', [
                'pendingExceptions' => [],
                'isEmpty' => true
            ]);
        } catch (\Exception $e) {
            Log::critical('Unexpected error in auditorAnalysisExceptionList', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return empty view instead of redirect
            return view('exception-setup.auditor-analysis-list', [
                'pendingExceptions' => [],
                'isEmpty' => true
            ]);
        }
    }




    public function auditeeResponse(Request $request)
    {
        $access_token = session('api_token');

        if (empty($access_token)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired, please login to access the application'
                ], 401);
            }
            return redirect()->route('login')->with('toast_warning', 'Session expired, login to access the application');
        }

        try {
            // Validate request data
            $validatedData = $request->validate([
                'exceptionItemId' => 'required|integer',
                'status' => 'required|string',
                // 'push_bak_status' => 'required|string',
                'statusComment' => 'required|string|max:255',
            ]);

            // dd($validatedData);

            $data = [
                'id' => (int) $validatedData['exceptionItemId'],
                'recommendedStatus' => $validatedData['status'],
                'statusComment' => $validatedData['statusComment'],
            ];

            // dd($data);

            // Make API request to update exception status
            $response = Http::withToken($access_token)
                ->put('http://192.168.1.200:5126/Auditor/ExceptionTracker/auditee-update/', $data);

            if ($response->successful()) {
                $message = 'Exception response has been submitted successfully';

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message
                    ]);
                }

                return redirect()->back()->with('toast_success', $message);
            }

            // Handle API errors
            $errorData = [
                'status' => $response->status(),
                'response' => $response->body(),
                'exceptionId' => $validatedData['exceptionItemId']
            ];

            $message = match ($response->status()) {
                404 => 'Exception not found for ID: ' . $validatedData['exceptionItemId'],
                422 => 'Validation error for ID: ' . $validatedData['exceptionItemId'],
                default => 'Failed to update exception status for ID: ' . $validatedData['exceptionItemId']
            };

            Log::error('API Error while updating exception status', $errorData);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => $response->status() == 422 ? $response->json()['errors'] ?? null : null
                ], $response->status());
            }

            return redirect()->back()->with('toast_error', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->with('toast_error', 'Please check your input and try again');
        } catch (\Exception $e) {
            Log::error('Unexpected error in auditeeResponse', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $message = 'An unexpected error occurred. Please try again.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 500);
            }

            return redirect()->back()->with('toast_error', $message);
        }
    }




    public function getFilteredExceptions($employeeId)
    {
        //filtering is based on groups if you don't belong to a group, you don't see an exception
        //you only see exceptions in a particular group you are part of, But top managers can see all exceptions

        // Fetch data from APIs
        $exceptions = ExceptionManipulationController::getExceptions(); //same as all reports data
        $batches = BatchController::getBatches();
        $groups = GroupController::getActivityGroups();
        $groupMembers = GroupMembersController::getGroupMembers();

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

        // Filter exceptions - and include top managers
        $filteredExceptions = collect($exceptions)->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $batchGroupMap, $topManagers, $employeeRoleId) {
            $groupId = $batchGroupMap[$exception->exceptionBatchId] ?? null;
            return $validBatches->has($exception->exceptionBatchId) &&
                $validGroups->has($groupId) && ($exception->status == 'PENDING' && $exception->recommendedStatus == null) && $employeeGroups->contains($groupId) || (in_array($employeeRoleId, $topManagers));
        });


        return $filteredExceptions->values()->all();
    }
}
