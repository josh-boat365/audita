<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GroupController extends Controller
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

        $branches = $this->getBranchData();

        $groups = $this->getActivityGroups();
        $employeeData = ExceptionController::getLoggedInUserInformation();

        $employeeFullName = $employeeData->firstName . ' ' . $employeeData->surname;

        return view('group-setup.index', compact('branches', 'groups', 'employeeFullName'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|integer',
            'active' => 'required|integer',
        ]);

        $access_token = session('api_token');

        $data = [
            'name' => $request->input('name'),
            'branchId' => $request->input('branch_id'),
            'active' => $request->input('active') == 1 ? true : false,
        ];

        try {
            $response = Http::withToken($access_token)->post('http://192.168.1.200:5126/Auditor/ActivityGroup', $data);

            if ($response->successful()) {

                return redirect()->route('group')->with('toast_success', 'Group created successfully');
            } else {
                // Log the error response
                Log::error('Failed to create group', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to create group');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while creating group', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }

    /**
     * Display the specified resource.
     */
    public function edit(string $id)
    {
        $branches = $this->getBranchData();

        try {
            // Make the GET request to the external API
            $response = $this->getAnActivityGroup($id);

            // Check the response status and return appropriate response
            if (!empty($response)) {
                $group_data = $response;

                return view('group-setup.edit', compact('group_data', 'branches'));
            } else {

                return redirect()->back()->with('toast_error', 'Group does not exist');
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while fetching group', [
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
            'name' => 'required|string|max:255',
            'branch_id' => 'required|integer',
            'active' => 'required|integer',
        ]);

        $access_token = session('api_token');

        $data = [
            'id' => $id,
            'name' => $request->input('name'),
            'branchId' => $request->input('branch_id'),
            'active' => $request->input('active') == 1 ? true : false,
        ];
        // dd($data);

        try {
            $response = Http::withToken($access_token)->put(
                'http://192.168.1.200:5126/Auditor/ActivityGroup/',
                $data
            );

            if($response->successful()) {
                return redirect()->route('group')->with('toast_success', 'Group updated successfully');
            } else {
                // Log the error response
                Log::error('Failed to update group', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to update group');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while updating group', [
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
                ->delete("http://192.168.1.200:5126/Auditor/ActivityGroup/{$id}");

            // Check the response status and return appropriate response
            if ($response->successful()) {
                return redirect()->route('group')->with('toast_success', 'Group deleted successfully');
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
    public function getBranchData()
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5124/HRMS/Branch');

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

    public static function getActivityGroups()
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/ActivityGroup');

            if ($response->successful()) {
                $groups = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $groups = [];
                Log::warning('Activity Group API returned 404 Not Found');
                toast('Activity group data not found', 'warning');
            } else {
                $groups = [];
                Log::error('Activity API request failed', ['status' => $response->status()]);
                toast('Error fetching activity group data', 'error');
            }
        } catch (\Exception $e) {
            $groups = [];
            Log::error('Error fetching activity group data', ['error' => $e->getMessage()]);
            toast('Error fetching activity group data', 'error');
        }

        return $groups;
    }
    public function getAnActivityGroup($id)
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/ActivityGroup/' . $id);

            if ($response->successful()) {
                $groups = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $groups = [];
                Log::warning('The Activity Group API returned 404 Not Found');
                toast('The Activity group data not found', 'warning');
            } else {

                $groups = [];
                Log::error('The Activity API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                toast('Error fetching the activity group data', 'error');
            }
        } catch (\Exception $e) {
            $groups = [];
            Log::error('Error fetching the activity group data', ['error' => $e->getMessage()]);
            toast('Error fetching the activity group data', 'error');
        }

        return $groups;
    }
}
