<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\GroupController;
use App\Services\AuditorApiService;
use App\Http\Traits\HandlesApiErrors;

class BatchController extends Controller
{
    use HandlesApiErrors;

    protected AuditorApiService $apiService;

    public function __construct(AuditorApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$this->hasValidApiToken()) {
            return $this->redirectToLoginIfNoToken();
        }

        $all_groups = collect(GroupController::getActivityGroups());

        $groups = collect($all_groups)->filter(fn($group) => $group->active == true); //active groups

        $units = UnitController::getAuditUnitData();

        $activeGroups = $groups->filter(function ($group) {
            return $group->active == true;
        });

        $batches = self::getBatches();

        $employeeData = ExceptionManipulationController::getLoggedInUserInformation();

        $employeeFullName = $employeeData->firstName . ' ' . $employeeData->surname;
        $employeeDepartment = $employeeData->department->name;

        $sortedBatches = collect($batches)->filter(function ($batch) use ($employeeDepartment) {
            return isset($batch->createdAt) && ($employeeDepartment ===  $batch->auditorUnitName);
        })
            ->sortByDesc('createdAt');

        $batchData = ExceptionController::paginate($sortedBatches, 15, $request);

        return view('batch-setup.index', compact('activeGroups', 'units', 'batchData', 'employeeFullName', 'employeeDepartment'));
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
            // 'activityGroupId' => 'required|integer',
        ]);

        $access_token = session('api_token');

        $data = [
            'name' => $request->input('name'),
            'year' => $request->input('year'),
            'active' => $request->input('active') == 1 ? true : false,
            'status' => $request->input('status'),
            'auditorUnitId' => $request->input('auditorUnitId'),
            // 'activityGroupId' => $request->input('activityGroupId'),
        ];

        try {
            $response = $this->apiService->post(
                $this->apiService->getEndpoint('exception_batch'),
                $data,
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Batch created successfully',
                'batch',
                'Create batch'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'creating batch', ['data' => $data]);
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
        // $activityGroups = GroupController::getActivityGroups();

        try {
            // Make the GET request to the external API
            $response = $this->getABatch($id);

            // Check the response status and return appropriate response
            if (!empty($response)) {
                $batch_data = $response;

                // dd($batch_data);

                return view('batch-setup.edit', compact('batch_data', 'auditUnits'));
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
            // 'activityGroupId' => 'required|integer',
        ]);

        $data = [
            'id' => $id,
            'name' => $request->input('name'),
            'year' => $request->input('year'),
            'active' => $request->input('active') == 1 ? true : false,
            'status' => $request->input('status'),
            'auditorUnitId' => $request->input('auditorUnitId'),
            // 'activityGroupId' => $request->input('activityGroupId'),
        ];

        try {
            $response = $this->apiService->put(
                $this->apiService->getEndpoint('exception_batch'),
                $data,
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Batch updated successfully',
                'batch',
                'Update batch'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'updating batch', ['data' => $data]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $response = $this->apiService->delete(
                "{$this->apiService->getEndpoint('exception_batch')}/{$id}",
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Batch deleted successfully',
                'batch',
                'Delete batch'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'deleting batch', ['batch_id' => $id]);
        }
    }

    public static function getBatches()
    {
        $service = app(AuditorApiService::class);

        try {
            $response = $service->get(
                $service->getEndpoint('exception_batch'),
                session('api_token')
            );

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
        $service = app(AuditorApiService::class);

        try {
            $response = $service->get(
                "{$service->getEndpoint('exception_batch')}/{$id}",
                session('api_token')
            );

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
