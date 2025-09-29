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

        $departments = ExceptionManipulationController::departmentData();
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
    public function list()
    {
        // Validate session token
        $access_token = session('api_token');
        if (empty($access_token)) {
            Log::warning('Session token missing in auditeeExceptionList');
            return redirect()->route('login')
                ->with('toast_warning', 'Session expired, please login to continue');
        }

        try {
            // Fetch data from API
            $response = Http::withToken($access_token)
                ->timeout(30)
                ->retry(3, 100)
                ->get('http://192.168.1.200:5126/Auditor/ExceptionTracker/pending-batch-exceptions');

            // Handle API failure
            if (!$response->successful()) {
                Log::error('API request failed', ['status' => $response->status()]);
                return view('exception-setup.auditor-status-list', [
                    'pendingExceptions' => [],
                    'isEmpty' => true
                ]);
            }

            $exceptions = $response->json();
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

            // Get batch-group mapping
            $batchGroupMap = collect(BatchController::getBatches())
                ->pluck('activityGroupId', 'id');

            // Process exceptions
            $pendingExceptions = collect($exceptions)
                ->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $batchGroupMap, $topManagers, $employeeRoleId) {
                    $batchId = $exception['exceptionBatchId'] ?? null;
                    $groupId = $batchGroupMap[$batchId] ?? null;

                    return $validBatches->has($batchId) &&
                        $validGroups->has($groupId) &&
                        in_array($exception['status'], ['PENDING', 'REVIEW', 'AMENDMENT', 'DECLINED']) &&
                        (in_array($employeeRoleId, $topManagers) || $employeeGroups->contains($groupId));
                })
                ->map(function ($exception) use ($loggedInUser) {
                    $nestedExceptions = collect($exception['exceptions'] ?? []);

                    // Count exceptions by status
                    $pendingCount = $nestedExceptions->where('status', 'PENDING')->count();
                    $notResolvedCount = $nestedExceptions->where('status', 'NOT-RESOLVED')->count();
                    $completedCount = $nestedExceptions->where('status', 'RESOLVED')->count();
                    $resolvedCount = $nestedExceptions->where('recommendedStatus', 'RESOLVED')->count();
                    $approvedCount = $nestedExceptions->where('status', 'APPROVED')->count();

                    // Total count of all tracked statuses
                    $totalExceptionCount = $pendingCount + $notResolvedCount + $completedCount + $approvedCount;

                    return [
                        'id' => $exception['id'],
                        'status' => $exception['status'],
                        'submittedBy' => $exception['submittedBy'] ?? 'Unknown',
                        'submittedAt' => $exception['submittedAt'] ?? now()->format('Y-m-d H:i:s'),
                        'groupName' => $exception['exceptionBatch']['activityGroupName'] ?? 'N/A',
                        'department' => $exception['departmentName'] ?? 'Unknown Department',
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

        $accessToken = session('api_token');
        if (empty($accessToken)) {
            Log::warning('Session token missing in openBatch');
            return redirect()->route('login')
                ->with('toast_warning', 'Session expired, please login to continue');
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

        $request = HTTP::withToken($accessToken)->get('http://192.168.1.200:5126/Auditor/ExceptionTracker/get-batch-exception/' . $exceptionId);
        $exceptions = $request->object();

        if (!$request->successful()) {
            Log::error('Failed to fetch batch exception', [
                'status' => $request->status(),
                'response' => $request->body()
            ]);
            return redirect()->back()
                ->with('toast_error', 'Failed to fetch batch exception. Please try again later.'. $request->body());
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
