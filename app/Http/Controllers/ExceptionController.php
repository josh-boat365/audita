<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\BatchController;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Pagination\LengthAwarePaginator;

class ExceptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $access_token = session('api_token');

        if (empty($access_token)) {
            return redirect()->route('login')->with('toast_warning', 'Session expired, login to access the application');
        }

        $employeeId = $this->getLoggedInUserInformation()->id;
        $filteredExceptions = $this->getFilteredExceptions($employeeId);
        $sortDescending = collect($filteredExceptions)->sortByDesc('createdAt');

        $exceptions = $this->paginate($sortDescending, 15, $request);

        // dd($exceptions);

        return view('exception-setup.index', compact('exceptions', 'employeeId'));
    }


    public function pendingExceptions(Request $request)
    {
        $access_token = session('api_token');

        if (empty($access_token)) {
            return redirect()->route('login')->with('toast_warning', 'Session expired, login to access the application');
        }

        $employeeId = $this->getLoggedInUserInformation()->id;
        // dd($employeeId);
        $pendingExceptions = $this->getPendingExceptions($employeeId);
        $sortDescending = collect($pendingExceptions)->sortByDesc('createdAt');

        $exceptions = $this->paginate($sortDescending, 15, $request);


        //store exception count in session
        session(['pending_exception_count' => $exceptions->count()]);

        return view('exception-setup.pending', compact('exceptions', 'employeeId'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = $this->departmentData();
        $batches = BatchController::getBatches();
        $processTypes = ProcessTypeController::getProcessTypes();
        $riskRates = RiskRateController::getRiskRates();

        return view('exception-setup.create', compact('departments', 'batches', 'processTypes', 'riskRates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'exception' => 'required|string',
            'rootCause' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:8',
            'occurrenceDate' => 'required|date_format:Y-m-d',
            'proposeResolutionDate' => 'nullable|date_format:Y-m-d',
            'resolutionDate' => 'nullable|date_format:Y-m-d',
            'processTypeId' => 'required|integer',
            'riskRateId' => 'nullable|integer',
            'departmentId' => 'required|integer',
            'exceptionBatchId' => 'required|integer',
        ]);
        // dd($request);
        $access_token = session('api_token');

        $data = [
            'exception' => $request->input('exception'),
            'rootCause' => $request->input('rootCause'),
            'status' => $request->input('status'),
            'occurrenceDate' => Carbon::createFromFormat('Y-m-d', $request->input('occurrenceDate'))->format('Y-m-d'),
            'proposeResolutionDate' => $request->input('proposeResolutionDate') ?
                Carbon::createFromFormat('Y-m-d', $request->input('proposeResolutionDate'))->format('Y-m-d') : null,
            'resolutionDate' => $request->input('resolutionDate') ?
                Carbon::createFromFormat('Y-m-d', $request->input('resolutionDate'))->format('Y-m-d') : null,
            'processTypeId' => $request->input('processTypeId'),
            'riskRateId' => $request->input('riskRateId'),
            'departmentId' => $request->input('departmentId'),
            'exceptionBatchId' => $request->input('exceptionBatchId'),

        ];

        try {
            $response = Http::withToken($access_token)->post('http://192.168.1.200:5126/Auditor/ExceptionTracker', $data);

            if ($response->successful()) {

                return redirect()->route('exception.list')->with('toast_success', 'Exception created successfully');
            } else {
                // Log the error response
                Log::error('Failed to create Exception', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to create Exception');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while creating Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
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

    public function edit2($id)
    {
        try {
            // Get all necessary data
            $batches = BatchController::getBatches();
            $departments = $this->departmentData();
            $processTypes = ProcessTypeController::getProcessTypes();
            $riskRates = RiskRateController::getRiskRates();
            $groups = GroupController::getActivityGroups();
            $groupMembers = GroupMembersController::getGroupMembers();
            $exceptions = $this->getExceptions();

            // Get the exception and validate it exists
            $exception = $this->getAnException($id);
            if (!$exception) {
                toast('Exception not found', 'error');
                return redirect()->back();
            }

            // dd($exception);

            // Get user information
            $user = $this->getLoggedInUserInformation();
            $employeeId = $user->id;
            $employeeDepartmentId = $user->departmentId;
            $employeeName = $user->firstName . ' ' . $user->surname;

            // Find the batch associated with the exception
            $exceptionBatch = collect($batches)->firstWhere('id', $exception->exceptionBatchId);
            if (!$exceptionBatch) {
                toast('Associated batch not found', 'error');
                return redirect()->back();
            }

            //Get all auditor ids from created exceptions
            $auditorIds = collect($exceptions)
                ->pluck('auditorId')
                ->unique()
                ->toArray();

            // dd($auditorIds);

            // Get all auditor IDs in the same group as the exception
            $groupAuditorIds = collect($groupMembers)
                ->where('activityGroupId', $exceptionBatch->activityGroupId)
                ->pluck('employeeId')
                ->toArray();

            // Determine if current user can edit (is auditor or in same group)
            $canEdit = ($exception->auditorId == $employeeId) ||
                (in_array($employeeId, $groupAuditorIds));


            return view('exception-setup.edit', compact(
                'exception',
                'batches',
                'departments',
                'processTypes',
                'riskRates',
                'groups',
                'groupAuditorIds',
                'employeeId',
                'employeeDepartmentId',
                'employeeName',
                'canEdit'
            ));
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection Error: Unable to reach Exception edit API', ['error' => $e->getMessage()]);
            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Exception occurred while fetching exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with(
                'toast_error',
                'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>'
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
        // Validate request data
        $validated = $request->validate([
            'exceptionTitle' => 'required|string|max:255',
            'exception' => 'required|string|max:255',
            'rootCause' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:8',
            'statusComment' => 'nullable|string|max:255',
            'occurrenceDate' => 'required|date_format:Y-m-d',
            'proposeResolutionDate' => 'nullable|date_format:Y-m-d',
            'resolutionDate' => 'nullable|date_format:Y-m-d',
            'processTypeId' => 'required|integer',
            'subProcessTypeId' => 'required|integer',
            'riskRateId' => 'nullable|integer',
            'departmentId' => 'required|integer',
            'exceptionBatchId' => 'required|integer',
            'requestTrackerId' => 'nullable|integer',
            'requestType' => 'nullable|string',
        ]);

        // Validate that the ID parameter is numeric
        if (!is_numeric($id)) {
            Log::error('Invalid ID parameter provided', ['id' => $id]);
            return redirect()->back()->with('toast_error', 'Invalid exception identifier');
        }

        $accessToken = session('api_token');
        $isBatchRequest = $validated['requestType'] === 'BATCH';

        // Check for authentication
        if (!$accessToken) {
            Log::error('No API token found in session');
            return redirect()->back()->with('toast_error', 'Authentication required. Please login again.');
        }

        try {
            // Build update data with CSRF token
            $data = array_merge([
                'id' => $id,
                '_token' => csrf_token(), // Add CSRF protection
            ], $validated);

            // Add requestTrackerId and requestType only for batch requests
            if (!$isBatchRequest) {
                unset($data['requestTrackerId'], $data['requestType']);
            }

            // Initialize variables for batch processing
            $shouldUpdateBatchStatus = false;
            $approvedCount = 0;
            $resolvedCount = 0;

            if ($isBatchRequest && isset($validated['requestTrackerId'])) {
                // Rate limit batch status checks (max 5 per minute)
                RateLimiter::attempt(
                    'batch-status-check:' . $validated['requestTrackerId'],
                    5,
                    function () use ($accessToken, $validated, &$batchData) {
                        $batchResponse = Http::withToken($accessToken)
                            ->timeout(30)
                            ->retry(3, 100) // Retry 3 times with 100ms delay
                            ->get("http://192.168.1.200:5126/Auditor/ExceptionTracker/get-batch-exception/{$validated['requestTrackerId']}");

                        if ($batchResponse->successful()) {
                            $batchData = $batchResponse->object();
                        }
                    }
                );

                if (isset($batchData->exceptions)) {
                    $currentExceptionStatus = null;

                    // Count APPROVED and RESOLVED exceptions and find current exception status
                    foreach ($batchData->exceptions as $exception) {
                        if (isset($exception->status)) {
                            if ($exception->status === 'APPROVED') {
                                $approvedCount++;
                            } elseif ($exception->status === 'RESOLVED') {
                                $resolvedCount++;
                            }

                            // Find the current exception's status
                            if (isset($exception->id) && $exception->id == $id) {
                                $currentExceptionStatus = $exception->status;
                            }
                        }
                    }

                    // Only adjust counts if we're actually changing from APPROVED to RESOLVED
                    if ($currentExceptionStatus === 'APPROVED' && $validated['status'] === 'RESOLVED') {
                        $approvedCount--; // One less APPROVED
                        $resolvedCount++; // One more RESOLVED
                        Log::info('Exception status transition from APPROVED to RESOLVED', [
                            'exception_id' => $id,
                            'batch_id' => $validated['requestTrackerId']
                        ]);
                    }

                    // Update batch status only if all APPROVED exceptions are now RESOLVED
                    $shouldUpdateBatchStatus = ($approvedCount === 0 && $resolvedCount > 0);
                }
            }

            // Update the exception with rate limiting
            $updateResponse = RateLimiter::attempt(
                'exception-update:' . $id,
                5,
                function () use ($accessToken, $data) {
                    return Http::withToken($accessToken)
                        ->timeout(30)
                        ->retry(3, 100) // Retry 3 times with 100ms delay
                        ->put('http://192.168.1.200:5126/Auditor/ExceptionTracker', $data);
                },
                60 // 1 minute decay
            );

            if (!$updateResponse || !$updateResponse->successful()) {
                Log::error('Failed to update Exception', [
                    'status' => $updateResponse ? $updateResponse->status() : 'no-response',
                    'exception_id' => $id,
                    'batch_request' => $isBatchRequest
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to update Exception');
            }

            Log::info('Exception updated successfully', ['exception_id' => $id]);

            // Update batch status if needed
            if ($shouldUpdateBatchStatus) {
                $batchStatusResponse = Http::withToken($accessToken)
                    ->timeout(30)
                    ->retry(3, 100)
                    ->put('http://192.168.1.200:5126/Auditor/ExceptionTracker/update-batch-exception-status', [
                        'batchExceptionId' => $validated['requestTrackerId'],
                        'status' => 'RESOLVED',
                        '_token' => csrf_token() // CSRF protection
                    ]);

                if ($batchStatusResponse->successful()) {
                    Log::info('Batch status updated to RESOLVED', [
                        'batch_id' => $validated['requestTrackerId'],
                        'resolved_count' => $resolvedCount
                    ]);

                    return redirect()->route('auditor.analysis.exception')
                        ->with('toast_success', 'Exception updated successfully. All batch exceptions have been resolved.');
                } else {
                    Log::warning('Exception updated but batch status update failed', [
                        'request_tracker_id' => $validated['requestTrackerId'],
                        'status' => $batchStatusResponse->status()
                    ]);
                }
            }

            return redirect()->back()->with('toast_success', 'Exception updated successfully');
        } catch (\Exception $e) {
            Log::error('Exception occurred while updating Exception', [
                'exception_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with(
                'toast_error',
                'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>'
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */



    public function destroy(Request $request, string $id)
    {
        // Get the access token from the session
        $accessToken = session('api_token');

        try {
            // Make the DELETE request to the external API
            $response = Http::withToken($accessToken)
                ->delete("http://192.168.1.200:5126/Auditor/ExceptionTracker/{$id}");

            // Check the response status and return appropriate response
            if ($response->successful()) {
                return redirect()->route('exception.list')->with('toast_success', 'Exception deleted successfully');
            } else {
                // Log the error response
                Log::error('Failed to delete Exception', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to delete Exception: ' . $response->body());
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while deleting Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }
    public function recommendExceptionForResolution(Request $request, string $id)
    {
        $request->validate([
            'resolution' => 'required|string'
        ]);

        $data = [
            'id' => $id,
            'recommendedStatus' => $request->input('resolution')
        ];

        // Get the access token from the session
        $accessToken = session('api_token');

        try {
            // Make the DELETE request to the external API
            $response = Http::withToken($accessToken)
                ->put("http://192.168.1.200:5126/Auditor/ExceptionTracker/auditee-update/", $data);

            // Check the response status and return appropriate response
            if ($response->successful()) {
                return redirect()->route('exception.list')->with('toast_success', 'Exception recommended for resolution successfully');
            } else {
                // Log the error response
                Log::error('Failed to recommended for resolution Exception', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to recommended for resolution Exception: ' . $response->body());
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while recommending for resolution Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }
    public function closeException(Request $request, string $id)
    {
        // Get the access token from the session
        $accessToken = session('api_token');

        $data = [
            'id' => $id
        ];

        try {
            // Make the DELETE request to the external API
            $response = Http::withToken($accessToken)
                ->put("http://192.168.1.200:5126/Auditor/ExceptionTracker/close-exception", $data);

            // Check the response status and return appropriate response
            if ($response->successful()) {
                if (URL::current() == route('exception.list')) {
                    return redirect()->route('exception.list')->with('toast_success', 'Exception closed successfully');
                } else {
                    return redirect()->route('exception.pending')->with('toast_success', 'Exception closed successfully');
                }
            } else {
                // Log the error response
                Log::error('Failed to close Exception', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to close Exception: ' . $response->body());
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while closing Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }


    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:255',
        ]);

        $access_token = session('api_token');

        $data = [
            'exceptionTrackerId' => $id,
            'comment' => $request->input('comment'),
        ];

        // dd($data);

        try {
            $response = Http::withToken($access_token)->post('http://192.168.1.200:5126/Auditor/ExceptionComment', $data);

            if ($response->successful()) {
                return redirect()->back()->with('toast_success', 'Comment added successfully');
            } else {
                // Log the error response
                Log::error('Failed to add comment', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to add comment');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception comment API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            Log::error('Exception occurred while adding comment', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }


    public function updateComment(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'comment' => 'required|string|max:255',
            'exceptionTrackerId' => 'required|integer',
        ]);

        $access_token = session('api_token');

        $data = [
            'id' => $id,
            'exceptionTrackerId' => $request->input('exceptionTrackerId'),
            'comment' => $request->input('comment'),
        ];


        try {
            $response = Http::withToken($access_token)->put('http://192.168.1.200:5126/Auditor/ExceptionComment', $data);

            if ($response->successful()) {
                return redirect()->back()->with('toast_success', 'Comment updated successfully');
            } else {
                // Log the error response
                Log::error('Failed to update comment', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'comment_id' => $id
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to update comment');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception comment API for update', [
                'error' => $e->getMessage(),
                'comment_id' => $id
            ]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Exception occurred while updating comment', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'comment_id' => $id
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }


    public function deleteComment($id)
    {
        $access_token = session('api_token');

        try {
            // Make the DELETE request to the external API
            $response = Http::withToken($access_token)
                ->delete("http://192.168.1.200:5126/Auditor/ExceptionComment/{$id}");

            // Check the response status and return appropriate response
            if ($response->successful()) {
                return redirect()->back()->with('toast_success', 'Exception Comment Deleted successfully');
            } else {
                // Log the error response
                Log::error('Failed to delete Exception Comment', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to delete Exception Comment');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception Comment delete API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while deleting Exception Comment', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }

    public static function getExceptionComments($exceptionId)
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/ExceptionComment');

            if ($response->successful()) {

                $api_response = $response->object() ?? [];
                $comments = collect($api_response)->filter(fn($comment) => $comment->exceptionTrackerId == $exceptionId)->all() ?? [];
            } elseif ($response->status() == 404) {
                $comments = [];
                Log::warning('Exception comments API returned 404 Not Found');
                toast('Exception comments data not found', 'warning');
            } else {
                $comments = [];
                Log::error('Exception comments API request failed', ['status' => $response->status()]);
                toast('Error fetching exception comments data', 'error');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception comment API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            $comments = [];
            Log::error('Error fetching exception comments', ['error' => $e->getMessage()]);
            toast('An error occurred. Please try again later', 'error');
        }
        return $comments;
    }


    public function exceptionFileUpload(Request $request, $id)
    {
        $validated = $request->validate([
            'files' => 'nullable|array',
            'files.*' => 'file|max:5120|mimes:png,jpg,jpeg,txt,pdf,doc,docx',
        ]);

        $files = $request->file('files') ?? [];
        if (empty($files)) {
            return response()->json(['status' => 'error', 'message' => 'No file uploaded'], 400);
        }

        $accessToken = session('api_token');
        if (!$accessToken) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized: Missing API token'], 401);
        }

        $apiUrl = 'http://192.168.1.200:5126/Auditor/ExceptionFileUpload';
        $uploadedFiles = [];

        try {
            foreach ($files as $file) {
                // Get the MIME type
                $mimeType = $file->getMimeType(); // Example: "image/jpeg", "application/pdf"
                $base64File = base64_encode(file_get_contents($file->getRealPath()));

                // Append the MIME type header to the base64 data
                $fileData = "data:$mimeType;base64,$base64File";

                $payload = [
                    'exceptionTrackerId' => $id,
                    'fileName' => $file->getClientOriginalName(),
                    'fileData' => $fileData, // Correctly formatted with MIME type
                ];

                $response = Http::withToken($accessToken)->post($apiUrl, $payload);

                if ($response->successful()) {
                    $uploadedFiles[] = [
                        'fileName' => $file->getClientOriginalName(),
                        'uploadDate' => now()->toDateTimeString(),
                        'fileData' => $fileData, // Store file with header
                    ];
                }
            }

            if (!empty($uploadedFiles)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'All files uploaded successfully',
                    'files' => $uploadedFiles
                ], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'File upload failed'], 500);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception file upload API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong, check your internet and try again'], 500);
        }
    }



    public function downloadExceptionFile($fileId)
    {
        try {
            $accessToken = session('api_token');

            if (!$accessToken) {
                Log::error('Unauthorized: Missing API token for file download');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: Missing API token'
                ], 401);
            }

            $apiUrl = "http://192.168.1.200:5126/Auditor/ExceptionFileUpload/{$fileId}";
            $response = Http::withToken($accessToken)->get($apiUrl);

            Log::info('Download API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to fetch file'
                ], 500);
            }

            $fileData = $response->json();

            $files = explode(',', $fileData['fileData'], 2);
            $base64Data = $files[1];

            return response()->json([
                'status' => 'success',
                'fileName' => $fileData['fileName'],
                'fileData' => $base64Data, // This includes the file header!
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception file download API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            Log::error('Error downloading file', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong, please try again'
            ], 500);
        }
    }




    public static function exceptionFileDelete($exceptionId)
    {
        $access_token = session('api_token');

        try {
            // Make the DELETE request to the external API
            $response = Http::withToken($access_token)
                ->delete("http://192.168.1.200:5126/Auditor/ExceptionFileUpload/{$exceptionId}");

            // Check the response status and return appropriate response
            if ($response->successful()) {
                return redirect()->back()->with('toast_success', 'Exception File removed successfully');
            } else {
                // Log the error response
                Log::error('Failed to delete Exception File', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to delete Exception File');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception file delete API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while deleting Exception File', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }


    public function deleteExceptionFile($fileId)
    {
        try {
            $accessToken = session('api_token');

            if (!$accessToken) {
                Log::error('Unauthorized: Missing API token for file deletion');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: Missing API token'
                ], 401);
            }

            $apiUrl = "http://192.168.1.200:5126/Auditor/ExceptionFileUpload/{$fileId}";
            $response = Http::withToken($accessToken)->delete($apiUrl);

            Log::info('Delete API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete file'
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'File deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting file', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong, please try again'
            ], 500);
        }
    }



    public static function getExceptions()
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/ExceptionTracker');

            if ($response->successful()) {
                $exceptions = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $exceptions = [];
                Log::warning('Exception API returned 404 Not Found');
                toast('Exception  data not found', 'warning');
            } else {
                $exceptions = [];
                Log::error('Exception API request failed', ['status' => $response->status()]);
                toast('Error fetching exception  data', 'error');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception Tracker API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            $exceptions = [];
            Log::error('Error fetching exception data', ['error' => $e->getMessage()]);
            toast('Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>', 'error');
        }

        return $exceptions;
    }

    public static function getBatchExceptions()
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/ExceptionTracker/pending-batch-exceptions');

            if ($response->successful()) {
                $exceptions = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $exceptions = [];
                Log::warning('Exception API returned 404 Not Found: pending-batch-exceptions');
                toast('Exception  data not found', 'warning');
            } else {
                $exceptions = [];
                Log::error('Exception API request failed: pending-batch-exceptions', ['status' => $response->status()]);
                toast('Error fetching exception  data', 'error');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception Tracker API: pending-batch-exceptions', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            $exceptions = [];
            Log::error('Error fetching exception data: pending-batch-exceptions', ['error' => $e->getMessage()]);
            toast('Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>', 'error');
        }

        return $exceptions;
    }



    public static function getAnException($id)
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/ExceptionTracker/' . $id);

            if ($response->successful()) {
                $exception = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $exception = [];
                Log::warning('The Exception  API returned 404 Not Found');
                toast('The Exception  data not found', 'warning');
            } else {
                $exception = [];
                Log::error('The Exception  API request failed', ['status' => $response->status()]);
                toast('Error fetching exception  data', 'error');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception tracker API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            $exception = [];
            Log::error('Error fetching The Exception  data', ['error' => $e->getMessage()]);
            toast('Error fetching The Exception data', 'error');
        }

        return $exception;
    }

    public static function departmentData()
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5124/HRMS/Department');

            if ($response->successful()) {
                $departments = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $departments = [];
                Log::warning('Department API returned 404 Not Found');
                toast('Department data not found', 'warning');
            } else {
                $departments = [];
                Log::error('Department API request failed', ['status' => $response->status()]);
                toast('Error fetching department data', 'error');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Department API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            $departments = [];
            Log::error('Error fetching department data', ['error' => $e->getMessage()]);
            toast(
                'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>',
                'error'
            );
        }

        return $departments;
    }

    public static function getFilteredExceptions($employeeId)
    {
        //filtering is based on groups if you don't belong to a group, you don't see an exception
        //you only see exceptions in a particular group you are part of, But top managers can see all exceptions

        $instance = new self();

        // Fetch data from APIs
        $exceptions = $instance->getExceptions(); //same as all reports data
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

        $employeeRoleId = $instance->getLoggedInUserInformation()->empRoleId;

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


    public static function getFilteredBatchExceptions($employeeId)
    {
        //filtering is based on groups if you don't belong to a group, you don't see an exception
        //you only see exceptions in a particular group you are part of, But top managers can see all exceptions

        $instance = new self();

        // Fetch data from APIs
        $exceptions = $instance->getBatchExceptions(); //same as all reports data
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

        $employeeRoleId = $instance->getLoggedInUserInformation()->empRoleId;

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



    public function getPendingExceptions($employeeId)
    {

        //filtering is based on groups if you don't belong to a group, you don't see an exception
        //you only see exceptions in a particular group you are part of, But top managers can see all exceptions

        // Fetch data from APIs
        $exceptions = $this->getExceptions();
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

        // Map batch IDs to their corresponding activity group IDs
        $batchGroupMap = collect($batches)
            ->pluck('activityGroupId', 'id');


        $employeeRoleId = $this->getLoggedInUserInformation()->empRoleId;

        // top managers
        // 1 - Managing Director
        // 2 - Head of Internal Audit
        // 4 - Head of Internal Control & Compliance
        $topMangers = [1, 2, 4];

        // Filter exceptions - and include topManagers
        $filteredExceptions = collect($exceptions)->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $batchGroupMap, $topMangers, $employeeRoleId) {
            $groupId = $batchGroupMap[$exception->exceptionBatchId] ?? null;
            return $validBatches->has($exception->exceptionBatchId) &&
                $validGroups->has($groupId) && ($exception->recommendedStatus == 'RESOLVED' && $exception->status == 'PENDING' || $exception->status == 'PENDING') && $employeeGroups->contains($groupId) || (in_array($employeeRoleId, $topMangers));
        });

        return $filteredExceptions->values()->all();
    }


    public static function getLoggedInUserInformation()
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5124/HRMS/Employee/GetEmployeeInformation');

            if ($response->successful()) {
                return $response->object() ?? [];
            } elseif ($response->status() == 404) {
                Log::warning('Employee Information API returned 404 Not Found');

                toast('Employee Information not found', 'warning');
                return [];
            } else {
                Log::error('Employee Information API request failed', ['status' => $response->status()]);

                toast('Error fetching Employee Information data', 'error');
                return [];
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Employee Information API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            // Handle general exceptions
            Log::error('Error fetching Employee Information data', ['error' => $e->getMessage()]);

            toast('Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>', 'error');
            return [];
        }
    }


    public static function paginate(array|Collection $items, int $perPage, Request $request): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        if (!$items instanceof Collection) {
            $items = collect($items);
        }

        $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage);

        return new LengthAwarePaginator(
            $currentItems,
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}
