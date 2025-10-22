<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FilterExceptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public static function handleException(object $data, array $statuses)
    {

        $exceptionData = $data;
        $batches = BatchController::getBatches();
        $groups = GroupController::getActivityGroups();
        $groupMembers = GroupMembersController::getGroupMembers();
        $employeeId = ExceptionManipulationController::getLoggedInUserInformation()->id;


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


        $employeeRoleId = ExceptionManipulationController::getLoggedInUserInformation()->empRoleId;

        // top managers
        // 1 - Managing Director
        // 2 - Head of Internal Audit
        // 4 - Head of Internal Control & Compliance
        $topManagers = [1, 2, 4];

        // Filter exceptions - and include top managers
        $filteredExceptions = collect($exceptionData)->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $topManagers, $employeeRoleId, $statuses) {
            // Get the actual group ID from the exception
            $groupId = $exception->activityGroupId;

            // Check if batch is valid (active and OPEN)
            $hasValidBatch = $validBatches->has($exception->exceptionBatchId);

            // Check if group is valid (active)
            $hasValidGroup = $validGroups->has($groupId);

            // Check if status is not DECLINED
            $hasValidStatus = !in_array($exception->status, $statuses);

            // Check access: employee belongs to group OR is a top manager
            $hasAccess = $employeeGroups->contains($groupId) || in_array($employeeRoleId, $topManagers);

            return $hasValidBatch && $hasValidGroup && $hasValidStatus && $hasAccess;
        });

        return $filteredExceptions->values()->all();
    }


    public static function handleBatchException(object $data, array $statuses)
    {

    }


}
