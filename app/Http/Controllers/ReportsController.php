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

        $reports = $this->getAllReports();
        $batches = BatchController::getBatches();
        $groups = GroupController::getActivityGroups();


        return view('reports.index', compact('reports', 'batches', 'groups'));
    }

    /**
     * Display the auditor's report.
     */
    public function auditorReport(){
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
        $filters = json_decode($request->input('filters'), true);
        $data = json_decode($request->input('data'), true);

        if (empty($data)) {
            return back()->with('error', 'No data to export');
        }

        // Group data by branch
        $branchGroups = $this->groupDataByBranch($data);

        // Create Word document
        $phpWord = new PhpWord();

        // Set document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('Audit System');
        $properties->setTitle('Audit Exception Report');
        $properties->setDescription('Generated audit exception report');

        // Define styles
        $this->defineStyles($phpWord);

        // Add content
        $this->addDocumentHeader($phpWord, $filters, count($data));
        $this->addExecutiveSummary($phpWord, $data, $branchGroups);
        $this->addBranchDetails($phpWord, $branchGroups);

        // Generate filename
        $filename = $this->generateFilename($filters);

        // Save and download
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter->save('php://output');
        exit;
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

    private function defineStyles($phpWord)
    {
        // Title style
        $phpWord->addTitleStyle(0, ['size' => 20, 'bold' => true, 'color' => '1F4E79']);
        $phpWord->addTitleStyle(1, ['size' => 16, 'bold' => true, 'color' => '2F5597']);
        $phpWord->addTitleStyle(2, ['size' => 14, 'bold' => true, 'color' => '2F5597']);
        $phpWord->addTitleStyle(3, ['size' => 12, 'bold' => true]);

        // Header style
        $phpWord->addFontStyle('headerStyle', ['bold' => true, 'size' => 14, 'color' => '1F4E79']);

        // Normal text style
        $phpWord->addFontStyle('normalText', ['size' => 11]);

        // Bold text style
        $phpWord->addFontStyle('boldText', ['size' => 11, 'bold' => true]);

        // Risk styles
        $phpWord->addFontStyle('highRisk', ['size' => 11, 'bold' => true, 'color' => 'DC3545']);
        $phpWord->addFontStyle('mediumRisk', ['size' => 11, 'bold' => true, 'color' => 'FFC107']);
        $phpWord->addFontStyle('lowRisk', ['size' => 11, 'bold' => true, 'color' => '28A745']);

        // Paragraph styles
        // $phpWord->addParagraphStyle('justified', ['alignment' => \PhpOffice\PhpWord\Style\Alignment::ALIGN_BOTH, 'spaceAfter' => 200]);
        // $phpWord->addParagraphStyle('centered', ['alignment' => \PhpOffice\PhpWord\Style\Alignment::ALIGN_CENTER]);
    }

    private function addDocumentHeader($phpWord, $filters, $totalRecords)
    {
        $section = $phpWord->addSection();

        // Document title
        $section->addTitle('AUDIT EXCEPTION REPORT', 0);
        $section->addTextBreak(1);

        // Report metadata
        $section->addText('Report Generated: ' . Carbon::now()->format('F d, Y \a\t H:i A'), 'boldText');
        $section->addText('Total Exceptions: ' . $totalRecords, 'boldText');
        $section->addTextBreak(1);

        // Applied filters
        if (!empty(array_filter($filters))) {
            $section->addTitle('Applied Filters', 2);
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    $label = ucfirst(str_replace(['_', 'Filter'], [' ', ''], $key));
                    $section->addText($label . ': ' . $value, 'normalText');
                }
            }
            $section->addTextBreak(1);
        }

        $section->addTextBreak(2);
    }

    private function addExecutiveSummary($phpWord, $data, $branchGroups)
    {
        $section = $phpWord->addSection();

        $section->addTitle('EXECUTIVE SUMMARY', 1);
        $section->addTextBreak(1);

        // Calculate statistics
        $totalExceptions = count($data);
        $branchCount = count($branchGroups);
        $riskStats = $this->calculateRiskStats($data);
        $statusStats = $this->calculateStatusStats($data);
        $processStats = $this->calculateProcessStats($data);

        // Create summary text
        $summaryText = "This audit exception report covers {$totalExceptions} exceptions across {$branchCount} branches. ";

        // Add risk information
        if ($riskStats['High'] > 0) {
            $summaryText .= "The report identifies {$riskStats['High']} high-risk exceptions that require immediate attention. ";
        }

        if ($riskStats['Medium'] > 0) {
            $summaryText .= "Additionally, {$riskStats['Medium']} medium-risk exceptions have been documented for management review. ";
        }

        if ($riskStats['Low'] > 0) {
            $summaryText .= "There are {$riskStats['Low']} low-risk exceptions that have been noted for process improvement. ";
        }

        // Add status information
        $resolvedCount = $statusStats['RESOLVED'] ?? 0;
        $pendingCount = $statusStats['PENDING'] ?? 0;

        if ($resolvedCount > 0) {
            $percentage = round(($resolvedCount / $totalExceptions) * 100, 1);
            $summaryText .= "Of the total exceptions, {$resolvedCount} ({$percentage}%) have been successfully resolved. ";
        }

        if ($pendingCount > 0) {
            $summaryText .= "{$pendingCount} exceptions remain pending resolution. ";
        }

        // Add process information
        $topProcesses = array_slice($processStats, 0, 3);
        $processList = implode(', ', array_keys($topProcesses));
        $summaryText .= "The exceptions span across multiple process areas including {$processList}, indicating the need for comprehensive control improvements across various operational areas.";

        // Add visual statistics
        $section->addText($summaryText, 'normalText', 'justified');
        $section->addTextBreak(1);

        // Add statistics tables side by side
        $table = $section->addTable([
            'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
            'cellMargin' => 50,
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER
        ]);

        $table->addRow();

        // Risk Distribution table
        $cell = $table->addCell(8000);
        $this->addStatisticsTableToCell($cell, 'Risk Distribution', $riskStats, [
            'High' => 'highRisk',
            'Medium' => 'mediumRisk',
            'Low' => 'lowRisk'
        ]);

        // Status Overview table
        $cell = $table->addCell(8000);
        $this->addStatisticsTableToCell($cell, 'Status Overview', $statusStats, [
            'RESOLVED' => 'normalText',
            'PENDING' => 'boldText',
            'OPEN' => 'normalText',
            'CLOSED' => 'normalText'
        ]);

        $section->addTextBreak(2);

        // Add top processes chart
        $section->addTitle('Top Process Areas', 2);
        $this->addProcessChart($section, $processStats);

        $section->addPageBreak();
    }

    private function addStatisticsTableToCell($cell, $title, $stats, $styleMap = [])
    {
        $innerTable = $cell->addTable([
            'borderSize' => 6,
            'borderColor' => 'DDDDDD',
            'cellMargin' => 50
        ]);

        // Header row
        $innerTable->addRow();
        $headerCell = $innerTable->addCell(5000, ['bgColor' => 'F2F2F2']);
        $headerCell->addText($title, 'boldText');
        $headerCell = $innerTable->addCell(3000, ['bgColor' => 'F2F2F2']);
        $headerCell->addText('Count', 'boldText');
        $headerCell = $innerTable->addCell(2000, ['bgColor' => 'F2F2F2']);
        $headerCell->addText('%', 'boldText');

        // Data rows
        $total = array_sum($stats);
        foreach ($stats as $key => $value) {
            $innerTable->addRow();
            $dataCell = $innerTable->addCell(5000);

            $style = $styleMap[$key] ?? 'normalText';
            $dataCell->addText($key, $style);

            $countCell = $innerTable->addCell(3000);
            $countCell->addText($value, 'normalText');

            $percentCell = $innerTable->addCell(2000);
            $percentage = $total > 0 ? round(($value / $total) * 100, 1) : 0;
            $percentCell->addText($percentage . '%', 'normalText');
        }
    }

    private function addProcessChart($section, $processStats)
    {
        // Limit to top 5 processes
        $topProcesses = array_slice($processStats, 0, 5);
        $totalExceptions = array_sum($processStats);

        $table = $section->addTable([
            'borderSize' => 1,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 50,
            'width' => 100 * 50
        ]);

        $table->addRow();

        // Add chart bars
        foreach ($topProcesses as $process => $count) {
            $percentage = round(($count / $totalExceptions) * 100);
            $barWidth = $percentage * 5; // Scale factor for visual representation

            $table->addRow();
            $cell = $table->addCell(2000);
            $cell->addText($process, 'boldText');

            $cell = $table->addCell(1000);
            $cell->addText($count, 'normalText');

            $cell = $table->addCell(10000);
            $cell->addText(str_repeat(' ', 10), null, [
                'shading' => [
                    'fill' => $this->getProcessChartColor($percentage),
                    'start' => 0,
                    'end' => $barWidth
                ]
            ]);

            $cell = $table->addCell(1000);
            $cell->addText($percentage . '%', 'normalText');
        }

        $section->addTextBreak(1);
        $section->addText('Figure 1: Distribution of exceptions across top process areas', ['size' => 9, 'italic' => true]);
    }

    private function addBranchDetails($phpWord, $branchGroups)
    {
        foreach ($branchGroups as $branchName => $reports) {
            $section = $phpWord->addSection();

            // Branch header
            $section->addTitle($branchName, 1);
            $section->addTextBreak(1);

            // Branch statistics
            $riskStats = $this->calculateRiskStats($reports);
            $statusStats = $this->calculateStatusStats($reports);

            // Add statistics tables
            $this->addStatisticsTable($section, 'Risk Distribution', $riskStats, [
                'High' => 'highRisk',
                'Medium' => 'mediumRisk',
                'Low' => 'lowRisk'
            ]);

            $this->addStatisticsTable($section, 'Status Overview', $statusStats, [
                'RESOLVED' => 'normalText',
                'PENDING' => 'boldText'
            ]);

            // Detailed exceptions
            $section->addTextBreak(1);
            $section->addTitle('Exception Details', 2);

            foreach ($reports as $report) {
                $this->addExceptionDetails($section, $report);
                $section->addTextBreak(1);
            }
        }
    }

    private function addStatisticsTable($section, $title, $stats, $styleMap = [])
    {
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'DDDDDD',
            'cellMargin' => 50
        ]);

        // Header row
        $table->addRow();
        $cell = $table->addCell(5000, ['bgColor' => 'F2F2F2']);
        $cell->addText($title, 'boldText');
        $cell = $table->addCell(5000, ['bgColor' => 'F2F2F2']);
        $cell->addText('Count', 'boldText');

        // Data rows
        foreach ($stats as $key => $value) {
            $table->addRow();
            $cell = $table->addCell(5000);

            $style = $styleMap[$key] ?? 'normalText';
            $cell->addText($key, $style);

            $cell = $table->addCell(5000);
            $cell->addText($value, 'normalText');
        }

        $section->addTextBreak(1);
    }

    private function addExceptionDetails($section, $report)
    {
        // Exception header
        $riskStyle = strtolower($report['riskRate']) . 'Risk';
        $section->addText($report['exceptionTitle'] ?? 'Exception', ['bold' => true, 'size' => 12]);

        // Metadata line
        $metaText = "Process: {$report['processType']} | Department: {$report['department']} | ";
        $metaText .= "Risk: {$report['riskRate']} | Status: {$report['status']} | ";
        $metaText .= "Auditor: {$report['auditorName']} | Occurrence Date: " . substr($report['occurrenceDate'], 0, 10);
        $section->addText($metaText, ['size' => 10, 'color' => '666666']);

        // Exception description
        $section->addText('Description:', 'boldText');
        $section->addText($report['exception'], 'normalText', 'justified');

        // Recommendation if exists
        if (!empty($report['recommendation'])) {
            $section->addText('Recommendation:', 'boldText');
            $section->addText($report['recommendation'], 'normalText', 'justified');
        }
    }

    private function calculateRiskStats($data)
    {
        $stats = ['High' => 0, 'Medium' => 0, 'Low' => 0];

        foreach ($data as $report) {
            $risk = $report['riskRate'] ?? 'Low';
            $stats[$risk]++;
        }

        return $stats;
    }

    private function calculateStatusStats($data)
    {
        $stats = [];

        foreach ($data as $report) {
            $status = $report['status'] ?? 'UNKNOWN';
            $stats[$status] = ($stats[$status] ?? 0) + 1;
        }

        return $stats;
    }

    private function calculateProcessStats($data)
    {
        $stats = [];

        foreach ($data as $report) {
            $process = $report['processType'] ?? 'Unknown Process';
            $stats[$process] = ($stats[$process] ?? 0) + 1;
        }

        arsort($stats);
        return $stats;
    }

    private function getProcessChartColor($percentage)
    {
        if ($percentage > 60) return 'C00000'; // Dark red
        if ($percentage > 40) return 'FF0000'; // Red
        if ($percentage > 20) return 'FFC000'; // Orange
        return 'FFFF00'; // Yellow
    }

    private function generateFilename($filters)
    {
        $baseName = 'Audit_Exception_Report_';

        // Add date range if specified
        if (!empty($filters['dateFrom']) || !empty($filters['dateTo'])) {
            $from = $filters['dateFrom'] ?? 'start';
            $to = $filters['dateTo'] ?? 'end';
            $baseName .= $from . '_to_' . $to . '_';
        }

        // Add branch if specified
        if (!empty($filters['batch'])) {
            $baseName .= str_replace(' ', '_', $filters['batch']) . '_';
        }

        // Add current timestamp
        $baseName .= Carbon::now()->format('Ymd_His');

        return $baseName . '.docx';
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
