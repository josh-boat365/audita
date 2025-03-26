<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\BatchController;

class ExceptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $access_token = session('api_token');

        if (empty($access_token)) {
            return redirect()->route('login')->with('toast_warning', 'Session expired, login to access the application');
        }

        $employeeId = $this->getLoggedInUserInformation()->id;
        $exceptions = $this->getFilteredExceptions($employeeId);


        return view('exception-setup.index', compact('exceptions', 'employeeId'));
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
            'exception' => 'required|string|max:255',
            'rootCause' => 'required|string|max:255',
            'status' => 'required|string|max:8',
            'occurrenceDate' => 'required|date_format:d/m/Y',
            'proposeResolutionDate' => 'nullable|date_format:d/m/Y',
            'resolutionDate' => 'nullable|date_format:d/m/Y',
            'processTypeId' => 'required|integer',
            'riskRateId' => 'required|integer',
            'departmentId' => 'required|integer',
            'exceptionBatchId' => 'required|integer',
        ]);

        $access_token = session('api_token');

        $data = [
            'exception' => $request->input('exception'),
            'rootCause' => $request->input('rootCause'),
            'status' => $request->input('status'),
            'occurrenceDate' => Carbon::createFromFormat('d/m/Y', $request->input('occurrenceDate'))->format('Y-m-d'),
            'proposeResolutionDate' => $request->input('proposeResolutionDate') ?
                Carbon::createFromFormat('d/m/Y', $request->input('proposeResolutionDate'))->format('Y-m-d') : null,
            'resolutionDate' => $request->input('resolutionDate') ?
                Carbon::createFromFormat('d/m/Y', $request->input('resolutionDate'))->format('Y-m-d') : null,
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
        $access_token = session('api_token');
        try {

            $batches = BatchController::getBatches();
            $departments = $this->departmentData();
            $processTypes = ProcessTypeController::getProcessTypes();
            $riskRates = RiskRateController::getRiskRates();
            $exception = $this->getAnException($id);
            $employeeId = $this->getLoggedInUserInformation()->id;

            return view('exception-setup.edit', compact(
                'exception',
                'batches',
                'departments',
                'processTypes',
                'riskRates',
                'employeeId'
            ));
        } catch (\Exception $e) {
            Log::error('Exception occurred while fetching exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
        $request->validate([
            'exception' => 'required|string|max:255',
            'rootCause' => 'required|string|max:255',
            'status' => 'required|string|max:8',
            'occurrenceDate' => 'required|date_format:d/m/Y',
            'proposeResolutionDate' => 'nullable|date_format:d/m/Y',
            'resolutionDate' => 'nullable|date_format:d/m/Y',
            'processTypeId' => 'required|integer',
            'riskRateId' => 'required|integer',
            'departmentId' => 'required|integer',
            'exceptionBatchId' => 'required|integer',
        ]);

        $access_token = session('api_token');

        $data = [
            'id' => $id,
            'exception' => $request->input('exception'),
            'rootCause' => $request->input('rootCause'),
            'status' => $request->input('status'),
            'occurrenceDate' => Carbon::createFromFormat('d/m/Y', $request->input('occurrenceDate'))->format('Y-m-d'),
            'proposeResolutionDate' => $request->input('proposeResolutionDate') ? Carbon::createFromFormat('d/m/Y', $request->input('proposeResolutionDate'))->format('Y-m-d') : null,
            'resolutionDate' => $request->input('resolutionDate') ? Carbon::createFromFormat('d/m/Y', $request->input('resolutionDate'))->format('Y-m-d') : null,
            'processTypeId' => $request->input('processTypeId'),
            'riskRateId' => $request->input('riskRateId'),
            'departmentId' => $request->input('departmentId'),
            'exceptionBatchId' => $request->input('exceptionBatchId'),
        ];

        try {
            $response = Http::withToken($access_token)->put('http://192.168.1.200:5126/Auditor/ExceptionTracker', $data);

            if ($response->successful()) {
                return redirect()->route('exception.list')->with('toast_success', 'Exception updated successfully');
            } else {
                // Log the error response
                Log::error('Failed to update Exception', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to update Exception');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while updating Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
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

        try {
            // Make the DELETE request to the external API
            $response = Http::withToken($accessToken)
                ->put("http://192.168.1.200:5126/Auditor/ExceptionTracker/close-exception/{$id}");

            // Check the response status and return appropriate response
            if ($response->successful()) {
                return redirect()->route('exception.list')->with('toast_success', 'Exception closed successfully');
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
        } catch (\Exception $e) {
            Log::error('Exception occurred while adding comment', [
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
                $base64File = base64_encode(file_get_contents($file->getRealPath()));

                $payload = [
                    'exceptionTrackerId' => $id,
                    'fileName' => $file->getClientOriginalName(),
                    'fileData' => $base64File,
                ];

                $response = Http::withToken($accessToken)->post($apiUrl, $payload);

                if ($response->successful()) {
                    $uploadedFiles[] = [
                        'id' => uniqid(),
                        'fileName' => $file->getClientOriginalName(),
                        'uploadDate' => now()->toDateTimeString(),
                        'fileData' => $base64File,
                    ];
                }
            }

            if (!empty($uploadedFiles)) {
                return response()->json(['status' => 'success', 'message' => 'All files uploaded successfully', 'files' => $uploadedFiles], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'File upload failed'], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong, check your internet and try again'], 500);
        }
    }


    public function getExceptionFiles($id)
    {
        try {
            $accessToken = session('api_token');
            if (!$accessToken) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: Missing API token'
                ], 401);
            }

            $apiUrl = "http://192.168.1.200:5126/Auditor/ExceptionFileUpload?exceptionTrackerId={$id}";
            $response = Http::withToken($accessToken)->get($apiUrl);

            if (!$response->successful()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to fetch files'
                ], 500);
            }

            return response()->json($response->json()); // Return JSON response for AJAX
        } catch (\Exception $e) {
            Log::error('Error fetching files', [
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
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while deleting Exception File', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
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
        } catch (\Exception $e) {
            $exceptions = [];
            Log::error('Error fetching exception data', ['error' => $e->getMessage()]);
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
        } catch (\Exception $e) {
            $departments = [];
            Log::error('Error fetching department data', ['error' => $e->getMessage()]);
            toast('An error occurred. Please try again later', 'error');
        }

        return $departments;
    }

    public function getFilteredExceptions($employeeId)
    {
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

        // dd($employeeGroups);

        // Map batch IDs to their corresponding activity group IDs
        $batchGroupMap = collect($batches)
            ->pluck('activityGroupId', 'id');

        // Filter exceptions
        $filteredExceptions = collect($exceptions)->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $batchGroupMap) {
            $groupId = $batchGroupMap[$exception->exceptionBatchId] ?? null;
            return $validBatches->has($exception->exceptionBatchId) &&
                $validGroups->has($groupId) &&
                $employeeGroups->contains($groupId);
        });

        // dd($filteredExceptions->values()->all());

        return $filteredExceptions->values()->all();
    }

    public static function getLoggedInUserInformation()
    {

        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5124/HRMS/Employee/GetEmployeeInformation');

            if ($response->successful()) {
                $employee = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $employee = [];
                Log::warning('Employee Information  API returned 404 Not Found');
                toast('Employee Information  data not found', 'warning');
            } else {
                $employee = [];
                Log::error('Employee Information  API request failed', ['status' => $response->status()]);
                toast('Error fetching Employee Information  data', 'error');
            }
        } catch (\Exception $e) {
            $employee = [];
            Log::error('Error fetching Employee Information  data', ['error' => $e->getMessage()]);
            toast('Error fetching Employee Information data', 'error');
        }

        return $employee;
    }
}
