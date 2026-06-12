<?php

namespace App\Http\Middleware;

use App\Models\LocalRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySsoJwt
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Missing Bearer token.',
                'errors' => null
            ], 401);
        }

        $token = str_replace('Bearer ', '', $authHeader);
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid JWT format.',
                'errors' => null
            ], 401);
        }

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        if (!$payload) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid JWT payload.',
                'errors' => null
            ], 401);
        }

        $email = $payload['email'] ?? $payload['sub'] ?? null;
        $ssoRole = $payload['role'] ?? $payload['roles'][0] ?? 'warga';

        if (!$email) {
            return response()->json([
                'status' => 'error',
                'message' => 'JWT payload does not contain user identity.',
                'errors' => null
            ], 401);
        }

        $localRole = match ($ssoRole) {
            'admin' => 'admin',
            'doctor', 'dokter' => 'doctor',
            default => 'patient',
        };

        LocalRole::updateOrCreate(
            ['sso_email' => $email],
            [
                'sso_role' => is_array($ssoRole) ? json_encode($ssoRole) : $ssoRole,
                'local_role' => $localRole,
            ]
        );

        $request->attributes->set('sso_user', [
            'email' => $email,
            'sso_role' => $ssoRole,
            'local_role' => $localRole,
            'payload' => $payload,
        ]);

        return $next($request);
    }
}