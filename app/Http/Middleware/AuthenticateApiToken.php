<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Autentikasi via header Authorization: Bearer <api_token>.
     * Token bersifat per akun dan menentukan role/scope aksesnya.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        $user = $token !== null
            ? User::query()->where('api_token', $token)->first()
            : null;

        if ($user === null) {
            return response()->json([
                'message' => 'Token API tidak valid atau belum ada.',
            ], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
