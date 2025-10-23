<?php

namespace App\Http\Controllers;

use App\Services\AuditorApiService;
use App\Http\Traits\HandlesApiErrors;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Controllers\ExceptionManipulationController;

class AuditCreateController extends Controller
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
    public function index()
    {
        if (!$this->hasValidApiToken()) {
            return redirect()->back()->with('toast_warning', 'Session Expired, Kindly Login Again');
        }

        $employeeUnitId = ExceptionManipulationController::getLoggedInUserInformation()->department->id;


        $departments = ExceptionManipulationController::departmentData();

        $batchData = BatchController::getBatches();
        $user = ExceptionManipulationController::getLoggedInUserInformation();

        $employeeFullName = $user->firstName . ' ' . $user->surname;
        $employeeDepartment = $user->department->name;

        $batches = collect($batchData)->filter(function ($batch) use ($employeeDepartment) {
            return isset($batch->createdAt) && $batch->active == true && $batch->status == 'OPEN' && ($employeeDepartment ===  $batch->auditorUnitName);
        });

        $employeeGroupsData = GroupController::getEmployeeGroups();
        $groups = collect($employeeGroupsData->activityGroups ?? [])->values();

        $processTypes = ProcessTypeController::getProcessTypes();
        $subProcessTypes = collect(ProcessTypeController::getSubProcessTypes());

        // Group sub-process types by process type ID using Laravel's groupBy
        $groupedSubProcessTypes = $subProcessTypes->groupBy('processTypeId')->toArray();

        return view('exception-setup.audit-create', [
            'departments' => $departments,
            'batches' => $batches,
            'groups' => $groups,
            'processTypes' => $processTypes,
            'subProcessTypes' => $subProcessTypes,
            'groupedSubProcessTypes' => $groupedSubProcessTypes
        ]);
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
            'activityGroupId' => 'required|integer',
            'occurrenceDate' => 'required|date',
            'exceptions' => 'required|array|min:1',
            'exceptions.*.exceptionTitle' => 'required|string|max:255',
            'exceptions.*.exception' => 'required|string',
            'exceptions.*.subProcessTypeId' => 'required|integer',
            'exceptions.*.files.*' => 'nullable|file|max:10240', // Max 10MB per file
        ]);



        // Prepare the data structure expected by the API
        $data = [
            'processTypeId' => $request->input('processTypeId'),
            'departmentId' => $request->input('departmentId'),
            'exceptionBatchId' => $request->input('exceptionBatchId'),
            'activityGroupId' => $request->input('activityGroupId'),
            'occurrenceDate' => $request->input('occurrenceDate'),
            'exceptions' => []
        ];



        // Format each exception
        foreach ($request->input('exceptions') as $index => $exception) {
            $exceptionData = [
                'exceptionTitle' => $exception['exceptionTitle'],
                'exception' => $exception['exception'],
                'status' => 'PENDING', // Default status
                'processTypeId' => $request->input('processTypeId'),
                'subProcessTypeId' => $exception['subProcessTypeId'],
                'departmentId' => $request->input('departmentId'),
                'exceptionBatchId' => $request->input('exceptionBatchId'),
                'activityGroupId' => $request->input('activityGroupId'),
                'fileUploads' => []
            ];

            // Handle file uploads for this exception
            if ($request->hasFile("exceptions.{$index}.files")) {
                $files = $request->file("exceptions.{$index}.files");

                foreach ($files as $file) {
                    if ($file->isValid()) {
                        // Read file content and encode to base64
                        $fileContent = file_get_contents($file->getRealPath());
                        $base64Content = base64_encode($fileContent);

                        $exceptionData['fileUploads'][] = [
                            'fileName' => $file->getClientOriginalName(),
                            'fileData' => $base64Content,
                            'fileDescription' => $exception['fileDescription'] ?? $file->getClientOriginalName()
                        ];
                    }
                }
            }

            $data['exceptions'][] = $exceptionData;
        }

        // dd($data);

        try {
            $response = $this->apiService->post(
                $this->apiService->getEndpoint('exception_batch_request'),
                $data,
                $this->getApiToken()
            );

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
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('toast_error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function list()
    {
        // Validate session token
        if (!$this->hasValidApiToken()) {
            return $this->redirectToLoginIfNoToken('Session expired, please login to continue');
        }

        try {
            // Fetch data from API
            $response = $this->apiService->get(
                $this->apiService->getEndpoint('pending_batch_exceptions'),
                $this->getApiToken()
            );

            // Handle API failure
            if (!$response->successful()) {
                Log::error('API request failed', ['status' => $response->status()]);
                return view('exception-setup.auditor-status-list', [
                    'pendingExceptions' => [],
                    'isEmpty' => true
                ]);
            }

            $exceptions = $response->object();
            if (!is_array($exceptions)) {
                return view('exception-setup.auditor-status-list', [
                    'pendingExceptions' => [],
                    'isEmpty' => true
                ]);
            }

            // Get user and lookup data
            $loggedInUser = ExceptionManipulationController::getLoggedInUserInformation();
            $employeeId = $loggedInUser->id;
            $employeeRoleId = $loggedInUser->empRoleId;
            $topManagers = [1, 2, 4]; // MD, Head of IA, Head of IC&C

            // Get valid batches and groups
            $validBatches = collect(BatchController::getBatches())
                ->filter(fn($batch) => $batch->active && $batch->status === 'OPEN')
                ->keyBy('id');

            $validGroups = collect(GroupController::getActivityGroups())
                ->filter(fn($group) => $group->active)
                ->keyBy('id');

            // Get user's groups
            $employeeGroups = collect(GroupMembersController::getGroupMembers())
                ->where('employeeId', $employeeId)
                ->pluck('activityGroupId')
                ->unique();

            // Process exceptions
            $pendingExceptions = collect($exceptions)
                ->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $topManagers, $employeeRoleId) {
                    // Get the actual group ID from the exception
                    $groupId = $exception->activityGroupId;

                    // Check if batch is valid (active and OPEN)
                    $hasValidBatch = $validBatches->has($exception->exceptionBatchId);

                    // Check if group is valid (active)
                    $hasValidGroup = $validGroups->has($groupId);


                    // Check if status is one of the tracked statuses
                    $hasValidStatus = in_array($exception->status, ['PENDING', 'REVIEW', 'AMENDMENT', 'DECLINED']);

                    // Check access: employee belongs to group OR is a top manager
                    $hasAccess = $employeeGroups->contains($groupId) || in_array($employeeRoleId, $topManagers);

                    return $hasValidBatch && $hasValidGroup && $hasValidStatus && $hasAccess;
                })
                ->map(function ($exception) use ($loggedInUser) {
                    $nestedExceptions = collect($exception->exceptions ?? []);

                    // Count exceptions by status
                    $pendingCount = $nestedExceptions->where('status', 'PENDING')->count();
                    $notResolvedCount = $nestedExceptions->where('status', 'NOT-RESOLVED')->count();
                    $completedCount = $nestedExceptions->where('status', 'RESOLVED')->count();
                    $resolvedCount = $nestedExceptions->where('recommendedStatus', 'RESOLVED')->count();
                    $approvedCount = $nestedExceptions->where('status', 'APPROVED')->count();
                    // Total count of all tracked statuses
                    $totalExceptionCount = $pendingCount + $notResolvedCount + $completedCount + $approvedCount;

                    return [
                        'id' => $exception->id,
                        'status' => $exception->status,
                        'submittedBy' => $exception->submittedBy ?? 'Unknown',
                        'submittedAt' => $exception->submittedAt ?? now()->format('Y-m-d H:i:s'),
                        'groupName' => $exception->activityGroup ?? 'N/A',
                        'department' => $exception->departmentName ?? 'Unknown Department',
                        'pendingCount' => $pendingCount,
                        'notResolvedCount' => $notResolvedCount,
                        'resolvedCount' => $resolvedCount,
                        'completedCount' => $completedCount,
                        'approvedCount' => $approvedCount,
                        'totalExceptionCount' => $totalExceptionCount,
                        'auditorDepartmentId' => $loggedInUser->departmentId,
                    ];
                })
                // Filter to only include records that have at least one exception in any of the tracked statuses
                ->filter(fn($exception) => $exception['totalExceptionCount'] > 0)
                ->values()
                ->all();

            // Always return view - let Blade handle empty state
            return view('exception-setup.auditor-status-list', [
                'pendingExceptions' => $pendingExceptions,
                'isEmpty' => empty($pendingExceptions)
            ]);
        } catch (\Exception $e) {
            Log::critical('Error in auditeeExceptionList', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return empty view instead of redirect
            return view('exception-setup.auditor-status-list', [
                'pendingExceptions' => [],
                'isEmpty' => true
            ]);
        }
    }


    public function viewExceptionStatus(Request $request, $exceptionId, $exceptionStatus)
    {

        // dd($exceptionId, $exceptionStatus);

        if (!$this->hasValidApiToken()) {
            return $this->redirectToLoginIfNoToken('Session expired, please login to continue');
        }

        $departments = ExceptionManipulationController::departmentData() ?? [];
        $batches = BatchController::getBatches() ?? [];
        $processTypes = ProcessTypeController::getProcessTypes() ?? [];
        $subProcessTypes = collect(ProcessTypeController::getSubProcessTypes() ?? []);
        $groupedSubProcessTypes = $subProcessTypes
            ->filter(function ($item) {
                return isset($item->processTypeId);
            })
            ->groupBy('processTypeId')
            ->toArray();

        $response = $this->apiService->get(
            $this->apiService->getEndpoint('batch_exception') . '/' . $exceptionId,
            $this->getApiToken()
        );
        $exceptions = $response->object();

        if (!$response->successful()) {
            Log::error('Failed to fetch batch exception', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return redirect()->back()
                ->with('toast_error', 'Failed to fetch batch exception. Please try again later.' . $response->body());
        }

        return view('exception-setup.auditor-status-view', [
            'exceptions' => $exceptions,
            'batchStatus' => $exceptionStatus,
            'departments' => $departments,
            'batches' => $batches,
            'processTypes' => $processTypes,
            'groupedSubProcessTypes' => $groupedSubProcessTypes,

        ]);
    }
}
