<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('getAuthToken', [AuthController::class, 'getAuthToken']);


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
