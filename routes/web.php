<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.auth-login');
})->name('login');
