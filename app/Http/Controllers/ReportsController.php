<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = $this->getAllReports();
        return view('reports.index', compact('reports'));
    }

    public function getAllReports()
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/ExceptionTracker');

            if ($response->successful()) {

                $Reports = $response->object() ?? [];
                // $Reports = collect($api_response)->filter(fn($comment) => $comment->exceptionTrackerId == $exceptionId)->all() ?? [];
            } elseif ($response->status() == 404) {
                $Reports = [];
                Log::warning('Exception Reports API returned 404 Not Found');
                toast('Exception Reports data not found', 'warning');
            } else {
                $Reports = [];
                Log::error('Exception Reports API request failed', ['status' => $response->status()]);
                toast('Error fetching exception Reports data', 'error');
            }
        } catch (\Exception $e) {
            $Reports = [];
            Log::error('Error fetching exception Reports', ['error' => $e->getMessage()]);
            toast('An error occurred. Please try again later', 'error');
        }
        return $Reports;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
