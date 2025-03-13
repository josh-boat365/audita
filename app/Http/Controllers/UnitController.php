<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class UnitController extends Controller
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

        $auditUnits = $this->getAuditUnitData();

        return view('unit-setup.index', compact('auditUnits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $access_token = session('api_token');

        $data = [
            'name' => $request->input('name'),
        ];

        try {
            $response = Http::withToken($access_token)->post('http://192.168.1.200:5126/Auditor/AuditorUnit', $data);

            if ($response->successful()) {

                return redirect()->route('unit')->with('toast_success', 'Audit Unit created successfully');
            } else {
                // Log the error response
                Log::error('Failed to create audit unit', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to create audit unit');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while creating audit unit', [
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
        // $auditUnit = $this->getAuditUnitData();

        try {
            // Make the GET request to the external API
            $response = $this->getAnAuditUnit($id);

            // Check the response status and return appropriate response
            if (!empty($response)) {
                $auditUnit = $response;

                return view('unit-setup.edit', compact( 'auditUnit'));
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
            'name' => 'required|string',
        ]);

        $access_token = session('api_token');

        $data = [
            'id' => $id,
            'name' => $request->input('name'),
        ];

        // dd($data);


        try {
            $response = Http::withToken($access_token)->put(
                'http://192.168.1.200:5126/Auditor/AuditorUnit/',
                $data
            );

            if ($response->successful()) {
                return redirect()->route('unit')->with('toast_success', 'Audit Unit updated successfully');
            } else {
                // Log the error response
                Log::error('Failed to update audit unit', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to update audit unit');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while updating audit unit', [
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
                ->delete("http://192.168.1.200:5126/Auditor/AuditorUnit/{$id}");

            // Check the response status and return appropriate response
            if ($response->successful()) {
                return redirect()->route('unit')->with('toast_success', 'Audit Unit deleted successfully');
            } else {
                // Log the error response
                Log::error('Failed to delete audit unit', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to delete audit unit');
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while deleting audit unit', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }

    public static function getAuditUnitData()
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/AuditorUnit');

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
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/AuditorUnit/' . $id);

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
