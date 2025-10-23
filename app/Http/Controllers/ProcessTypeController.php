<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AuditorApiService;
use App\Http\Traits\HandlesApiErrors;

class ProcessTypeController extends Controller
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

        $processTypesData = $this->getProcessTypes();
        $sortedProcessTypes = collect($processTypesData)->sortByDesc('createdAt');

        $processTypes = ExceptionController::paginate($sortedProcessTypes, 15, $request);

        return view('process-type-setup.index', compact('processTypes'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'required|integer',
        ]);

        $data = [
            'name' => $request->input('name'),
            'active' => $request->input('active') == 1 ? true : false,
        ];

        try {
            $response = $this->apiService->post(
                $this->apiService->getEndpoint('process_type'),
                $data,
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Process Type created successfully',
                'process-type',
                'Create process type'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'creating process type', ['data' => $data]);
        }
    }



    public function storeSubProcess(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'processTypeId' => 'required|integer',
            'active' => 'required|integer',
        ]);

        $data = [
            'name' => $request->input('name'),
            'processTypeId' => $request->input('processTypeId'),
            'active' => $request->input('active') == 1 ? true : false,
        ];

        try {
            $response = $this->apiService->post(
                $this->apiService->getEndpoint('sub_process_type'),
                $data,
                $this->getApiToken()
            );

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sub Process Type created successfully',
                    'data' => $data['name'],
                    'processTypeId' => $data['processTypeId']
                ]);
            } else {
                Log::error('Failed to create sub process type', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to create sub process type');
            }
        } catch (\Exception $e) {
            return $this->handleApiException($e, 'creating sub process type', ['data' => $data]);
        }
    }



    /**
     * Display the specified resource.
     */
    public function edit(string $id)
    {

        try {
            // Make the GET request to the external API
            $response = $this->getAProcessType($id);

            // Check the response status and return appropriate response
            if (!empty($response)) {
                $processType = $response;

                return view('process-type-setup.edit', compact('processType'));
            } else {

                return redirect()->back()->with('toast_error', 'Process Type does not exist');
            }
        } catch (\Exception $e) {
            return $this->handleApiException($e, 'fetching process type', ['process_type_id' => $id]);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'required|integer',
        ]);

        $data = [
            'id' => $id,
            'name' => $request->input('name'),
            'active' => $request->input('active') == 1 ? true : false,
        ];

        try {
            $response = $this->apiService->put(
                $this->apiService->getEndpoint('process_type'),
                $data,
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Process Type updated successfully',
                'process-type',
                'Update process type'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'updating process type', ['data' => $data]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $response = $this->apiService->delete(
                "{$this->apiService->getEndpoint('process_type')}/{$id}",
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Process Type deleted successfully',
                'process-type',
                'Delete process type'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'deleting process type', ['process_type_id' => $id]);
        }
    }

    /**
     * Fetch process type data from the API
     */

    public static function getProcessTypes()
    {
        $service = app(AuditorApiService::class);

        try {
            $response = $service->get(
                $service->getEndpoint('process_type'),
                session('api_token')
            );

            if ($response->successful()) {
                $processType = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $processType = [];
                Log::warning('Process Type API returned 404 Not Found');
                toast('Process Type data not found', 'warning');
            } else {

                $processType = [];
                Log::error('Process Type API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                toast('Error fetching process type data', 'error');
            }
        } catch (\Exception $e) {
            $processType = [];
            Log::error('Error fetching process type data', ['error' => $e->getMessage()]);
            toast('Error fetching process type data', 'error');
        }

        return $processType;
    }


    public static function getSubProcessTypes()
    {
        $service = app(AuditorApiService::class);

        try {
            $response = $service->get(
                $service->getEndpoint('sub_process_type'),
                session('api_token')
            );

            if ($response->successful()) {
                $processType = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $processType = [];
                Log::warning('Process Type API returned 404 Not Found');
                toast('Sub Process Type data not found', 'warning');
            } else {

                $processType = [];
                Log::error('Sub Process Type API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                toast('Error fetching sub process type data', 'error');
            }
        } catch (\Exception $e) {
            $processType = [];
            Log::error('Error fetching sub process type data', ['error' => $e->getMessage()]);
            toast('Error fetching sub process type data', 'error');
        }

        return $processType;
    }


    public static function getSubProcessTypesByProcessTypeId($processTypeId = null)
    {
        $service = app(AuditorApiService::class);

        try {
            $response = $service->get(
                $service->getEndpoint('sub_process_type'),
                session('api_token')
            );

            if ($response->successful()) {
                $allSubProcessTypes = $response->json() ?? [];

                // If specific processTypeId requested, filter results
                if ($processTypeId) {
                    $filtered = array_filter($allSubProcessTypes, function ($item) use ($processTypeId) {
                        return $item['processTypeId'] == $processTypeId;
                    });
                    return response()->json(array_values($filtered));
                }

                return response()->json($allSubProcessTypes);
            }

            return response()->json([], $response->status());
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }


    public function getAProcessType($id)
    {
        try {
            $response = $this->apiService->get(
                "{$this->apiService->getEndpoint('process_type')}/{$id}",
                $this->getApiToken()
            );

            if ($response->successful()) {
                $processType = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $processType = [];
                Log::warning('The Process Type API returned 404 Not Found');
                toast('The Process Type data not found', 'warning');
            } else {

                $processType = [];
                Log::error('The Process Type API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                toast('Error fetching the process type data', 'error');
            }
        } catch (\Exception $e) {
            $processType = [];
            Log::error('Error fetching the process type data', ['error' => $e->getMessage()]);
            toast('Error fetching the process type data', 'error');
        }

        return $processType;
    }
}
