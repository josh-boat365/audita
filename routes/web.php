<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UnitController;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\RiskRateController;
use App\Http\Controllers\ExceptionController;
use App\Http\Controllers\ProcessTypeController;
use App\Http\Controllers\GroupMembersController;


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
Route::get('/dashboard', function () {

    return view('dashboard');
})->name('dashboard');

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

//RISK RATE SETUP
Route::get('/risk-rate', [RiskRateController::class, 'index'])->name('risk-rate');
Route::post('/risk-rate', [RiskRateController::class, 'store'])->name('risk-rate.post');
Route::get('/risk-rate/{id}/edit', [RiskRateController::class, 'edit'])->name('risk-rate.edit');
Route::post('/risk-rate/{id}/update', [RiskRateController::class, 'update'])->name('risk-rate.update');
Route::post('/risk-rate/{id}/delete', [RiskRateController::class, 'destroy'])->name('risk-rate.delete');

//EXCEPTION SETUP
Route::get('/list-exception', [ExceptionController::class, 'index'])->name('exception.list');
Route::get('/create-exception', [ExceptionController::class, 'create'])->name('exception.create');
Route::post('/create-exception', [ExceptionController::class, 'store'])->name('exception.post');
Route::get('/exception/{id}/open', [ExceptionController::class, 'edit'])->name('exception.edit');
Route::post('/exception/{id}/update', [ExceptionController::class, 'update'])->name('exception.update');
Route::get('/exception/{id}/delete', [ExceptionController::class, 'destroy'])->name('exception.delete');
Route::post('/exception/{id}/file-upload', [ExceptionController::class, 'exceptionFileUpload'])->name('exception.file.upload');
// Route::get('/exception/{id}/file-download', [ExceptionController::class, 'exceptionFileDownload'])->name('exception.file.download');
Route::get('/exception/{id}/file-delete', [ExceptionController::class, 'exceptionFileDelete'])->name('exception.file.delete');




Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// });
