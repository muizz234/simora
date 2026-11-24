<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Membatasi internal API hanya untuk service lain yang memegang token.
 *
 * Mendukung rotasi: token lama tetap diterima sampai batas waktu yang
 * ditentukan, sehingga deployment token baru tidak menyebabkan downtime.
 */
class EnsureServiceToken
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $provided = (string) ($request->header('X-Service-Token') ?? $request->bearerToken() ?? '');

        if ($provided === '') {
            abort(Response::HTTP_UNAUTHORIZED, 'Token antar-service tidak valid.');
        }

        $credentials = [
            [
                'secret' => config('microservices.token'),
                'expires_at' => config('microservices.token_expires_at'),
            ],
            [
                'secret' => config('microservices.token_previous'),
                'expires_at' => config('microservices.token_previous_expires_at'),
            ],
        ];

        foreach ($credentials as $credential) {
            $secret = $credential['secret'];

            if (! is_string($secret) || $secret === '') {
                continue;
            }

            if (! $this->isActive($credential['expires_at'])) {
                continue;
            }

            if (hash_equals($secret, $provided)) {
                return $next($request);
            }
        }

        abort(Response::HTTP_UNAUTHORIZED, 'Token antar-service tidak valid.');
    }

    private function isActive(mixed $expiresAt): bool
    {
        if ($expiresAt === null || $expiresAt === '') {
            return true;
        }

        try {
            return now()->lt(Carbon::parse((string) $expiresAt));
        } catch (Throwable) {
            return false;
        }
    }
}
