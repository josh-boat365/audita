<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\BatchController;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\ExceptionManipulationController;
use App\Services\AuditorApiService;
use App\Http\Traits\HandlesApiErrors;

class ExceptionController extends Controller
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

        $employeeId = ExceptionManipulationController::getLoggedInUserInformation()->id;
        $employeeDepartmentId = ExceptionManipulationController::getLoggedInUserInformation()->departmentId;
        $filteredExceptions = ExceptionManipulationController::getFilteredExceptions($employeeId);
        $sortDescending = collect($filteredExceptions)->sortByDesc('createdAt');

        $exceptions = $this->paginate($sortDescending, 15, $request);

        return view('exception-setup.index', compact('exceptions', 'employeeId', 'employeeDepartmentId'));
    }


    public function pendingExceptions(Request $request)
    {
        if (!$this->hasValidApiToken()) {
            return $this->redirectToLoginIfNoToken();
        }

        $employeeId = ExceptionManipulationController::getLoggedInUserInformation()->id;
        $instance = new ExceptionManipulationController();
        $pendingExceptions = $instance->getPendingExceptions($employeeId);
        $sortDescending = collect($pendingExceptions)->sortByDesc('createdAt');

        $exceptions = $this->paginate($sortDescending, 15, $request);

        // Store exception count in session
        session(['pending_exception_count' => $exceptions->count()]);

        return view('exception-setup.pending', compact('exceptions', 'employeeId'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = ExceptionManipulationController::departmentData();
        $batchData = BatchController::getBatches();
        $employeeData = ExceptionManipulationController::getLoggedInUserInformation();

        $employeeFullName = $employeeData->firstName . ' ' . $employeeData->surname;
        $employeeDepartment = $employeeData->department->name;
        // dd($batches);
        $batches = collect($batchData)->filter(function ($batch) use ($employeeDepartment) {
            return isset($batch->createdAt) && ($employeeDepartment ===  $batch->auditorUnitName);
        })
            ->sortByDesc('createdAt');
        $employeeGroupsData = GroupController::getEmployeeGroups();
        $groups = collect($employeeGroupsData->activityGroups ?? [])->values();
        $processTypes = ProcessTypeController::getProcessTypes();
        $riskRates = RiskRateController::getRiskRates();

        return view('exception-setup.create', compact('departments', 'groups', 'batches', 'processTypes', 'riskRates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'exceptionTitle' => 'required|string',
            'exception' => 'required|string',
            'rootCause' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:8',
            'occurrenceDate' => 'required|date_format:Y-m-d',
            'proposeResolutionDate' => 'nullable|date_format:Y-m-d',
            'resolutionDate' => 'nullable|date_format:Y-m-d',
            'processTypeId' => 'required|integer',
            'riskRateId' => 'nullable|integer',
            'recommendation' => 'nullable|integer',
            'riskAnalysis' => 'nullable|integer',
            'departmentId' => 'required|integer',
            'exceptionBatchId' => 'required|integer',
            'activityGroupId' => 'required|integer',
        ]);
        // dd($request);
        $access_token = session('api_token');

        $data = [
            'exceptionTitle' => $request->input('exceptionTitle'),
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
            'recommendation' => $request->input('recommendation'),
            'riskAnalysis' => $request->input('riskAnalysis'),
            'departmentId' => $request->input('departmentId'),
            'exceptionBatchId' => $request->input('exceptionBatchId'),
            'activityGroupId' => $request->input('activityGroupId'),

        ];

        try {
            $response = $this->apiService->post(
                $this->apiService->getEndpoint('exception_tracker'),
                $data,
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Exception created successfully',
                'exception.list',
                'Create exception'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'creating exception', [
                'data' => $data
            ]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */

    public function edit($id)
    {
        try {
            // Get all necessary data

            $departments = ExceptionManipulationController::departmentData();
            $processTypes = ProcessTypeController::getProcessTypes();
            $riskRates = RiskRateController::getRiskRates();
            $employeeGroupsData = GroupController::getEmployeeGroups();
            $groups = collect($employeeGroupsData->activityGroups ?? [])->values();
            $groupMembers = GroupMembersController::getGroupMembers();
            $exceptions = ExceptionManipulationController::getExceptions();

            // Get the exception and validate it exists
            $exception = ExceptionManipulationController::getAnException($id);
            if (!$exception) {
                toast('Exception not found', 'error');
                return redirect()->back();
            }

            $batchData = BatchController::getBatches();
            $user = ExceptionManipulationController::getLoggedInUserInformation();

            $employeeFullName = $user->firstName . ' ' . $user->surname;
            $employeeDepartment = $user->department->name;

            $batches = collect($batchData)->filter(function ($batch) use ($employeeDepartment, $exception) {
                return isset($batch->createdAt) && ($employeeDepartment ===  $batch->auditorUnitName) || $batch->id == $exception->exceptionBatchId;
            });


            // dd($exception);

            // Get user information

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
            // dd($groupMembers);

            // Get all auditor IDs in the same group as the exception
            $groupAuditorIds = collect($groupMembers)
                ->where('activityGroupId', $exception->activityGroupId)
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
            'exception' => 'required|string',
            'rootCause' => 'nullable|string',
            'status' => 'nullable|string',
            'statusComment' => 'nullable|string',
            'occurrenceDate' => 'required|date_format:Y-m-d',
            'proposeResolutionDate' => 'nullable|date_format:Y-m-d',
            'resolutionDate' => 'nullable|date_format:Y-m-d',
            'processTypeId' => 'required|integer',
            'subProcessTypeId' => 'nullable|integer',
            'riskRateId' => 'nullable|integer',
            'departmentId' => 'required|integer',
            'exceptionBatchId' => 'required|integer',
            'requestTrackerId' => 'nullable|integer',
            'requestType' => 'nullable|string',  // Added nullable
            'recommendation' => 'nullable|string',
            'riskAnalysis' => 'nullable|string',
        ]);

        // Update batch request check to handle undefined key
        $isBatchRequest = isset($validated['requestType']) && $validated['requestType'] === 'BATCH';

        // Check for authentication
        if (!$this->hasValidApiToken()) {
            return $this->redirectToLoginIfNoToken();
        }

        try {
            // Build update data with CSRF token
            $data = array_merge([
                'id' => $id,
                '_token' => csrf_token(),
            ], $validated);

            // Add requestTrackerId and requestType only for batch requests
            if (!$isBatchRequest) {
                unset($data['requestTrackerId'], $data['requestType']);
            }

            // Initialize variables for batch processing
            $shouldUpdateBatchStatus = false;
            $batchData = null;

            // FIRST: Update the individual exception
            $updateResponse = $this->apiService->put(
                $this->apiService->getEndpoint('exception_tracker'),
                $data,
                $this->getApiToken(),
                true, // use rate limiting
                'exception-update:' . $id
            );

            if (!$updateResponse->successful()) {
                Log::error('Failed to update Exception', [
                    'status' => $updateResponse->status(),
                    'exception_id' => $id,
                    'batch_request' => $isBatchRequest
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to update Exception');
            }

            Log::info('Exception updated successfully', ['exception_id' => $id]);

            // SECOND: Check if we need to update batch status (only for batch requests)
            if ($isBatchRequest && isset($validated['requestTrackerId'])) {
                Log::info('Checking batch status for batch request', [
                    'requestTrackerId' => $validated['requestTrackerId'],
                    'exception_id' => $id
                ]);

                // Get updated batch data AFTER the individual exception was updated
                $batchCheckResponse = $this->apiService->get(
                    "{$this->apiService->getEndpoint('batch_exception')}/{$validated['requestTrackerId']}",
                    $this->getApiToken(),
                    true, // use rate limiting
                    'batch-status-check:' . $validated['requestTrackerId']
                );

                if ($batchCheckResponse->successful()) {
                    $batchData = $batchCheckResponse->object();

                    if (isset($batchData->exceptions)) {
                        $totalExceptions = 0;
                        $resolvedCount = 0;
                        $approvedCount = 0;
                        $notResolvedCount = 0;

                        // Count all exception statuses in the batch
                        foreach ($batchData->exceptions as $exception) {
                            $totalExceptions++;

                            if (isset($exception->status)) {
                                if ($exception->status === 'RESOLVED') {
                                    $resolvedCount++;
                                } elseif ($exception->status === 'APPROVED') {
                                    $approvedCount++;
                                } elseif ($exception->status === 'NOT-RESOLVED') {
                                    $notResolvedCount++;
                                }
                            }
                        }

                        Log::info('Batch status analysis', [
                            'requestTrackerId' => $validated['requestTrackerId'],
                            'totalExceptions' => $totalExceptions,
                            'resolvedCount' => $resolvedCount,
                            'approvedCount' => $approvedCount,
                            'notResolvedCount' => $notResolvedCount
                        ]);

                        // Determine if batch should be marked as RESOLVED
                        // All exceptions must be RESOLVED (no APPROVED or NOT-RESOLVED remaining)
                        $shouldUpdateBatchStatus = ($totalExceptions > 0 && $resolvedCount === $totalExceptions);

                        if ($shouldUpdateBatchStatus) {
                            Log::info('All exceptions in batch are RESOLVED, updating batch status', [
                                'requestTrackerId' => $validated['requestTrackerId'],
                                'totalExceptions' => $totalExceptions,
                                'resolvedCount' => $resolvedCount
                            ]);
                        } else {
                            Log::info('Batch not ready for RESOLVED status', [
                                'requestTrackerId' => $validated['requestTrackerId'],
                                'reason' => $resolvedCount < $totalExceptions ? 'Not all exceptions resolved' : 'No exceptions found'
                            ]);
                        }
                    }
                } else {
                    Log::warning('Failed to fetch batch data for status check', [
                        'requestTrackerId' => $validated['requestTrackerId']
                    ]);
                }
            }

            // THIRD: Update batch status if all exceptions are resolved
            if ($shouldUpdateBatchStatus && isset($validated['requestTrackerId'])) {
                Log::info('Attempting to update batch status to RESOLVED', [
                    'batchExceptionId' => $validated['requestTrackerId']
                ]);

                $batchStatusResponse = $this->apiService->put(
                    $this->apiService->getEndpoint('update_batch_status'),
                    [
                        'batchExceptionId' => $validated['requestTrackerId'],
                        'status' => 'RESOLVED',
                        '_token' => csrf_token()
                    ],
                    $this->getApiToken()
                );

                if ($batchStatusResponse->successful()) {
                    Log::info('Batch status updated to RESOLVED successfully', [
                        'batch_id' => $validated['requestTrackerId'],
                        'resolved_count' => $resolvedCount ?? 'unknown'
                    ]);

                    return redirect()->route('auditor.analysis.exception')
                        ->with('toast_success', 'Exception updated successfully. All batch exceptions have been resolved and batch status updated.');
                } else {
                    Log::error('Exception updated but batch status update failed', [
                        'request_tracker_id' => $validated['requestTrackerId'],
                        'status' => $batchStatusResponse->status(),
                        'response' => $batchStatusResponse->body()
                    ]);

                    // Still return success for the individual exception update
                    return redirect()->back()
                        ->with('toast_warning', 'Exception updated successfully, but failed to update batch status. Please contact support.');
                }
            }

            return redirect()->back()->with('toast_success', 'Exception updated successfully');
        } catch (\Exception $e) {
            return $this->handleApiException($e, 'updating exception', [
                'exception_id' => $id,
                'is_batch_request' => $isBatchRequest ?? false
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */



    public function destroy(Request $request, string $id)
    {
        try {
            $response = $this->apiService->delete(
                "{$this->apiService->getEndpoint('exception_tracker')}/{$id}",
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Exception deleted successfully',
                'exception.list',
                'Delete exception'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'deleting exception', [
                'exception_id' => $id
            ]);
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
