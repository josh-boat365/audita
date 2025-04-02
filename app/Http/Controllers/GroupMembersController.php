<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GroupMembersController extends Controller
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

        $groupMembersData = $this->getGroupMembers();
        $sortedMembers = collect($groupMembersData)->sortByDesc('createdAt');
        $groupMembers = ExceptionController::paginate($sortedMembers, 15, $request);

        $employees = $this->getEmployeeData();
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
        $employees = $this->getEmployeeData();
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

        $access_token = session('api_token');

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
                $response = Http::withToken($access_token)->post('http://192.168.1.200:5126/Auditor/GroupMember', $member);
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
            Log::error('Exception occurred while adding Group Members to group', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }


    /**
     * Display the specified resource.
     */

    public function edit($id)
    {
        $employees = $this->getEmployeeData();
        $groups = GroupController::getActivityGroups();

        try {
            // Make the GET request to the external API
            $response = $this->getAGroupMember($id);

            // Check the response status and return appropriate response
            if (!empty($response)) {
                $groupMember = $response;

                return view('group-setup.members.edit', compact('employees', 'groupMember', 'groups'));
            } else {

                return redirect()->back()->with('toast_error', 'Group Member does not exist');
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while fetching Group Member to group', [
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
            'activityGroupId' => 'required|integer',
            'employeeId' => 'required|integer',
        ]);

        $access_token = session('api_token');
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

            $response = Http::withToken($access_token)->put(
                'http://192.168.1.200:5126/Auditor/GroupMember/',
                $data
            );

            if ($response->successful()) {
                return redirect()->route('members')->with('toast_success', 'Group Member updated successfully');
            } else {
                Log::error('Failed to update group member', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to update group member');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while updating group member', [
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
                ->delete("http://192.168.1.200:5126/Auditor/GroupMember/{$id}");

            // Check the response status and return appropriate response
            if ($response->successful()) {
                return redirect()->route('members')->with('toast_success', 'Group deleted successfully');
            } else {
                // Log the error response
                Log::error('Failed to delete group', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to delete group');
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while deleting group', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }

    /**
     * Fetch branch data from the API
     */
    public static function getEmployeeData()
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5124/HRMS/Employee/GetAllEmployeesWithRoles');

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
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/GroupMember');

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
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/GroupMember/' . $id);

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
