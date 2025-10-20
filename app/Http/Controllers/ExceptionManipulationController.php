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
        // Filtering is based on groups - if you don't belong to a group, you don't see an exception
        // You only see exceptions in a particular group you are part of, but top managers can see all exceptions
        // MODIFIED: Now filters only Internal Control exceptions

        $instance = new self();

        // Fetch data from APIs
        $exceptions = $instance->getExceptions(); // Same as all reports data
        $batches = BatchController::getBatches();
        $groups = GroupController::getActivityGroups();
        $groupMembers = GroupMembersController::getGroupMembers();

        // Create a lookup map for all batches (keyed by ID) to check audit units
        $batchLookup = collect($batches)->keyBy('id');

        // Filter active groups and map them by ID
        $validGroups = collect($groups)
            ->filter(fn($group) => $group->active)
            ->keyBy('id');

        // Get groups where the specified employee belongs
        $employeeGroups = collect($groupMembers)
            ->where('employeeId', $employeeId)
            ->pluck('activityGroupId')
            ->unique();

        // Get employee role information
        $employeeRoleId = $instance->getLoggedInUserInformation()->empRoleId;

        // Top managers role IDs who can see all Internal Control exceptions
        // 1 - Managing Director
        // 4 - Head of Internal Control & Compliance
        $topManagersForIC = [1, 4];

        // Filter exceptions - Internal Control only
        $filteredExceptions = collect($exceptions)->filter(function ($exception) use (
            $batchLookup,
            $validGroups,
            $employeeGroups,
            $topManagersForIC,
            $employeeRoleId
        ) {
            // Only show PENDING exceptions with null recommendedStatus
            if (!($exception->status == 'PENDING' && $exception->recommendedStatus == null)) {
                return false;
            }

            // Get the batch this exception belongs to
            $batch = $batchLookup[$exception->exceptionBatchId] ?? null;

            // Check if batch exists
            if (!$batch) {
                return false;
            }

            // Check if batch belongs to Internal Control (auditorUnitId = 2)
            if ($batch->auditorUnitId !== 2) {
                return false;
            }

            // Check if batch is active and open
            if (!($batch->active && $batch->status === 'OPEN')) {
                return false;
            }

            // Check if exception's activity group is valid (active)
            if (!$validGroups->has($exception->activityGroupId)) {
                return false;
            }

            // Check access permissions:
            // 1. Top managers for Internal Control can see all IC exceptions
            // 2. Regular employees can only see exceptions from groups they belong to
            $isTopManager = in_array($employeeRoleId, $topManagersForIC);
            $isMemberOfGroup = $employeeGroups->contains($exception->activityGroupId);

            return $isTopManager || $isMemberOfGroup;
        })->values()->all();

        return $filteredExceptions;
    }


    public static function getFilteredBatchExceptions($employeeId)
    {
        // Filtering is based on groups - if you don't belong to a group, you don't see an exception
        // You only see exceptions in a particular group you are part of, but top managers can see all exceptions

        $instance = new self();

        // Fetch data from APIs
        $exceptions = $instance->getBatchExceptions(); // Same as all reports data
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

        // Get employee role
        $employeeRoleId = $instance->getLoggedInUserInformation()->empRoleId;

        // Top managers
        // 1 - Managing Director
        // 2 - Head of Internal Audit
        // 4 - Head of Internal Control & Compliance
        $topManagers = [1, 2, 4];

        // Filter exceptions - based on activity groups and include top managers
        $filteredExceptions = collect($exceptions)->filter(function ($exception) use (
            $validBatches,
            $validGroups,
            $employeeGroups,
            $topManagers,
            $employeeRoleId
        ) {
            // Check if exception's activity group is valid (active)
            $hasValidGroup = $validGroups->has($exception->activityGroupId);

            // Check if exception's batch is valid (active and OPEN)
            $hasValidBatch = $validBatches->has($exception->exceptionBatchId);

            // Check if exception is pending without recommendation
            $isPendingWithoutRecommendation = $exception->status == 'PENDING' && $exception->recommendedStatus == null;

            // Check if employee belongs to the exception's group
            $belongsToGroup = $employeeGroups->contains($exception->activityGroupId);

            // Check if employee is a top manager
            $isTopManager = in_array($employeeRoleId, $topManagers);

            // Return exceptions that meet all conditions OR if user is a top manager
            return $hasValidGroup && $hasValidBatch && $isPendingWithoutRecommendation && ($belongsToGroup || $isTopManager);
        })->values()->all();

        return $filteredExceptions;
    }



    public function getPendingExceptions($employeeId)
    {
        // Filtering is based on groups - if you don't belong to a group, you don't see an exception
        // You only see exceptions in a particular group you are part of, but top managers can see all exceptions

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

        // Get employee role
        $employeeRoleId = $this->getLoggedInUserInformation()->empRoleId;

        // Top managers
        // 1 - Managing Director
        // 2 - Head of Internal Audit
        // 4 - Head of Internal Control & Compliance
        $topManagers = [1, 2, 4];

        // Filter exceptions - based on activity groups and include top managers
        $filteredExceptions = collect($exceptions)->filter(function ($exception) use (
            $validBatches,
            $validGroups,
            $employeeGroups,
            $topManagers,
            $employeeRoleId
        ) {
            // Check if exception's activity group is valid (active)
            $hasValidGroup = $validGroups->has($exception->activityGroupId);

            // Check if exception's batch is valid (active and OPEN)
            $hasValidBatch = $validBatches->has($exception->exceptionBatchId);

            // Check if exception is pending resolution
            $isPendingResolution = $exception->recommendedStatus == 'RESOLVED' && $exception->status == 'PENDING';

            // Check if employee belongs to the exception's group
            $belongsToGroup = $employeeGroups->contains($exception->activityGroupId);

            // Check if employee is a top manager
            $isTopManager = in_array($employeeRoleId, $topManagers);

            // Return exceptions that meet all conditions OR if user is a top manager
            return $hasValidGroup && $hasValidBatch && $isPendingResolution && ($belongsToGroup || $isTopManager);
        })->values()->all();

        return $filteredExceptions;
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
