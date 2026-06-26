<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use L5Swagger\ConfigFactory;
use L5Swagger\Http\Controllers\SwaggerController;

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

Route::get('/docs', function (Request $request) {
    $documentation = 'default';

    $request->offsetSet('documentation', $documentation);
    $request->offsetSet('config', app(ConfigFactory::class)->documentationConfig($documentation));

    return app(SwaggerController::class)->api($request);
});

$graphqlPlayground = function () {
    return response(<<<'HTML'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GraphQL Playground</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f7f7f8; color: #171717; }
        main { max-width: 960px; margin: 32px auto; padding: 0 16px; }
        textarea { width: 100%; min-height: 180px; box-sizing: border-box; padding: 12px; font-family: Consolas, monospace; font-size: 14px; }
        button { margin: 12px 0; padding: 10px 16px; cursor: pointer; }
        pre { min-height: 180px; padding: 12px; overflow: auto; background: #111827; color: #f9fafb; }
    </style>
</head>
<body>
<main>
    <h1>GraphQL Playground</h1>
    <textarea id="query">{ __schema { queryType { name } } }</textarea>
    <button id="run" type="button">Run Query</button>
    <pre id="result">Ready.</pre>
</main>
<script>
document.getElementById('run').addEventListener('click', async function () {
    const result = document.getElementById('result');
    result.textContent = 'Loading...';

    try {
        const response = await fetch('/graphql', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ query: document.getElementById('query').value })
        });

        result.textContent = JSON.stringify(await response.json(), null, 2);
    } catch (error) {
        result.textContent = String(error);
    }
});
</script>
</body>
</html>
HTML, 200)->header('Content-Type', 'text/html; charset=UTF-8');
};

Route::get('/graphql-playground', $graphqlPlayground);
Route::get('/graphiql', $graphqlPlayground);
