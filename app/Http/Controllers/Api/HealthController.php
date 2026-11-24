<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Health check ringan untuk orchestrator, load balancer, dan service lain.
 *
 * Tidak memakai token: pemeriksa infrastruktur harus bisa mengaksesnya
 * sebelum kredensial aplikasi tersedia. Endpoint ini tidak membocorkan
 * detail koneksi, hanya status "ok" / "degraded".
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('select 1');
            $database = 'ok';
        } catch (Throwable) {
            $database = 'unavailable';
        }

        $status = $database === 'ok' ? 'ok' : 'degraded';

        return response()->json([
            'status' => $status,
            'service' => 'simora',
            'database' => $database,
            'timestamp' => now()->toIso8601String(),
        ], $status === 'ok' ? 200 : 503);
    }
}
