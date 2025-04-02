<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\GroupController;

class BatchController extends Controller
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

        $all_groups = collect(GroupController::getActivityGroups());

        $groups = collect($all_groups)->filter(fn($group) => $group->active == true); //active groups

        $units = UnitController::getAuditUnitData();

        $activeGroups = $groups->filter(function ($group) {
            return $group->active == true;
        });

        $batches = self::getBatches();
        $sortedBatches = collect($batches)->sortByDesc('createdAt');

        $batchData = ExceptionController::paginate($sortedBatches, 15, $request);

        $employeeData = ExceptionController::getLoggedInUserInformation();

        $employeeFullName = $employeeData->firstName .' '. $employeeData->surname;

        // dd($employeeFullName);


        return view('batch-setup.index', compact('activeGroups', 'units', 'batchData', 'employeeFullName'));
    }

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
        $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer',
            'active' => 'required|integer',
            'status' => 'required|string|max:7',
            'auditorUnitId' => 'required|integer',
            'activityGroupId' => 'required|integer',
        ]);

        $access_token = session('api_token');

        $data = [
            'name' => $request->input('name'),
            'year' => $request->input('year'),
            'active' => $request->input('active') == 1 ? true : false,
            'status' => $request->input('status'),
            'auditorUnitId' => $request->input('auditorUnitId'),
            'activityGroupId' => $request->input('activityGroupId'),
        ];

        try {
            $response = Http::withToken($access_token)->post('http://192.168.1.200:5126/Auditor/ExceptionBatch', $data);

            if ($response->successful()) {

                return redirect()->route('batch')->with('toast_success', 'Batch created successfully');
            } else {
                // Log the error response
                Log::error('Failed to create batch', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to create batch');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while creating batch', [
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
    public function edit($id)
    {
        $auditUnits = UnitController::getAuditUnitData();
        $activityGroups = GroupController::getActivityGroups();

        try {
            // Make the GET request to the external API
            $response = $this->getABatch($id);

            // Check the response status and return appropriate response
            if (!empty($response)) {
                $batch_data = $response;

                // dd($batch_data);

                return view('batch-setup.edit', compact('batch_data', 'auditUnits', 'activityGroups'));
            } else {

                return redirect()->back()->with('toast_error', 'Batch does not exist');
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while fetching group', [
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
            'name' => 'required|string|max:255',
            'year' => 'required|integer',
            'active' => 'required|integer',
            'status' => 'required|string|max:7',
            'auditorUnitId' => 'required|integer',
            'activityGroupId' => 'required|integer',
        ]);

        $access_token = session('api_token');

        $data = [
            'id' => $id,
            'name' => $request->input('name'),
            'year' => $request->input('year'),
            'active' => $request->input('active') == 1 ? true : false,
            'status' => $request->input('status'),
            'auditorUnitId' => $request->input('auditorUnitId'),
            'activityGroupId' => $request->input('activityGroupId'),
        ];

        try {
            $response = Http::withToken($access_token)->put('http://192.168.1.200:5126/Auditor/ExceptionBatch/', $data);

            if ($response->successful()) {
                return redirect()->route('batch')->with('toast_success', 'Batch updated successfully');
            } else {
                // Log the error response
                Log::error('Failed to update batch', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to update batch');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while updating batch', [
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
        $accessToken = session('api_token'); // Replace with your actual access token

        try {
            // Make the DELETE request to the external API
            $response = Http::withToken($accessToken)
                ->delete("http://192.168.1.200:5126/Auditor/ExceptionBatch/{$id}");

            // Check the response status and return appropriate response
            if ($response->successful()) {
                return redirect()->route('batch')->with('toast_success', 'Batch deleted successfully');
            } else {
                // Log the error response
                Log::error('Failed to delete Batch', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to delete Batch');
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while deleting Batch', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }

    public static function getBatches()
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/ExceptionBatch');

            if ($response->successful()) {
                $batches = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $batches = [];
                Log::warning('Exception Batch API returned 404 Not Found');
                toast('Exception batch data not found', 'warning');
            } else {
                $batches = [];
                Log::error('Exception Batch API request failed', ['status' => $response->status()]);
                toast('Error fetching exception batch data', 'error');
            }
        } catch (\Exception $e) {
            $batches = [];
            Log::error('Error fetching exception batch data', ['error' => $e->getMessage()]);
            toast('Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>', 'error');
        }

        return $batches;
    }

    public static function getABatch($id)
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/ExceptionBatch/'. $id);

            if ($response->successful()) {
                $batch = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $batch = [];
                Log::warning('The Exception Batch API returned 404 Not Found');
                toast('The Exception batch data not found', 'warning');
            } else {
                $batch = [];
                Log::error('The Exception Batch API request failed', ['status' => $response->status()]);
                toast('Error fetching exception batch data', 'error');
            }
        } catch (\Exception $e) {
            $batch = [];
            Log::error('Error fetching The Exception batch data', ['error' => $e->getMessage()]);
            toast('Error fetching The Exception data', 'error');
        }

        return $batch;
    }
}
