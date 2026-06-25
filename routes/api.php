<?php

use App\Http\Controllers\Api\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('iae.key')->group(function () {
    Route::get(
        '/appointments',
        [AppointmentController::class, 'index']
    );

    Route::get(
        '/appointments/health',
        [AppointmentController::class, 'health']
    );

    Route::get(
        '/appointments/{id}',
        [AppointmentController::class, 'show']
    );

    Route::post(
        '/appointments',
        [AppointmentController::class, 'store']
    );
});