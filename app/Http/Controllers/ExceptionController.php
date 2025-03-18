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

        $exceptions = $this->getExceptions();

        return view('exception-setup.index', compact('exceptions'));
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

    public function edit(string $id)
    {
        $batches = BatchController::getBatches();
        $departments = $this->departmentData();
        $processTypes = ProcessTypeController::getProcessTypes();
        $riskRates = RiskRateController::getRiskRates();

        try {
            // Make the GET request to the external API
            $response = $this->getAnException($id);

            //get files for the exception
            $encryptedFiles = $this->getExceptionFiles($id);

            //decode file data
            $files = array_map(function ($file) {
                return [
                    'id' => $file->id,
                    'fileName' => $file->fileName,
                    'fileData' => base64_decode($file->fileData),
                    'uploadDate' => $file->uploadDate,
                ];
            }, $encryptedFiles);

            //get exception comments
            $comments = $this->getExceptionComments($id);


            // Check the response status and return appropriate response
            if (!empty($response)) {
                $exception = $response;

                return view('exception-setup.edit', compact(
                    'exception',
                    'batches',
                    'departments',
                    'processTypes',
                    'riskRates',
                    'files',
                    'comments'
                ));
            } else {

                return redirect()->back()->with('toast_error', 'Exception does not exist');
            }
        } catch (\Exception $e) {
            // Log the exception
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

    public function exceptionFileUpload(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'file' => 'required|mimes:pdf,doc,docx,png,jpeg,jpg,txt|max:5048',
        ]);

        $access_token = session('api_token');

        if ($request->file('file')) {
            $file = $request->file('file');
            $fileContent = file_get_contents($file);
            $base64File = base64_encode($fileContent);

            $data = [
                'exceptionTrackerId' => $id,
                'fileName' => $file->getClientOriginalName(),
                'fileData' => $base64File,
            ];

            try {
                $response = Http::withToken($access_token)->post('http://192.168.1.200:5126/Auditor/ExceptionFileUpload', $data);

                if ($response->successful()) {
                    return redirect()->back()->with('toast_success', 'Exception File uploaded successfully');
                } else {
                    // Log the error response
                    Log::error('Failed to upload file', [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return redirect()->back()->with('toast_error', 'Sorry, failed to upload file');
                }
            } catch (\Exception $e) {
                Log::error('Exception occurred while uploading file', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
            }
        }

        return redirect()->back()->with('toast_error', 'No file uploaded');
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
                return redirect()->back()->with('toast_error', 'Sorry, failed to delete Exception');
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

    public static function getExceptionFiles($exceptionId)
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/ExceptionFileUpload');

            if ($response->successful()) {
                $api_response = $response->object() ?? [];

                $files = collect($api_response)->filter(fn($file) => $file->exceptionTrackerId == $exceptionId)->all() ?? [];
            } elseif ($response->status() == 404) {
                $files = [];
                Log::warning('Exception files API returned 404 Not Found');
                toast('Exception files data not found', 'warning');
            } else {
                $files = [];
                Log::error('Exception files API request failed', ['status' => $response->status()]);
                toast('Error fetching exception files data', 'error');
            }
        } catch (\Exception $e) {
            Log::error('Error fetching exception files', ['error' => $e->getMessage()]);
            toast('An error occurred. Please try again later', 'error');
        }

        return $files;
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
}
