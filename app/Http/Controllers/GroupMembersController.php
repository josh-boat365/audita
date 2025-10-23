<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AuditorApiService;
use App\Http\Traits\HandlesApiErrors;

class GroupMembersController extends Controller
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

        $groupMembersData = $this->getGroupMembers();
        $sortedMembers = collect($groupMembersData)->sortByDesc('createdAt');
        $groupMembers = ExceptionController::paginate($sortedMembers, 15, $request);

        $employees = collect($this->getEmployeeData())->map(function ($employee) {
            return (object) $employee;
        });
        $groups = GroupController::getActivityGroups();

        // Group the already paginated members
        $groupedMembers = $groupMembers->groupBy('activityGroupId')
            ->map(function ($members, $groupId) use ($groups) {
                $group = collect($groups)->firstWhere('id', $groupId);
                return [
                    'group' => $group,
                    'members' => $members
                ];
            });

        return view('group-setup.members.index', [
            'groupedMembers' => $groupedMembers,
            'paginator' => $groupMembers, // Preserve your original paginator
            'employees' => $employees,
            'groups' => $groups
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        $employees = collect($this->getEmployeeData())->map(function ($employee) {
            return (object) $employee;
        });
        $all_groups = GroupController::getActivityGroups();
        $groups = collect($all_groups)->filter(fn($group) => $group->active == true); //active groups

        return view('group-setup.members.create', compact('employees', 'groups'));
    }




    /**
     * Store a newly created resource in storage.
     */


    public function store(Request $request)
    {
        $request->validate([
            'activityGroupId' => 'required|integer',
            'employeeId' => 'required|array',
            'employeeId.*' => 'required|integer',
        ]);

        try {
            $employeeIds = $request->input('employeeId');
            $activityGroupId = $request->input('activityGroupId');

            // Fetch existing group members
            $groupMembers = $this->getGroupMembers();
            $existingMembers = collect($groupMembers)->where('activityGroupId', $activityGroupId);

            $newMembers = [];
            $skippedMembers = [];

            foreach ($employeeIds as $employeeId) {
                $existingMember = $existingMembers->firstWhere('employeeId', $employeeId);

                if ($existingMember) {
                    // Employee is already in the group, store for message feedback
                    $skippedMembers[] = $existingMember->employeeName;
                } else {
                    // Employee can be added to the group
                    $newMembers[] = [
                        'activityGroupId' => $activityGroupId,
                        'employeeId' => $employeeId,
                    ];
                }
            }

            // Add only new members to the group
            $responses = [];
            foreach ($newMembers as $member) {
                $response = $this->apiService->post(
                    $this->apiService->getEndpoint('group_member'),
                    $member,
                    $this->getApiToken()
                );
                $responses[] = $response;
            }

            // Check if all requests were successful
            $allSuccessful = collect($responses)->every(fn($response) => $response->successful());

            if ($allSuccessful) {
                $message = 'Group Members added successfully';
                if (!empty($skippedMembers)) {
                    $message .= ', but the following members were already in the group: ' . implode(', ', $skippedMembers);
                }
                return redirect()->route('members')->with('toast_success', $message);
            } else {
                foreach ($responses as $response) {
                    if (!$response->successful()) {
                        Log::error('Failed to add Group Member to group', [
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);
                    }
                }
                return redirect()->back()->with('toast_error', 'Sorry, failed to add some Group Members to group');
            }
        } catch (\Exception $e) {
            return $this->handleApiException($e, 'adding group members', [
                'activity_group_id' => $request->input('activityGroupId')
            ]);
        }
    }


    /**
     * Display the specified resource.
     */

    public function edit($id)
    {
        $employees = collect($this->getEmployeeData())->map(function ($employee) {
            return (object) $employee;
        });


        $groups = GroupController::getActivityGroups();

        try {
            // Make the GET request to the external API
            $response = $this->getAGroupMember($id);

            $groupMemberIds = collect($this->getGroupMembers())->where('id', $id)->unique()->toArray();
            $groupMemberEmployeeId = $groupMember->employeeId ?? null; // Get the employeeId from the group member
            // Check the response status and return appropriate response
            if (!empty($response)) {
                $groupMember = $response;

                return view('group-setup.members.edit', compact('employees', 'groupMember', 'groupMemberIds', 'groupMemberEmployeeId', 'groups'));
            } else {

                return redirect()->back()->with('toast_error', 'Group Member does not exist');
            }
        } catch (\Exception $e) {
            return $this->handleApiException($e, 'fetching group member', ['member_id' => $id]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'activityGroupId' => 'required|integer',
            'employeeId' => 'required|integer',
        ]);

        $activityGroupId = $request->input('activityGroupId');
        $employeeId = $request->input('employeeId');

        try {
            // Fetch existing group members
            $groupMembers = $this->getGroupMembers();
            $existingMember = collect($groupMembers)->firstWhere(function ($member) use ($activityGroupId, $employeeId) {
                return $member->activityGroupId == $activityGroupId && $member->employeeId == $employeeId;
            });

            if ($existingMember) {
                return redirect()->back()->with('toast_error', "{$existingMember->employeeName} is already in the group and cannot be added again.");
            }

            $data = [
                'id' => $id,
                'activityGroupId' => $activityGroupId,
                'employeeId' => $employeeId,
            ];

            $response = $this->apiService->put(
                $this->apiService->getEndpoint('group_member'),
                $data,
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Group Member updated successfully',
                'members',
                'Update group member'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'updating group member', ['data' => $data ?? []]);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $response = $this->apiService->delete(
                "{$this->apiService->getEndpoint('group_member')}/{$id}",
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Group Member deleted successfully',
                'members',
                'Delete group member'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'deleting group member', ['member_id' => $id]);
        }
    }

    /**
     * Fetch employee data from the HRMS API (external API)
     */
    public static function getEmployeeData()
    {
        try {
            // Note: This uses a different API (HRMS on port 5124, not Auditor API on 5126)
            // Hardcoded for now as it's a different service
            $response = \Illuminate\Support\Facades\Http::withToken(session('api_token'))
                ->get('http://192.168.1.200:5124/HRMS/Employee/GetAllEmployeesWithRoles');

            if ($response->successful()) {
                $branches = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $branches = [];
                Log::warning('Branch API returned 404 Not Found');
            } else {
                $branches = [];
                Log::error('Branch API request failed', ['status' => $response->status()]);
            }
        } catch (\Exception $e) {
            $branches = [];
            Log::error('Error fetching branch data', ['error' => $e->getMessage()]);
        }

        return $branches;
    }

    public static function getGroupMembers()
    {
        $service = app(AuditorApiService::class);

        try {
            $response = $service->get(
                $service->getEndpoint('group_member'),
                session('api_token')
            );

            if ($response->successful()) {
                $group_members = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $group_members = [];
                Log::warning('Group Member API returned 404 Not Found');
                toast('Group Member data not found', 'warning');
            } else {
                $group_members = [];
                Log::error('Activity API request failed', ['status' => $response->status()]);
                toast('Error fetching Group Member data', 'error');
            }
        } catch (\Exception $e) {
            $group_members = [];
            Log::error('Error fetching Group Member data', ['error' => $e->getMessage()]);
            toast('Error fetching Group Member data', 'error');
        }

        return $group_members;
    }

    public function getAGroupMember($id)
    {
        try {
            $response = $this->apiService->get(
                "{$this->apiService->getEndpoint('group_member')}/{$id}",
                $this->getApiToken()
            );

            if ($response->successful()) {
                $groups = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $groups = [];
                Log::warning('The Group Member API returned 404 Not Found');
                toast('The Group Member data not found', 'warning');
            } else {

                $groups = [];
                Log::error('Group Member API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                toast('Error fetching the Group Member data', 'error');
            }
        } catch (\Exception $e) {
            $groups = [];
            Log::error('Error fetching the Group Member data', ['error' => $e->getMessage()]);
            toast('Error fetching the Group Member data', 'error');
        }

        return $groups;
    }
}
