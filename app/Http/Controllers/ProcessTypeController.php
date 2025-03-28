<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ProcessTypeController extends Controller
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

        $processTypesData = $this->getProcessTypes();
        $sortedProcessTypes = collect($processTypesData)->sortByDesc('createdAt');

        $processTypes = ExceptionController::paginate($sortedProcessTypes, 15, $request);

        return view('process-type-setup.index', compact('processTypes'));
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
            $response = Http::withToken($access_token)->post('http://192.168.1.200:5126/Auditor/ProcessType', $data);

            if ($response->successful()) {

                return redirect()->route('process-type')->with('toast_success', 'Process Type created successfully');
            } else {
                // Log the error response
                Log::error('Failed to create process type', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to create process type');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while creating process type', [
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
            $response = $this->getAProcessType($id);

            // Check the response status and return appropriate response
            if (!empty($response)) {
                $processType = $response;

                return view('process-type-setup.edit', compact('processType'));
            } else {

                return redirect()->back()->with('toast_error', 'Process Type does not exist');
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while fetching process type', [
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
                'http://192.168.1.200:5126/Auditor/ProcessType/',
                $data
            );

            if ($response->successful()) {
                return redirect()->route('process-type')->with('toast_success', 'Process Type updated successfully');
            } else {
                // Log the error response
                Log::error('Failed to update process type', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to update process type');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while updating process type', [
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
                ->delete("http://192.168.1.200:5126/Auditor/ProcessType/{$id}");

            // Check the response status and return appropriate response
            if ($response->successful()) {
                return redirect()->route('process-type')->with('toast_success', 'Process Type deleted successfully');
            } else {
                // Log the error response
                Log::error('Failed to delete process type', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to delete process type');
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while deleting process type', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }

    /**
     * Fetch branch data from the API
     */

    public static function getProcessTypes()
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/ProcessType');

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
    public function getAProcessType($id)
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/ProcessType/' . $id);

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
