<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/openapi.json', function () {
    $path = storage_path('api-docs/api-docs.json');

    abort_unless(is_file($path), 404, 'OpenAPI specification has not been generated.');

    return response()->file($path, [
        'Content-Type' => 'application/json',
    ]);
});

Route::get('/api-docs.json', function () {
    $path = storage_path('api-docs/api-docs.json');

    abort_unless(is_file($path), 404, 'OpenAPI specification has not been generated.');

    return response()->file($path, [
        'Content-Type' => 'application/json',
    ]);
});

Route::redirect('/docs', '/api/documentation');
