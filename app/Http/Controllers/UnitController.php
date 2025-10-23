<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AuditorApiService;
use App\Http\Traits\HandlesApiErrors;

class UnitController extends Controller
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

        $auditUnitsData = $this->getAuditUnitData();

        $sortedAuditUnit = collect($auditUnitsData)->sortByDesc('createdAt');

        $auditUnits = ExceptionController::paginate($sortedAuditUnit, 15, $request);

        return view('unit-setup.index', compact('auditUnits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $data = [
            'name' => $request->input('name'),
        ];

        try {
            $response = $this->apiService->post(
                $this->apiService->getEndpoint('auditor_unit'),
                $data,
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Audit Unit created successfully',
                'unit',
                'Create audit unit'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'creating audit unit', ['data' => $data]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function edit(string $id)
    {
        try {
            // Make the GET request to the external API
            $response = $this->getAnAuditUnit($id);

            // Check the response status and return appropriate response
            if (!empty($response)) {
                $auditUnit = $response;

                return view('unit-setup.edit', compact('auditUnit'));
            } else {

                return redirect()->back()->with('toast_error', 'Audit Unit does not exist');
            }
        } catch (\Exception $e) {
            return $this->handleApiException($e, 'fetching audit unit', ['unit_id' => $id]);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $data = [
            'id' => $id,
            'name' => $request->input('name'),
        ];

        try {
            $response = $this->apiService->put(
                $this->apiService->getEndpoint('auditor_unit'),
                $data,
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Audit Unit updated successfully',
                'unit',
                'Update audit unit'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'updating audit unit', ['data' => $data]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $response = $this->apiService->delete(
                "{$this->apiService->getEndpoint('auditor_unit')}/{$id}",
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Audit Unit deleted successfully',
                'unit',
                'Delete audit unit'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'deleting audit unit', ['unit_id' => $id]);
        }
    }

    public static function getAuditUnitData()
    {
        $service = app(AuditorApiService::class);

        try {
            $response = $service->get(
                $service->getEndpoint('auditor_unit'),
                session('api_token')
            );

            if ($response->successful()) {
                $auditUnits = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $auditUnits = [];
                Log::warning('Audit Unit API returned 404 Not Found');
            } else {
                $auditUnits = [];
                Log::error('Audit Unit API request failed', ['status' => $response->status()]);
            }
        } catch (\Exception $e) {
            $auditUnits = [];
            Log::error('Error fetching audit unit data', ['error' => $e->getMessage()]);
        }

        return $auditUnits;
    }

    public function getAnAuditUnit($id)
    {
        try {
            $response = $this->apiService->get(
                "{$this->apiService->getEndpoint('auditor_unit')}/{$id}",
                $this->getApiToken()
            );

            if ($response->successful()) {
                $auditUnit = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $auditUnit = [];
                Log::warning('The Audit Unit API returned 404 Not Found');
                toast('The Audit Unit not found', 'warning');
            } else {
                $auditUnit = [];
                Log::error('The Audit Unit API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                toast('Error fetching the audit unit', 'error');
            }
        } catch (\Exception $e) {
            $auditUnit = [];
            Log::error('Error fetching the audit unit', ['error' => $e->getMessage()]);
            toast('Error fetching the audit unit', 'error');
        }

        return $auditUnit;
    }

}
