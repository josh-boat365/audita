<?php

namespace App\Http\Controllers;

use App\Services\AuditorApiService;
use App\Http\Traits\HandlesApiErrors;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\Log;

class ReportsController extends Controller
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
    public function index()
    {
        // Validate session
        $sessionValidation = $this->validateSession();
        if ($sessionValidation) {
            return $sessionValidation;
        }

        $reports = collect($this->getAllReports())->filter(function ($report) {
            return !in_array($report->status, ['DECLINED']);
        })->values()->all();
        $batches = BatchController::getBatches();
        $groups = GroupController::getActivityGroups();


        return view('reports.index', compact('reports', 'batches', 'groups'));
    }

    /**
     * Display the auditor's report.
     */
    public function auditorReport()
    {
        // Validate session
        $sessionValidation = $this->validateSession();
        if ($sessionValidation) {
            return $sessionValidation;
        }

        $reportsData = collect($this->getAllReports());
        $batches = BatchController::getBatches();
        $groups = GroupController::getActivityGroups();

        $statuses = ['APPROVED', 'ANALYSIS', 'RESOLVED'];
        $retrieveExceptions = FilterExceptionController::handleException($reportsData,  $statuses);
        $reports = $retrieveExceptions;


        return view('reports.auditor-report', compact('reports', 'batches', 'groups'));
    }

    public static function getAllReports()
    {
        $access_token = session('api_token');

        try {
            $apiService = app(AuditorApiService::class);

            $response = $apiService->get(
                $apiService->getEndpoint('exception_tracker'),
                $access_token
            );

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





    private function validateSession()
    {
        if (!$this->hasValidApiToken()) {
            session()->flush();
            return redirect()->route(route: 'login')->with('toast_warning', 'Session expired, login to access the application');
        }
        return null;
    }
}
