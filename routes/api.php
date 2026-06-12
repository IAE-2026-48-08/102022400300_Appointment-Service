<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppointmentController;

Route::prefix('v1')->middleware('iae.key')->group(function () {
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);

    Route::post('/appointments', [AppointmentController::class, 'store'])
        ->middleware('sso.jwt');
});