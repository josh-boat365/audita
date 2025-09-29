<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ExceptionManipulationController extends Controller
{
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
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception Tracker API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            $exceptions = [];
            Log::error('Error fetching exception data', ['error' => $e->getMessage()]);
            toast('Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>', 'error');
        }

        return $exceptions;
    }

    public static function getBatchExceptions()
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/ExceptionTracker/pending-batch-exceptions');

            if ($response->successful()) {
                $exceptions = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $exceptions = [];
                Log::warning('Exception API returned 404 Not Found: pending-batch-exceptions');
                toast('Exception  data not found', 'warning');
            } else {
                $exceptions = [];
                Log::error('Exception API request failed: pending-batch-exceptions', ['status' => $response->status()]);
                toast('Error fetching exception  data', 'error');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception Tracker API: pending-batch-exceptions', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            $exceptions = [];
            Log::error('Error fetching exception data: pending-batch-exceptions', ['error' => $e->getMessage()]);
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
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception tracker API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            $exception = [];
            Log::error('Error fetching The Exception  data', ['error' => $e->getMessage()]);
            toast('Error fetching The Exception data', 'error');
        }

        return $exception;
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
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Department API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            $departments = [];
            Log::error('Error fetching department data', ['error' => $e->getMessage()]);
            toast(
                'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>',
                'error'
            );
        }

        return $departments;
    }

    public static function getFilteredExceptions($employeeId)
    {
        //filtering is based on groups if you don't belong to a group, you don't see an exception
        //you only see exceptions in a particular group you are part of, But top managers can see all exceptions
        //MODIFIED: Now filters only Internal Control exceptions

        $instance = new self();

        // Fetch data from APIs
        $exceptions = $instance->getExceptions(); //same as all reports data
        $batches = BatchController::getBatches();
        $groups = GroupController::getActivityGroups();
        $groupMembers = GroupMembersController::getGroupMembers();

        // Create a lookup map for all batches (keyed by ID) to check audit units
        $batchLookup = collect($batches)->keyBy('id');

        // Get groups where the specified employee belongs
        $employeeGroups = collect($groupMembers)
            ->where('employeeId', $employeeId)
            ->pluck('activityGroupId')
            ->unique();

        // Map batch IDs to their corresponding activity group IDs
        $batchGroupMap = collect($batches)->pluck('activityGroupId', 'id');

        // Get employee role information
        $employeeRoleId = $instance->getLoggedInUserInformation()->empRoleId;

        // Top managers role IDs who can see all Internal Control exceptions
        // 1 - Managing Director
        // 4 - Head of Internal Control & Compliance
        $topManagersForIC = [1, 4];

        // Filter exceptions - Internal Control only
        $filteredExceptions = collect($exceptions)->filter(function ($exception) use ($batchLookup, $employeeGroups, $batchGroupMap, $topManagersForIC, $employeeRoleId) {

            // Only show PENDING exceptions with null recommendedStatus
            if (!($exception->status == 'PENDING' && $exception->recommendedStatus == null)) {
                return false;
            }

            // Get the batch this exception belongs to
            $batch = $batchLookup[$exception->exceptionBatchId] ?? null;
            // dd($batch);
            // Check if batch exists and belongs to Internal Control (auditorUnitId = 2)
            if ($batch->auditorUnitId === 2) {
                return true;
            }

            // Check if batch is active and open
            if (!($batch->active && $batch->status === 'OPEN')) {
                return false;
            }

            // Get the activity group ID for this exception's batch
            $groupId = $batchGroupMap[$exception->exceptionBatchId] ?? null;

            // Check access permissions:
            // 1. Top managers for Internal Control can see all IC exceptions
            // 2. Regular employees can only see exceptions from groups they belong to
            $isTopManager = in_array($employeeRoleId, $topManagersForIC);
            $isMemberOfGroup = $employeeGroups->contains($groupId);

            return $isTopManager || $isMemberOfGroup;
        });

        // dd($filteredExceptions);

        return $filteredExceptions->values()->all();
    }


    public static function getFilteredBatchExceptions($employeeId)
    {
        //filtering is based on groups if you don't belong to a group, you don't see an exception
        //you only see exceptions in a particular group you are part of, But top managers can see all exceptions

        $instance = new self();

        // Fetch data from APIs
        $exceptions = $instance->getBatchExceptions(); //same as all reports data
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

        $employeeRoleId = $instance->getLoggedInUserInformation()->empRoleId;

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



    public function getPendingExceptions($employeeId)
    {

        //filtering is based on groups if you don't belong to a group, you don't see an exception
        //you only see exceptions in a particular group you are part of, But top managers can see all exceptions

        // Fetch data from APIs
        $exceptions = $this->getExceptions();
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

        // Map batch IDs to their corresponding activity group IDs
        $batchGroupMap = collect($batches)
            ->pluck('activityGroupId', 'id');


        $employeeRoleId = $this->getLoggedInUserInformation()->empRoleId;

        // top managers
        // 1 - Managing Director
        // 2 - Head of Internal Audit
        // 4 - Head of Internal Control & Compliance
        $topMangers = [1, 2, 4];

        // Filter exceptions - and include topManagers
        $filteredExceptions = collect($exceptions)->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $batchGroupMap, $topMangers, $employeeRoleId) {
            $groupId = $batchGroupMap[$exception->exceptionBatchId] ?? null;
            return $validBatches->has($exception->exceptionBatchId) &&
                $validGroups->has($groupId) && ($exception->recommendedStatus == 'RESOLVED' && $exception->status == 'PENDING') && $employeeGroups->contains($groupId) || (in_array($employeeRoleId, $topMangers));
        });

        return $filteredExceptions->values()->all();
    }


    public static function getLoggedInUserInformation()
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5124/HRMS/Employee/GetEmployeeInformation');

            if ($response->successful()) {
                return $response->object() ?? [];
            } elseif ($response->status() == 404) {
                Log::warning('Employee Information API returned 404 Not Found');

                toast('Employee Information not found', 'warning');
                return [];
            } else {
                Log::error('Employee Information API request failed', ['status' => $response->status()]);

                toast('Error fetching Employee Information data', 'error');
                return [];
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Employee Information API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            // Handle general exceptions
            Log::error('Error fetching Employee Information data', ['error' => $e->getMessage()]);

            toast('Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>', 'error');
            return [];
        }
    }
}
