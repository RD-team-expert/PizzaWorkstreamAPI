<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\WorkStreamExporter;
use Illuminate\Support\Facades\Route;


Route::middleware(['excel_secret'])->group(function () {
    Route::get('/get-token', [ApiController::class, 'getAccessToken']);
    Route::get('/position-applications', [ApiController::class, 'getPositionApplications']);
    Route::get('/test-middleware', function () {
        return response()->json(['success' => true]);
    });
    Route::get('/update-data-warehouse', [ApiController::class, 'updateDataWarehouse']);

    Route::get('/applicants-csv', [WorkStreamExporter::class, 'exportCsv']);
});



