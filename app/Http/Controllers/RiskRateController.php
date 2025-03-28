<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class RiskRateController extends Controller
{
    public function index(Request $request)
    {
        $access_token = session('api_token');

        if (empty($access_token)) {
            return redirect()->route('login')->with('toast_warning', 'Session expired, login to access the application');
        }

        $riskRatesData = $this->getRiskRates();

        $sortedRiskRates = collect($riskRatesData)->sortByDesc('createdAt');

        $riskRates = ExceptionController::paginate($sortedRiskRates, 15, $request);

        return view('risk-rate-setup.index', compact('riskRates'));
    }

    /**
     * Show the form for creating a new resource.
     */



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'required|integer',
        ]);

        $access_token = session('api_token');

        $data = [
            'name' => $request->input('name'),
            'active' => $request->input('active') == 1 ? true : false,
        ];

        try {
            $response = Http::withToken($access_token)->post('http://192.168.1.200:5126/Auditor/RiskRate', $data);

            if ($response->successful()) {

                return redirect()->route('risk-rate')->with('toast_success', 'Risk Rate created successfully');
            } else {
                // Log the error response
                Log::error('Failed to create Risk Rate', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to create Risk Rate');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while creating Risk Rate', [
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
            // Log the exception
            Log::error('Exception occurred while fetching Risk Rate', [
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
            'active' => 'required|integer',
        ]);

        $access_token = session('api_token');

        $data = [
            'id' => $id,
            'name' => $request->input('name'),
            'active' => $request->input('active') == 1 ? true : false,
        ];
        // dd($data);

        try {
            $response = Http::withToken($access_token)->put(
                'http://192.168.1.200:5126/Auditor/RiskRate/',
                $data
            );

            if ($response->successful()) {
                return redirect()->route('risk-rate')->with('toast_success', 'Risk Rate updated successfully');
            } else {
                // Log the error response
                Log::error('Failed to update risk rate', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to update Risk Rate');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while updating Risk Rate', [
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
                ->delete("http://192.168.1.200:5126/Auditor/RiskRate/{$id}");

            // Check the response status and return appropriate response
            if ($response->successful()) {
                return redirect()->route('risk-rate')->with('toast_success', 'Risk Rate deleted successfully');
            } else {
                // Log the error response
                Log::error('Failed to delete Risk Rate', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to delete Risk Rate');
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while deleting Risk Rate', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }

    /**
     * Fetch branch data from the API
     */

    public static function getRiskRates()
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/RiskRate');

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
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/RiskRate/' . $id);

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
