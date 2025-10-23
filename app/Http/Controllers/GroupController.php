<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AuditorApiService;
use App\Http\Traits\HandlesApiErrors;

class GroupController extends Controller
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

        $branches = $this->getBranchData();

        $groupsData = $this->getActivityGroups();
        $sortedGroups = collect($groupsData)->sortByDesc('createdAt');

        $groups = ExceptionController::paginate($sortedGroups, 15, $request);

        $employeeData = ExceptionManipulationController::getLoggedInUserInformation();

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

        $data = [
            'name' => $request->input('name'),
            'branchId' => $request->input('branch_id'),
            'active' => $request->input('active') == 1 ? true : false,
        ];

        try {
            $response = $this->apiService->post(
                $this->apiService->getEndpoint('activity_group'),
                $data,
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Group created successfully',
                'group',
                'Create group'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'creating group', ['data' => $data]);
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
            return $this->handleApiException($e, 'fetching group', ['group_id' => $id]);
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

        $data = [
            'id' => $id,
            'name' => $request->input('name'),
            'branchId' => $request->input('branch_id'),
            'active' => $request->input('active') == 1 ? true : false,
        ];

        try {
            $response = $this->apiService->put(
                $this->apiService->getEndpoint('activity_group'),
                $data,
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Group updated successfully',
                'group',
                'Update group'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'updating group', ['data' => $data]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $response = $this->apiService->delete(
                "{$this->apiService->getEndpoint('activity_group')}/{$id}",
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Group deleted successfully',
                'group',
                'Delete group'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'deleting group', ['group_id' => $id]);
        }
    }

    /**
     * Fetch branch data from the API (HRMS external API)
     */
    public function getBranchData()
    {
        try {
            // Note: This uses a different API (HRMS on port 5124, not Auditor API on 5126)
            // Hardcoded for now as it's a different service
            $response = \Illuminate\Support\Facades\Http::withToken(session('api_token'))
                ->get('http://192.168.1.200:5124/HRMS/Branch');

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
        $service = app(AuditorApiService::class);

        try {
            $response = $service->get(
                $service->getEndpoint('activity_group'),
                session('api_token')
            );

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


    public static function getEmployeeGroups()
    {
        $service = app(AuditorApiService::class);

        $employeeId = ExceptionManipulationController::getLoggedInUserInformation()->id;

        try {
            $response = $service->get(
                "{$service->getEndpoint('employee_groups')}/{$employeeId}",
                session('api_token')
            );

            if ($response->successful()) {
                $groups = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $groups = [];
                Log::warning('Employee Group API returned 404 Not Found');
                toast('Employee group data not found', 'warning');
            } else {
                $groups = [];
                Log::error('Employee API request failed', ['status' => $response->status()]);
                toast('Error fetching employee group data', 'error');
            }
        } catch (\Exception $e) {
            $groups = [];
            Log::error('Error fetching employee group data', ['error' => $e->getMessage()]);
            toast('Error fetching employee group data', 'error');
        }

        return $groups;
    }

    public function getAnActivityGroup($id)
    {
        try {
            $response = $this->apiService->get(
                "{$this->apiService->getEndpoint('activity_group')}/{$id}",
                $this->getApiToken()
            );

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
