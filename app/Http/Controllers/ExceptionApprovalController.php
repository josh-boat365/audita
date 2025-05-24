<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExceptionApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function exceptionSupList()
    {

        return view('exception-setup.supervisor-approval-list');
    }

    public function showBranchExcepitonListForApproval()
    {

        return view('exception-setup.branch-exception-list-for-approval');
    }

    public function auditeeExceptionList(){

        return view('exception-setup.auditee-exception-list');
    }


    public function auditeeExceptionView(){

        return view('exception-setup.auditee-exception-view');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function exceptionAudList(Request $request)
    {
        $access_token = session('api_token');

        if (empty($access_token)) {
            return redirect()->route('login')->with('toast_warning', 'Session expired, login to access the application');
        }

        $employeeId = ExceptionController::getLoggedInUserInformation()->id;
        $filteredExceptions = $this->getFilteredExceptions($employeeId);
        $sortDescending = collect($filteredExceptions)->sortByDesc('createdAt');

        $exceptions = ExceptionController::paginate($sortDescending, 15, $request);

        return view('exception-setup.supervisor-approval-list', compact('exceptions', 'employeeId'));
    }

    public function supEditException($id)
    {
        try {
            // Get all necessary data
            $batches = BatchController::getBatches();
            $departments = ExceptionController::departmentData();
            $processTypes = ProcessTypeController::getProcessTypes();
            $riskRates = RiskRateController::getRiskRates();
            $groups = GroupController::getActivityGroups();
            $groupMembers = GroupMembersController::getGroupMembers();
            $exceptions = ExceptionController::getExceptions();

            // Get the exception and validate it exists
            $exception = ExceptionController::getAnException($id);
            if (!$exception) {
                toast('Exception not found', 'error');
                return redirect()->back();
            }

            // Get user information
            $user = ExceptionController::getLoggedInUserInformation();
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


            return view('exception-setup.supervisor-approval', compact(
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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

    public function getFilteredExceptions($employeeId)
    {
        //filtering is based on groups if you don't belong to a group, you don't see an exception
        //you only see exceptions in a particular group you are part of, But top managers can see all exceptions

        // Fetch data from APIs
        $exceptions = ExceptionController::getExceptions(); //same as all reports data
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

        $employeeRoleId = ExceptionController::getLoggedInUserInformation()->empRoleId;

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
}
