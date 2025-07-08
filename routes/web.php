<?php

use App\Http\Controllers\AuditCreateController;
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
use App\Http\Controllers\ProcessTypeController;
use App\Http\Controllers\GroupMembersController;
use App\Http\Controllers\ExceptionApprovalController;


Route::post('/getAuthAPIToken', [AuthController::class, 'getAuthToken']);

// Route::middleware(['guest'])->group(
// function () {
Route::get('/', function () {
    return view('auth.auth-login');
})->name('login');

Route::get('/register', function () {
    return view('auth.auth-register');
})->name('register');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
// }
// );


// Route::middleware(['auth'])->group(function () {
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/my-dashboard/group/{id}', [DashboardController::class, 'groupDashboard'])->name('my.group.dashboard');

// BATCH SETUP
Route::get('/batch', [BatchController::class, 'index'])->name('batch');
Route::post('/batch', [BatchController::class, 'store'])->name('batch.post');
Route::get('/batch-edit/{id}', [BatchController::class, 'edit'])->name('batch.edit');
Route::post('/batch/{id}/update', [BatchController::class, 'update'])->name('batch.update');
Route::post('/batch/{id}/delete', [BatchController::class, 'destroy'])->name('batch.delete');


// GROUP SETUP
Route::get('/group', [GroupController::class, 'index'])->name('group');
Route::post('/group', [GroupController::class, 'store'])->name('group.post');
Route::get('/group-edit/group/{id}', [GroupController::class, 'edit'])->name('group.edit');
Route::post('/group-update/{id}/group', [GroupController::class, 'update'])->name('group.update');
Route::post('/group-delete/{id}/group', [GroupController::class, 'destroy'])->name('group.delete');

//GROUP MEMBERS SETUP
Route::get('/group-members', [GroupMembersController::class, 'index'])->name('members');
Route::get('/group-members-create', [GroupMembersController::class, 'create'])->name('members.create');
Route::post('/group-members-create', [GroupMembersController::class, 'store'])->name('members.post');
Route::get('/group-members/{id}/edit', [GroupMembersController::class, 'edit'])->name('members.edit');
Route::post('/group-members/{id}/update', [GroupMembersController::class, 'update'])->name('members.update');
Route::post('/group-members/{id}/delete', [GroupMembersController::class, 'destroy'])->name('members.delete');


//UNIT SETUP
Route::get('/unit', [UnitController::class, 'index'])->name('unit');
Route::post('/unit', [UnitController::class, 'store'])->name('unit.post');
Route::get('/unit-edit/{id}', [UnitController::class, 'edit'])->name('unit.edit');
Route::post('/unit/{id}/update', [UnitController::class, 'update'])->name('unit.update');
Route::post('/unit/{id}/delete', [UnitController::class, 'destroy'])->name('unit.delete');

//PROCESS TYPE SETUP
Route::get('/process-type', [ProcessTypeController::class, 'index'])->name('process-type');
Route::post('/process-type', [ProcessTypeController::class, 'store'])->name('process-type.post');
Route::get('/process-type/{id}/edit', [ProcessTypeController::class, 'edit'])->name('process-type.edit');
Route::post('/process-type/{id}/update', [ProcessTypeController::class, 'update'])->name('process-type.update');
Route::post('/process-type/{id}/delete', [ProcessTypeController::class, 'destroy'])->name('process-type.delete');

//SUB PROCESS TYPE SETUP
Route::post('/sub-process-type', [ProcessTypeController::class, 'storeSubProcess'])->name('sub.process.type');
Route::get('/get-sub-process-types/{processTypeId}', [ProcessTypeController::class, 'getSubProcessTypesByProcessTypeId'])->name('get.subProcessTypes');


//RISK RATE SETUP
Route::get('/risk-rate', [RiskRateController::class, 'index'])->name('risk-rate');
Route::post('/risk-rate', [RiskRateController::class, 'store'])->name('risk-rate.post');
Route::get('/risk-rate/{id}/edit', [RiskRateController::class, 'edit'])->name('risk-rate.edit');
Route::post('/risk-rate/{id}/update', [RiskRateController::class, 'update'])->name('risk-rate.update');
Route::post('/risk-rate/{id}/delete', [RiskRateController::class, 'destroy'])->name('risk-rate.delete');

