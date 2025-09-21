<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Add a login route to prevent errors
Route::get('/login', function() {
    return response()->json(['message' => 'Please use the API login endpoint at /api/login'], 401);
})->name('login');
