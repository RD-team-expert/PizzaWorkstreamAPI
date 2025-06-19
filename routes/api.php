<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;


Route::middleware(['excel_secret'])->group(function () {
    Route::get('/get-token', [ApiController::class, 'getAccessToken']);
    Route::get('/position-applications', [ApiController::class, 'getPositionApplications']);
    Route::get('/test-middleware', function () {
        return response()->json(['success' => true]);
    });
});