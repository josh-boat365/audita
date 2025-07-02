<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Collection;

class AuditCreateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $access_token = session('api_token');
        if (empty($access_token)) {
            return redirect()->back()->with('toast_warning', 'Session Expired, Kindly Login Again');
        }

        $departments = ExceptionController::departmentData();
        $batches = BatchController::getBatches();
        $processTypes = ProcessTypeController::getProcessTypes();
        $subProcessTypes = collect(ProcessTypeController::getSubProcessTypes());

        // Group sub-process types by process type ID using Laravel's groupBy
        $groupedSubProcessTypes = $subProcessTypes->groupBy('processTypeId')->toArray();

        return view('exception-setup.audit-create', [
            'departments' => $departments,
            'batches' => $batches,
            'processTypes' => $processTypes,
            'subProcessTypes' => $subProcessTypes,
            'groupedSubProcessTypes' => $groupedSubProcessTypes
        ]);
    }

    /**
     * Convert data to a Laravel Collection ensuring consistent structure
     *
     * @param mixed $data
     * @return Collection
     */




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'processTypeId' => 'required|integer',
            'departmentId' => 'required|integer',
            'exceptionBatchId' => 'required|integer',
            'occurrenceDate' => 'required|date',
            'exceptions' => 'required|array|min:1',
            'exceptions.*.exceptionTitle' => 'required|string|max:255',
            'exceptions.*.exception' => 'required|string',
            'exceptions.*.subProcessTypeId' => 'required|integer',
            // 'exceptions.*.status' => 'required|string|in:Open,In Progress,Resolved',
        ]);

        $access_token = session('api_token');

        // Prepare the data structure expected by the API
        $data = [
            'processTypeId' => $request->input('processTypeId'),
            'departmentId' => $request->input('departmentId'),
            'exceptionBatchId' => $request->input('exceptionBatchId'),
            'occurrenceDate' => $request->input('occurrenceDate'),
            'exceptions' => []
        ];

        // Format each exception
        foreach ($request->input('exceptions') as $exception) {
            $data['exceptions'][] = [
                'exceptionTitle' => $exception['exceptionTitle'],
                'exception' => $exception['exception'],
                'status' => 'PENDING', // Default status,
                'subProcessTypeId' => $exception['subProcessTypeId'],
                'departmentId' => $request->input('departmentId'),
                'exceptionBatchId' => $request->input('exceptionBatchId')
            ];
        }

        try {
            $response = Http::withToken($access_token)
                ->post('http://192.168.1.200:5126/Auditor/ExceptionTracker/batch-request', $data);

            if ($response->successful()) {
                return redirect()->back()->with('toast_success', 'Bulk exceptions created successfully');
            } else {
                // Log the error response
                Log::error('Failed to create bulk exceptions', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'request_data' => $data
                ]);

                return redirect()->back()
                    ->withInput()
                    ->with('toast_error', 'Failed to create exceptions: ' . $response->json('message', 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while creating bulk exceptions', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $data
            ]);

            return redirect()->back()
                ->withInput()
                ->with('toast_error', 'Something went wrong: ' . $e->getMessage());
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
