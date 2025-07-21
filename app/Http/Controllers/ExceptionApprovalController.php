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

    public function auditeeExceptionList()
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
            // dd($exceptions);

            $loggedInUser = ExceptionController::getLoggedInUserInformation();
            // dd($loggedInUser->departmentId);

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
                    'auditorDepartmentId' => '---', //current logged in user's department id (auditor department check)
                ]];
            } else {
                $pendingExceptions = collect($exceptions)
                    // Filter top-level exceptions by status
                    ->filter(function ($exception) {
                        $validParentStatuses = ['APPROVED'];
                        return isset($exception['status']) &&
                            in_array($exception['status'], $validParentStatuses);
                    })
                    // Transform and count nested exceptions
                    ->map(function ($exception) use ($loggedInUser) {
                        $pendingCount = collect($exception['exceptions'] ?? [])
                            ->where('status', 'APPROVED')
                            // ->where('recommendedStatus', null) // Only count if recommendedStatus is null
                            ->count();
                        $countForRespondedExceptionsByAuditee = collect($exception['exceptions'] ?? [])
                            ->where('recommendedStatus', 'RESOLVED')
                            ->count();

                        return [
                            'id' => $exception['id'] ?? null,
                            'status' => $exception['status'] ?? '---',
                            'submittedBy' => $exception['submittedBy'] ?? 'Unknown',
                            'submittedAt' => $exception['submittedAt'] ?? now()->format('Y-m-d H:i:s'),
                            'groupName' => $exception['exceptionBatch']['activityGroupName'] ?? 'N/A',
                            'department' => $exception['departmentName'] ?? 'Unknown Department',
                            'exceptionCount' => $pendingCount,
                            'auditorDepartmentId' =>  $loggedInUser->departmentId,
                            'countForRespondedExceptionsByAuditee' => $countForRespondedExceptionsByAuditee
                        ];
                    })
                    // Only include if at least 1 nested exception matches
                    ->filter(function ($exception) {
                        return ($exception['exceptionCount'] ?? 0) > 0;
                    })
                    ->values()
                    ->all();


                // dd($pendingExceptions);
            }

            // 7. Return View with Processed Data
            return view('exception-setup.auditee-exception-list', [
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
                                    && $subException->recommendedStatus == 'RESOLVED';
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
                        $preparedException = ExceptionController::getAnException($subException->id);

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
                'departments' => ExceptionController::departmentData() ?? [],
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
                'status' => 'required|string|in:APPROVED,AMENDMENT,ANALYSIS,DECLINED,PENDING',
                'statusComment' => 'nullable|string|max:255',
            ]);

            $exceptionId = $request->input('singleExceptionId');
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
                    'ANALYSIS' => 'push for analysis'
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

                // Handle regular HTTP responses based on status
                return $request->status === 'ANALYSIS'
                    ? redirect()->back()->with('toast_success', $message)
                    : ($request->status === 'PENDING' ? redirect()->route('exception.auditor.list')->with('toast_success', $message)
                        : redirect()->route('exception.supervisor.list')->with('toast_success', $message));
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
                $pendingExceptions = collect($exceptions)
                    // Filter top-level exceptions by status
                    ->filter(function ($exception) {
                        $validParentStatuses = ['DECLINED', 'AMENDMENT', 'APPROVED'];
                        return isset($exception['status']) &&
                            in_array($exception['status'], $validParentStatuses);
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
                    // Filter top-level exceptions by status
                    ->filter(function ($exception) {
                        return isset($exception['status']) && $exception['status'] === 'ANALYSIS';
                    })
                    // Transform and count nested exceptions
                    ->map(function ($exception) {
                        $pendingCount = collect($exception['exceptions'] ?? [])
                            ->where('status', 'APPROVED')
                            ->where('recommendedStatus', 'RESOLVED') // Only count if recommendedStatus is RESOLVED
                            ->count();

                        return [
                            'id' => $exception['id'] ?? null,
                            'status' => $exception['status'] ?? '---',
                            'submittedBy' => $exception['submittedBy'] ?? 'Unknown',
                            'submittedAt' => $exception['submittedAt'] ?? now()->format('Y-m-d H:i:s'),
                            'groupName' => $exception['exceptionBatch']['activityGroupName'] ?? 'N/A',
                            'department' => $exception['departmentName'] ?? 'Unknown Department',
                            'exceptionCount' => $pendingCount,
                        ];
                    })
                    // Only include if at least 1 nested exception matches
                    ->filter(function ($exception) {
                        return ($exception['exceptionCount'] ?? 0) > 0;
                    })
                    ->values()
                    ->all();


                // dd($pendingExceptions);
            }

            // 7. Return View with Processed Data
            return view('exception-setup.auditor-analysis-list', [
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
        $departments = ExceptionController::departmentData() ?? [];
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
        $exceptions = ExceptionController::getExceptions(); //same as all reports data
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

        $employeeRoleId = ExceptionController::getLoggedInUserInformation()->empRoleId;

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
