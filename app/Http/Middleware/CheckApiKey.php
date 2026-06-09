<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-IAE-KEY');

        if ($apiKey !== '102022400300') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Invalid or missing API Key.',
                'errors' => null
            ], 401);
        }

        return $next($request);
    }
}