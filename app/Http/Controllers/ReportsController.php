<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Validate session
        $sessionValidation = $this->validateSession();
        if ($sessionValidation) {
            return $sessionValidation;
        }

        $reports = $this->getAllReports();
        $batches = BatchController::getBatches();
        $groups = GroupController::getActivityGroups();


        return view('reports.index', compact('reports', 'batches', 'groups'));
    }

    public static function getAllReports()
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
                return redirect()->route('login')->with('toast_error', 'Error fetching exception Reports data');
            }
        } catch (\Exception $e) {
            $Reports = [];
            Log::error('Error fetching exception Reports', ['error' => $e->getMessage()]);
            // toast('An error occurred. Please try again later', 'error');
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
        return $Reports;
    }




    public function exportPdf(Request $request)
    {
        // Validate session
        $sessionValidation = $this->validateSession();
        if ($sessionValidation) {
            return $sessionValidation;
        }


        $filters = json_decode($request->input('filters'), true);
        $data = json_decode($request->input('data'), true);
        // dd($data);
        // Generate filename based on filters
        $filename = 'Exceptions_Report';
        if ($filters['batch']) $filename .= '_Batch-' . $filters['batch'];
        if ($filters['branch']) $filename .= '_Branch-' . $filters['branch'];
        if ($filters['status']) $filename .= '_Status-' . $filters['status'];
        $filename .= '.pdf';

        $pdf = Pdf::loadView('reports.pdf_template', [
            'data' => $data,
            'filters' => $filters
        ]);

        return $pdf->stream($filename);
    }

    private function validateSession()
    {
        if (empty(session('api_token'))) {
            session()->flush();
            return redirect()->route(route: 'login')->with('toast_warning', 'Session expired, login to access the application');
        }
        return null;
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
