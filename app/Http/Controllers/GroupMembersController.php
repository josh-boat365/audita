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
    public function index()
    {
        $access_token = session('api_token');

        if (empty($access_token)) {
            return redirect()->route('login')->with('toast_warning', 'Session expired, login to access the application');
        }


        $groupMembers = $this->getGroupMembers();
        // dd($groupMembers);
        $employees = $this->getEmployeeData();
        $groups = GroupController::getActivityGroups();

        return view('group-setup.members.index', compact('groupMembers', 'groups', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        $employees = $this->getEmployeeData();
        $groups = GroupController::getActivityGroups();

        return view('group-setup.members.create', compact('employees', 'groups'));
    }




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'activityGroupId' => 'required|integer',
            'employeeId' => 'required|integer',
        ]);

        $access_token = session('api_token');

        $data = [
            'activityGroupId' => $request->input('activityGroupId'),
            'employeeId' => $request->input('employeeId'),
        ];

        try {
            $response = Http::withToken($access_token)->post('http://192.168.1.200:5126/Auditor/GroupMember', $data);

            if ($response->successful()) {

                return redirect()->route('members')->with('toast_success', 'Group Member added to Group successfully');
            } else {
                // Log the error response
                Log::error('Failed to added Group Member to group', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to added Group Member to group');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while adding Group Member to group', [
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

        $data = [
            'id' => $id,
            'activityGroupId' => $request->input('activityGroupId'),
            'employeeId' => $request->input('employeeId'),
        ];

        // dd($data);

        try {
            $response = Http::withToken($access_token)->put(
                'http://192.168.1.200:5126/Auditor/GroupMember/',
                $data
            );

            if ($response->successful()) {
                return redirect()->route('members')->with('toast_success', 'Group Member updated successfully');
            } else {
                // Log the error response
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
    public function getEmployeeData()
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
