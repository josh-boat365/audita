<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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

        $reports = collect($this->getAllReports())->filter(function ($report) {
            // Include all statuses except DECLINED for the regular reports view
            // DECLINED exceptions are viewable in auditor-report view where there's more filtering
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
        // dd($reportsData);
        $batchData = BatchController::getBatches();
        $employeeData = ExceptionManipulationController::getLoggedInUserInformation();

        // Handle case where API is unreachable (returns empty array)
        if (is_array($employeeData) && empty($employeeData)) {
            $employeeFullName = 'Unknown User';
            $employeeDepartment = 'Unknown Department';
        } else {
            $employeeFullName = $employeeData->firstName . ' ' . $employeeData->surname;
            $employeeDepartment = $employeeData->department->name;
        }


        $batches = collect($batchData)->filter(function ($batch) use ($employeeDepartment) {
            return isset($batch->createdAt) && ($employeeDepartment ===  $batch->auditorUnitName);
        });

        // dd($batches);

        // $groups = GroupController::getActivityGroups();


        $statuses = ['APPROVED', 'ANALYSIS', 'RESOLVED', 'DECLINED', 'NOT-RESOLVED'];
        $retrieveExceptions = FilterExceptionController::handleException($reportsData,  $statuses);
        $reports = $retrieveExceptions;

        $groups = $this->getGroupsForAuditorUnit($reportsData, $batches);
        // dd($groups);

        // dd($reports);


        return view('reports.auditor-report', compact('reports', 'batches', 'groups'));
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

    function getGroupsForAuditorUnit($reports, $batches)
    {
        $batchIds = $batches->pluck('id')->toArray();

        // Get unique group IDs from reports that belong to these batches
        $relevantGroupIds = $reports
            ->filter(fn($report) => in_array($report->exceptionBatchId, $batchIds))
            ->pluck('activityGroupId')
            ->unique()
            ->toArray();

        // Filter and return only relevant groups as collection
        return collect(GroupController::getActivityGroups())
            ->filter(fn($group) => in_array($group->id, $relevantGroupIds));
    }








    private function validateSession()
    {
        if (empty(session('api_token'))) {
            session()->flush();
            return redirect()->route(route: 'login')->with('toast_warning', 'Session expired, login to access the application');
        }
        return null;
    }
}
