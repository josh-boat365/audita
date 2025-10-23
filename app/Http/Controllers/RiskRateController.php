<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AuditorApiService;
use App\Http\Traits\HandlesApiErrors;

class RiskRateController extends Controller
{
    use HandlesApiErrors;

    protected AuditorApiService $apiService;

    public function __construct(AuditorApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(Request $request)
    {
        if (!$this->hasValidApiToken()) {
            return $this->redirectToLoginIfNoToken();
        }

        $riskRatesData = $this->getRiskRates();

        $sortedRiskRates = collect($riskRatesData)->sortByDesc('createdAt');

        $riskRates = ExceptionController::paginate($sortedRiskRates, 15, $request);

        return view('risk-rate-setup.index', compact('riskRates'));
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
                $this->apiService->getEndpoint('risk_rate'),
                $data,
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Risk Rate created successfully',
                'risk-rate',
                'Create risk rate'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'creating risk rate', ['data' => $data]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function edit(string $id)
    {

        try {
            // Make the GET request to the external API
            $response = $this->getARiskRate($id);

            // Check the response status and return appropriate response
            if (!empty($response)) {
                $riskRate = $response;

                return view('risk-rate-setup.edit', compact('riskRate'));
            } else {

                return redirect()->back()->with('toast_error', 'Risk Rate does not exist');
            }
        } catch (\Exception $e) {
            return $this->handleApiException($e, 'fetching risk rate', ['risk_rate_id' => $id]);
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
                $this->apiService->getEndpoint('risk_rate'),
                $data,
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Risk Rate updated successfully',
                'risk-rate',
                'Update risk rate'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'updating risk rate', ['data' => $data]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $response = $this->apiService->delete(
                "{$this->apiService->getEndpoint('risk_rate')}/{$id}",
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Risk Rate deleted successfully',
                'risk-rate',
                'Delete risk rate'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'deleting risk rate', ['risk_rate_id' => $id]);
        }
    }

    /**
     * Fetch risk rate data from the API
     */

    public static function getRiskRates()
    {
        $service = app(AuditorApiService::class);

        try {
            $response = $service->get(
                $service->getEndpoint('risk_rate'),
                session('api_token')
            );

            if ($response->successful()) {
                $riskRate = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $riskRate = [];
                Log::warning('Risk Rate API returned 404 Not Found');
                toast('Risk Rate data not found', 'warning');
            } else {

                $riskRate = [];
                Log::error('Risk Rate API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                toast('Error fetching Risk Rate data', 'error');
            }
        } catch (\Exception $e) {
            $riskRate = [];
            Log::error('Error fetching Risk Rate data', ['error' => $e->getMessage()]);
            toast('Error fetching Risk Rate data', 'error');
        }

        return $riskRate;
    }

    public function getARiskRate($id)
    {
        try {
            $response = $this->apiService->get(
                "{$this->apiService->getEndpoint('risk_rate')}/{$id}",
                $this->getApiToken()
            );

            if ($response->successful()) {
                $riskRate = $response->object() ?? [];
            } elseif ($response->status() == 404) {
                $riskRate = [];
                Log::warning('The Risk Rate API returned 404 Not Found');
                toast('The Risk Rate data not found', 'warning');
            } else {

                $riskRate = [];
                Log::error('The Risk Rate API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                toast('Error fetching the Risk Rate data', 'error');
            }
        } catch (\Exception $e) {
            $riskRate = [];
            Log::error('Error fetching the Risk Rate data', ['error' => $e->getMessage()]);
            toast('Error fetching the Risk Rate data', 'error');
        }

        return $riskRate;
    }
}