//EXCEPTION SETUP
Route::get('/list-exception', [ExceptionController::class, 'index'])->name('exception.list');
Route::get('/list-pending-exception', [ExceptionController::class, 'pendingExceptions'])->name('exception.pending');
Route::get('/list-resolved-exception', [ExceptionController::class, 'resolvedExceptions'])->name('exception.resolved');
Route::get('/create-exception', [ExceptionController::class, 'create'])->name('exception.create');
Route::post('/create-exception', [ExceptionController::class, 'store'])->name('exception.post');
Route::get('/exception/{id}/open', [ExceptionController::class, 'edit2'])->name('exception.edit');
Route::get('/exception/{id}/open-pending', [ExceptionController::class, 'edit2'])->name('exception.pending.edit');
Route::post('/exception/{id}/update', [ExceptionController::class, 'update'])->name('exception.update');
Route::post('/exception/{id}/delete', [ExceptionController::class, 'destroy'])->name('exception.delete');
Route::post('/exception/{id}/file-upload', [ExceptionController::class, 'exceptionFileUpload'])->name('exception.file.upload');
Route::get('/exception/{id}/get-files', [ExceptionController::class, 'downloadExceptionFile'])->name('exception.file.download');
Route::delete('/exception/{id}/file-delete', [ExceptionController::class, 'deleteExceptionFile'])->name('exception.file.delete');
Route::post('/exception/{id}/close', [ExceptionController::class, 'closeException'])->name('exception.close');
Route::post('/exception/{id}/auditee-resolution', [ExceptionController::class, 'recommendExceptionForResolution'])->name('exception.resolution');

//EXCEPTION COMMENTS
Route::post('/exception/{id}/comment', [ExceptionController::class, 'storeComment'])->name('exception.comment.post');
Route::post('/exception/{id}/comment-delete', [ExceptionController::class, 'deleteComment'])->name('exception.comment.delete');
Route::post('/exception/{id}/comment-edit', [ExceptionController::class, 'updateComment'])->name('exception.comment.edit');

//EXCEPTION APPROVALS
Route::get('/exception/supervisor-approval-list', [ExceptionApprovalController::class, 'exceptionSupList'])->name('exception.supervisor.list');
Route::get('/exception/supervisor/show-exception-list-for-approval/{batchId}/{status}', [ExceptionApprovalController::class, 'showExceptionListWithStatusForApproval'])->name('show.supervisor.exception.for.approval');
Route::get('/exception/{id}/open-supervisor-approval', [ExceptionApprovalController::class, 'supEditException'])->name('exception.supervisor.edit');
Route::get('/exception/auditor-approval-list', [ExceptionApprovalController::class, 'exceptionAuditorList'])->name('exception.auditor.list');
Route::get('/exception/auditor/show-exception-list-for-approval/{batchId}/{status}', [ExceptionApprovalController::class, 'showAuditorExceptionListForApproval'])->name('show.auditor.exception.list.for.approval');
Route::get('/exception/auditee/exception-list', [ExceptionApprovalController::class, 'auditeeExceptionList'])->name('auditee.exception.list');
// Route::get('/exception/auditee/open-exception-list', [ExceptionApprovalController::class, 'auditeeExceptionView'])->name('auditee.exception.view');

//EXCEPTION APPROVALS ACTIONS - APPROVE OR DECLINE
Route::post('/exception/supervisor-approve-or-decline-single', [ExceptionApprovalController::class, 'supervisorApproveOrDeclineSingleException'])->name('exception.supervisor.approve-decline');
Route::post('/exception/supervisor-action', [ExceptionApprovalController::class, 'supervisorActionOnBatchException'])->name('exception.supervisor.action');

//AUDITEE SUBMIT RESPONSE
Route::post('/exception/auditee-response', [ExceptionApprovalController::class, 'auditeeResponse'])->name('auditee.submit.response');

//AUDITOR ANALYSE EXCEPTION
Route::post('/exception/auditor/push-exception-for-analysis', [ExceptionApprovalController::class, 'auditorPushForAnalysis'])->name('auditor.analysis.push');
Route::get('/exception/auditor/analysis-exception-list', [ExceptionApprovalController::class, 'auditorAnalysisExceptionList'])->name('auditor.analysis.exception');
Route::get('/exception/auditor/analysis-exception-view', [ExceptionApprovalController::class, 'auditorAnalysisExceptionView'])->name('auditor.analysis.exception.view');

//REPORTS
Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
Route::get('/reports/{id}/download', [ReportsController::class, 'download'])->name('reports.download');
Route::post('/reports/export-pdf', [ReportsController::class, 'exportPdf'])->name('reports.export.pdf');


//AUDIT CREATE
Route::get('/audit/create', [AuditCreateController::class, 'index'])->name('audit.create');
Route::post('/audit/bulk-exceptions/create', [AuditCreateController::class, 'store'])->name('bulk.exception.create');
Route::post('/audit/create', [AuditCreateController::class, 'store'])->name('audit.post');
Route::get('/audit/{id}/open', [AuditCreateController::class, 'editAudit'])->name('audit.edit');
Route::post('/audit/{id}/update', [AuditCreateController::class, 'updateAudit'])->name('audit.update');
Route::post('/audit/{id}/delete', [AuditCreateController::class, 'destroyAudit'])->name('audit.delete');








Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// });
