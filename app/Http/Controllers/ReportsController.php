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

        $reportsData = $this->getAllReports();
        $batches = BatchController::getBatches();
        $groups = GroupController::getActivityGroups();
        $groupMembers = GroupMembersController::getGroupMembers();
        $employeeId = ExceptionController::getLoggedInUserInformation()->id;


        // Filter active batches with status 'OPEN' and map them by ID
        $validBatches = collect($batches)
            ->filter(fn($batch) => $batch->active && $batch->status === 'OPEN')
            ->keyBy('id');

        // Filter active groups and map them by ID
        $validGroups = collect($groups)
            ->filter(fn($group) => $group->active)
            ->keyBy('id');

        // Get groups where the specified employee belongs
        $employeeGroups = collect($groupMembers)
            ->where('employeeId', $employeeId)
            ->pluck('activityGroupId')
            ->unique();

        // dd($employeeGroups);

        // Map batch IDs to their corresponding activity group IDs
        $batchGroupMap = collect($batches)
            ->pluck('activityGroupId', 'id');

        $employeeRoleId = ExceptionController::getLoggedInUserInformation()->empRoleId;

        // top managers
        // 1 - Managing Director
        // 2 - Head of Internal Audit
        // 4 - Head of Internal Control & Compliance
        $topManagers = [1, 2, 4];

        // Filter exceptions - and include top managers
        $filteredExceptions = collect($reportsData)->filter(function ($exception) use ($validBatches, $validGroups, $employeeGroups, $batchGroupMap, $topManagers, $employeeRoleId) {
            $groupId = $batchGroupMap[$exception->exceptionBatchId] ?? null;
            return $validBatches->has($exception->exceptionBatchId) &&
                $validGroups->has($groupId) && (in_array($exception->status, ['APPROVED', 'ANALYSIS', 'RESOLVED'])) && $employeeGroups->contains($groupId) || (in_array($employeeRoleId, $topManagers));
        });

        $reports = $filteredExceptions->values()->all();



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

    /**
     * Download the report as a WORD.
     */
    public function exportWord(Request $request)
    {
        try {
            // Validate input
            if (!$request->has('data')) {
                throw new \Exception('No data provided');
            }

            $data = json_decode($request->input('data'), true);
            $filters = json_decode($request->input('filters', '{}'), true) ?? [];

            if (empty($data)) {
                return back()->with('error', 'No data to export');
            }

            // Create new Word document
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $phpWord->setDefaultFontName('Arial');
            $phpWord->setDefaultFontSize(11);

            // Add document properties
            $properties = $phpWord->getDocInfo();
            $properties->setCreator('Internal Audit System');
            $properties->setTitle('Audit Exception Report');

            // Add styles
            $this->addDocumentStyles($phpWord);

            // Process data
            $branchGroups = $this->groupDataByBranch($data);
            $this->addContentToDocument($phpWord, $branchGroups, $filters);

            // Create writer and save to memory
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');

            // Generate filename
            $filename = 'Exception_Report_' . ($filters['year'] ?? date('Y')) . '_' . date('Ymd_His') . '.docx';

            // Stream the file to the browser
            return response()->streamDownload(
                function () use ($objWriter) {
                    $objWriter->save('php://output');
                },
                $filename,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                    'Expires' => '0'
                ]
            );
        } catch (\Exception $e) {
            Log::error('Document generation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate document: ' . $e->getMessage());
        }
    }

    private function addDocumentStyles(PhpWord $phpWord)
    {
        // Title styles
        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 16, 'color' => '1F4E79']);

        // Paragraph styles
        $phpWord->addFontStyle('headerMain', ['bold' => true, 'size' => 16, 'alignment' => 'center']);
        $phpWord->addFontStyle('headerSub', ['bold' => true, 'size' => 14, 'alignment' => 'center']);
        $phpWord->addFontStyle('headerBranch', ['bold' => true, 'size' => 12, 'alignment' => 'center']);
        $phpWord->addFontStyle('exceptionTitle', ['bold' => true, 'size' => 12, 'underline' => 'single']);
        $phpWord->addFontStyle('metadata', ['size' => 10, 'color' => '555555']);
        $phpWord->addFontStyle('normalText', ['size' => 11]);
        $phpWord->addFontStyle('boldText', ['size' => 11, 'bold' => true]);
    }

    private function addContentToDocument(PhpWord $phpWord, array $branchGroups, array $filters)
    {
        foreach ($branchGroups as $branchName => $reports) {
            $section = $phpWord->addSection();

            // Add document header
            $section->addText('BESTPOINT SAVINGS AND LOANS LIMITED', 'headerMain');
            $section->addText('INTERNAL AUDIT DEPARTMENT EXCEPTION SHEET', 'headerSub');

            $batchYear = $filters['year'] ?? date('Y');
            $section->addText("FOR $branchName BRANCH DURING THE $batchYear AUDIT", 'headerBranch');
            $section->addTextBreak(2);

            // Add exceptions
            foreach ($reports as $report) {
                $this->addExceptionSection($section, $report);
                $section->addTextBreak(1);
            }

            if ($branchName !== array_key_last($branchGroups)) {
                $section->addPageBreak();
            }
        }
    }

    private function addExceptionSection($section, $report)
    {
        $section->addText($report['exceptionTitle'] ?? 'Exception', 'exceptionTitle');

        $metaText = sprintf(
            "Process: %s | Department: %s | Risk: %s | Status: %s | Auditor: %s | Date: %s",
            $report['processType'] ?? 'N/A',
            $report['department'] ?? 'N/A',
            $report['riskRate'] ?? 'N/A',
            $report['status'] ?? 'N/A',
            $report['auditorName'] ?? 'N/A',
            substr($report['occurrenceDate'] ?? '', 0, 10) ?: 'N/A'
        );
        $section->addText($metaText, 'metadata');

        $section->addText('Description:', 'boldText');
        $section->addText($report['exception'] ?? 'No description provided', 'normalText');

        if (!empty($report['recommendation'])) {
            $section->addText('Recommendation:', 'boldText');
            $section->addText($report['recommendation'], 'normalText');
        }

        $section->addText(str_repeat('_', 80), ['size' => 8, 'color' => 'CCCCCC']);
    }

    private function defineDocumentStyles(PhpWord $phpWord)
    {
        $phpWord->addFontStyle('headerMain', ['bold' => true, 'size' => 16]);
        $phpWord->addFontStyle('headerSub', ['bold' => true, 'size' => 14]);
        $phpWord->addFontStyle('headerBranch', ['bold' => true, 'size' => 12]);
        $phpWord->addFontStyle('exceptionTitle', ['bold' => true, 'size' => 12, 'underline' => 'single']);
        $phpWord->addFontStyle('metadata', ['size' => 10, 'color' => '555555']);
        $phpWord->addFontStyle('sectionLabel', ['bold' => true, 'size' => 11]);
        $phpWord->addFontStyle('normalText', ['size' => 11]);
        $phpWord->addFontStyle('separator', ['color' => 'AAAAAA', 'size' => 8]);
    }


    private function groupDataByBranch($data)
    {
        $groups = [];
        $batches = collect(session('batches', []));
        $activityGroups = collect(session('groups', []));

        foreach ($data as $report) {
            $branchName = $this->getBranchName($report['exceptionBatchId'], $batches, $activityGroups);
            if (!isset($groups[$branchName])) {
                $groups[$branchName] = [];
            }
            $groups[$branchName][] = $report;
        }

        return $groups;
    }

    private function getBranchName($exceptionBatchId, $batches, $activityGroups)
    {
        $batch = $batches->firstWhere('id', $exceptionBatchId);
        if ($batch) {
            $group = $activityGroups->firstWhere('id', $batch['activityGroupId']);
            if ($group) {
                return $group['branchName'];
            }
        }
        return 'Unknown Branch';
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
}
