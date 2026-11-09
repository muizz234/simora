<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Autentikasi via header Authorization: Bearer <api_token>.
     * Token disimpan sebagai hash sha256; fallback plaintext untuk
     * data/token lama yang belum dimigrasi.
     *
     * TODO(migrasi): setelah seluruh token lama ter-migrasi (mis. via
     * UserSeeder / AccountController baru yang sudah sha256) dan
     * ApiTokenTest diperbarui untuk tidak lagi mengandalkan plaintext,
     * HAPUS fallback plaintext di bawah ini. Jangan hapus sebelum itu
     * karena test lama masih mengharapkan token plaintext tetap diterima.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        $user = null;

        if ($token !== null) {
            $user = User::query()->where('api_token', hash('sha256', $token))->first();

            if ($user === null) {
                // Legacy: token lama tersimpan plaintext (mis. factory/test lama).
                $user = User::query()->where('api_token', $token)->first();

                if ($user !== null) {
                    // Migrasi oportunistik: langsung re-hash sekali agar
                    // fallback ini tidak terpicu lagi untuk user yang sama.
                    // Re-hash bersifat sekali-sejalan: setelah tersimpan
                    // sha256, request berikutnya masuk jalur hash dan tidak
                    // lagi memicu log ini (bukan log per-request).
                    $user->forceFill(['api_token' => hash('sha256', $token)])->saveQuietly();
                    Log::warning('Legacy plaintext api_token di-rehash ke sha256.', [
                        'user_id' => $user->id,
                    ]);
                }
            }
        }

        if ($user === null) {
            return response()->json([
                'message' => 'Token API tidak valid atau belum ada.',
            ], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
