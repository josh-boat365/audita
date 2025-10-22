<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UnitController;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RiskRateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExceptionController;
use App\Http\Controllers\AuditCreateController;
use App\Http\Controllers\AuditeeDataManipulationController;
use App\Http\Controllers\ChatsAndCommentsController;
use App\Http\Controllers\GroupExceptionsFilter;
use App\Http\Controllers\ProcessTypeController;
use App\Http\Controllers\GroupMembersController;
use App\Http\Controllers\ExceptionApprovalController;
use App\Http\Controllers\ExceptionStatusChange;

/*
|--------------------------------------------------------------------------
| API Authentication Routes
|--------------------------------------------------------------------------
| Routes for API token generation and authentication
*/

Route::post('/getAuthAPIToken', [AuthController::class, 'getAuthToken']);

/*
|--------------------------------------------------------------------------
| Guest Authentication Routes
|--------------------------------------------------------------------------
| Routes for user login and registration (accessible to guests only)
*/

// Route::middleware(['guest'])->group(
// function () {
// Login Routes
Route::get('/', function () {
    return view('auth.auth-login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Registration Routes
Route::get('/register', function () {
    return view('auth.auth-register');
})->name('register');

Route::post('/register', [AuthController::class, 'register'])->name('register.post');
// }
// );

/*
|--------------------------------------------------------------------------
| Protected Dashboard Routes
|--------------------------------------------------------------------------
| Main dashboard and group-specific dashboard routes
*/

// Route::middleware(['auth'])->group(function () {
// Main Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Group Dashboard
Route::get('/my-dashboard/group/{id}', [DashboardController::class, 'groupDashboard'])->name('my.group.dashboard');

/*
|--------------------------------------------------------------------------
| Batch Management Routes
|--------------------------------------------------------------------------
| CRUD operations for batch management
*/

Route::get('/batch', [BatchController::class, 'index'])->name('batch');
Route::post('/batch', [BatchController::class, 'store'])->name('batch.post');
Route::get('/batch-edit/{id}', [BatchController::class, 'edit'])->name('batch.edit');
Route::post('/batch/{id}/update', [BatchController::class, 'update'])->name('batch.update');
Route::post('/batch/{id}/delete', [BatchController::class, 'destroy'])->name('batch.delete');

/*
|--------------------------------------------------------------------------
| Group Management Routes
|--------------------------------------------------------------------------
| CRUD operations for group setup and management
*/

Route::get('/group', [GroupController::class, 'index'])->name('group');
Route::post('/group', [GroupController::class, 'store'])->name('group.post');
Route::get('/group-edit/group/{id}', [GroupController::class, 'edit'])->name('group.edit');
Route::post('/group-update/{id}/group', [GroupController::class, 'update'])->name('group.update');
Route::post('/group-delete/{id}/group', [GroupController::class, 'destroy'])->name('group.delete');

/*
|--------------------------------------------------------------------------
| Group Members Management Routes
|--------------------------------------------------------------------------
| CRUD operations for managing group members
*/

Route::get('/group-members', [GroupMembersController::class, 'index'])->name('members');
Route::get('/group-members-create', [GroupMembersController::class, 'create'])->name('members.create');
Route::post('/group-members-create', [GroupMembersController::class, 'store'])->name('members.post');
Route::get('/group-members/{id}/edit', [GroupMembersController::class, 'edit'])->name('members.edit');
Route::post('/group-members/{id}/update', [GroupMembersController::class, 'update'])->name('members.update');
Route::post('/group-members/{id}/delete', [GroupMembersController::class, 'destroy'])->name('members.delete');

/*
|--------------------------------------------------------------------------
| Unit Management Routes
|--------------------------------------------------------------------------
| CRUD operations for unit setup and management
*/

Route::get('/unit', [UnitController::class, 'index'])->name('unit');
Route::post('/unit', [UnitController::class, 'store'])->name('unit.post');
Route::get('/unit-edit/{id}', [UnitController::class, 'edit'])->name('unit.edit');
Route::post('/unit/{id}/update', [UnitController::class, 'update'])->name('unit.update');
Route::post('/unit/{id}/delete', [UnitController::class, 'destroy'])->name('unit.delete');

/*
|--------------------------------------------------------------------------
| Process Type Management Routes
|--------------------------------------------------------------------------
| CRUD operations for process types and sub-process types
*/

// Main Process Type Routes
Route::get('/process-type', [ProcessTypeController::class, 'index'])->name('process-type');
Route::post('/process-type', [ProcessTypeController::class, 'store'])->name('process-type.post');
Route::get('/process-type/{id}/edit', [ProcessTypeController::class, 'edit'])->name('process-type.edit');
Route::post('/process-type/{id}/update', [ProcessTypeController::class, 'update'])->name('process-type.update');
Route::post('/process-type/{id}/delete', [ProcessTypeController::class, 'destroy'])->name('process-type.delete');

// Sub Process Type Routes
Route::post('/sub-process-type', [ProcessTypeController::class, 'storeSubProcess'])->name('sub.process.type');
Route::get('/get-sub-process-types/{processTypeId}', [ProcessTypeController::class, 'getSubProcessTypesByProcessTypeId'])->name('get.subProcessTypes');

/*
|--------------------------------------------------------------------------
| Risk Rate Management Routes
|--------------------------------------------------------------------------
| CRUD operations for risk rate setup and management
*/

Route::get('/risk-rate', [RiskRateController::class, 'index'])->name('risk-rate');
Route::post('/risk-rate', [RiskRateController::class, 'store'])->name('risk-rate.post');
Route::get('/risk-rate/{id}/edit', [RiskRateController::class, 'edit'])->name('risk-rate.edit');
Route::post('/risk-rate/{id}/update', [RiskRateController::class, 'update'])->name('risk-rate.update');
Route::post('/risk-rate/{id}/delete', [RiskRateController::class, 'destroy'])->name('risk-rate.delete');

/*
|--------------------------------------------------------------------------
| Exception Management Routes
|--------------------------------------------------------------------------
| Core exception CRUD operations, file management, and comments
*/

// Exception Listing Routes
Route::get('/list-exception', [ExceptionController::class, 'index'])->name('exception.list');
Route::get('/list-pending-exception', [ExceptionController::class, 'pendingExceptions'])->name('exception.pending');
Route::get('/list-resolved-exception', [ExceptionController::class, 'resolvedExceptions'])->name('exception.resolved');

// Exception CRUD Routes
Route::get('/create-exception', [ExceptionController::class, 'create'])->name('exception.create');
Route::post('/create-exception', [ExceptionController::class, 'store'])->name('exception.post');
Route::get('/exception/{id}/open', [ExceptionController::class, 'edit'])->name('exception.edit');
Route::get('/exception/{id}/open-pending', [ExceptionController::class, 'edit'])->name('exception.pending.edit');
Route::post('/exception/{id}/update', [ExceptionController::class, 'update'])->name('exception.update');
Route::post('/exception/{id}/delete', [ExceptionController::class, 'destroy'])->name('exception.delete');

// Exception File Management
Route::post('/exception/{id}/file-upload', [ChatsAndCommentsController::class, 'exceptionFileUpload'])->name('exception.file.upload');
Route::get('/exception/{id}/get-files', [ChatsAndCommentsController::class, 'downloadExceptionFile'])->name('exception.file.download');
Route::delete('/exception/{id}/file-delete', [ChatsAndCommentsController::class, 'deleteExceptionFile'])->name('exception.file.delete');

// Exception Status Management
Route::post('/exception/{id}/close', [ExceptionStatusChange::class, 'closeException'])->name('exception.close');
Route::post('/exception/{id}/auditee-resolution', [ExceptionStatusChange::class, 'recommendExceptionForResolution'])->name('exception.resolution');

// Exception Comments Management
Route::post('/exception/{id}/comment', [ChatsAndCommentsController::class, 'storeComment'])->name('exception.comment.post');
Route::post('/exception/{id}/comment-delete', [ChatsAndCommentsController::class, 'deleteComment'])->name('exception.comment.delete');
Route::post('/exception/{id}/comment-edit', [ChatsAndCommentsController::class, 'updateComment'])->name('exception.comment.edit');

/*
|--------------------------------------------------------------------------
| Exception Approval Workflow Routes
|--------------------------------------------------------------------------
| Routes for supervisor, auditor, and auditee exception approvals
*/

// Supervisor Approval Routes
Route::get('/exception/supervisor-approval-list', [ExceptionApprovalController::class, 'exceptionSupList'])->name('exception.supervisor.list');
Route::get('/exception/supervisor/show-exception-list-for-approval/{batchId}/{status}', [AuditeeDataManipulationController::class, 'showExceptionListWithStatusForApproval'])->name('show.supervisor.exception.for.approval');
Route::get('/exception/{id}/open-supervisor-approval', [ExceptionApprovalController::class, 'supEditException'])->name('exception.supervisor.edit');

// Auditor Approval Routes
Route::get('/exception/auditor-approval-list', [ExceptionApprovalController::class, 'exceptionAuditorList'])->name('exception.auditor.list');
Route::get('/exception/auditor/show-exception-list-for-approval/{batchId}/{status}', [AuditeeDataManipulationController::class, 'showAuditorExceptionListForApproval'])->name('show.auditor.exception.list.for.approval');

// Auditee [Branch Exception] Routes
Route::get('/exception/auditee/exception-list', [AuditeeDataManipulationController::class, 'auditeeExceptionList'])->name('auditee.exception.list');
Route::get('/exception/auditee/pending-exception-list', [AuditeeDataManipulationController::class, 'auditeePendingExceptionList'])->name('auditee.pending.exception.list');
Route::post('/exception/auditee-push-back-to-auditor', [ExceptionStatusChange::class, 'exceptionPushBackToAuditor'])->name('auditee.push.back');
Route::get('/exception/group-exception-enquiry-list', [GroupExceptionsFilter::class, 'groupExceptionStatus'])->name('group.exception.enquiry.list');
Route::get('/exception/group-exception-enquiry-open/{exceptionId}/{exceptionStatus}', [GroupExceptionsFilter::class, 'openBatch'])->name('group.exception.open');


// New filtering route
Route::get('/exception/group-filter-exceptions', [GroupExceptionsFilter::class, 'filterExceptions'])
    ->name('exceptions.filter');

// Optional: Route to get filter options dynamically
Route::get('/exception/group-filter-options', [GroupExceptionsFilter::class, 'getFilterOptions'])
    ->name('exceptions.filter-options');

/*
|--------------------------------------------------------------------------
| Exception Approval Actions
|--------------------------------------------------------------------------
| Routes for approval/decline actions and responses
*/

// Supervisor Approval Actions
Route::post('/exception/supervisor-approve-or-decline-single', [ExceptionApprovalController::class, 'supervisorApproveOrDeclineSingleException'])->name('exception.supervisor.approve-decline');
Route::post('/exception/supervisor-action', [ExceptionApprovalController::class, 'supervisorActionOnBatchException'])->name('exception.supervisor.action');

// Auditee Response Submission
Route::post('/exception/auditee-response', [ExceptionApprovalController::class, 'auditeeResponse'])->name('auditee.submit.response');

/*
|--------------------------------------------------------------------------
| Auditor Exception Analysis Routes
|--------------------------------------------------------------------------
| Routes for auditor analysis and exception review
*/

Route::post('/exception/auditor/push-exception-for-analysis', [ExceptionApprovalController::class, 'auditorPushForAnalysis'])->name('auditor.analysis.push');
Route::get('/exception/auditor/analysis-exception-list', [ExceptionApprovalController::class, 'auditorAnalysisExceptionList'])->name('auditor.analysis.exception');
Route::get('/exception/auditor/analysis-exception-view', [ExceptionApprovalController::class, 'auditorAnalysisExceptionView'])->name('auditor.analysis.exception.view');

/*
|--------------------------------------------------------------------------
| Reports and Export Routes
|--------------------------------------------------------------------------
| Routes for generating and downloading reports
*/

Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
Route::get('/auditor-reports', [ReportsController::class, 'auditorReport'])->name('auditor.report');
// Route::get('/reports/{id}/download', [ReportsController::class, 'download'])->name('reports.download');
// Route::post('/reports/export-pdf', [ReportsController::class, 'exportPdf'])->name('reports.export.pdf');
// Route::post('/reports/export-pdf', [ReportsController::class, 'exportWord'])->name('reports.export-word');

/*
|--------------------------------------------------------------------------
| Audit Creation and Management Routes
|--------------------------------------------------------------------------
| Routes for creating and managing audits
*/

Route::get('/audit/create', [AuditCreateController::class, 'index'])->name('audit.create');
Route::get('/auditor/exception-status-list', [AuditCreateController::class, 'list'])->name('audit.list');
Route::get('/exception/exception-status-view/{exceptionId}/{exceptionStatus}', [AuditCreateController::class, 'viewExceptionStatus'])->name('audit.view.exception.status');
Route::post('/audit/bulk-exceptions/create', [AuditCreateController::class, 'store'])->name('bulk.exception.create');
Route::post('/audit/create', [AuditCreateController::class, 'store'])->name('audit.post');
Route::get('/audit/{id}/open', [AuditCreateController::class, 'editAudit'])->name('audit.edit');
Route::post('/audit/{id}/update', [AuditCreateController::class, 'updateAudit'])->name('audit.update');
Route::post('/audit/{id}/delete', [AuditCreateController::class, 'destroyAudit'])->name('audit.delete');









Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
