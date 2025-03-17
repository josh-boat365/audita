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
            'proposeResolutionDate' =>  Carbon::createFromFormat('d/m/Y', $request->input('proposeResolutionDate'))->format('Y-m-d') ?? null,
            'resolutionDate' =>  Carbon::createFromFormat('d/m/Y', $request->input('resolutionDate'))->format('Y-m-d') ?? null,
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

            // Check the response status and return appropriate response
            if (!empty($response)) {
                $exception = $response;

                return view('exception-setup.edit', compact('exception', 'batches', 'departments', 'processTypes', 'riskRates'));
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

    // public function exceptionFileUpload($id)
    // {
    //     $access_token = session('api_token');

    //     try {
    //         $response = Http::withToken($access_token)->post('http://192.168.1.200:5126/Auditor/ExceptionFileUpload');
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        // Get the access token from the session
        $accessToken = session('api_token'); // Replace with your actual access token

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
