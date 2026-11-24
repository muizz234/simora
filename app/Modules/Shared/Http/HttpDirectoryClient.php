<?php

namespace App\Modules\Shared\Http;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Konfigurasi HTTP bersama untuk seluruh direktori antar-service.
 *
 * Menghilangkan enam salinan pengaturan client dan memastikan kebijakan
 * timeout/retry yang sama berlaku untuk ORGMATE, E-PROGRESS, dan SENTRA.
 */
abstract class HttpDirectoryClient
{
    public function __construct(
        private readonly ?string $baseUrl,
        private readonly ?string $token,
        private readonly int $timeout = 5,
    ) {}

    protected function client(): PendingRequest
    {
        if ($this->baseUrl === null || $this->baseUrl === '') {
            throw new RuntimeException($this->missingUrlMessage());
        }

        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->acceptJson()
            ->withToken($this->token ?? '')
            ->connectTimeout(3)
            ->timeout($this->timeout)
            // Di Laravel versi ini, retry(n) = n total percobaan, bukan n retry.
            // Jadi 3 berarti 1 permintaan awal + 2 retry.
            ->retry(3, 100, function (Throwable $exception): bool {
                if ($exception instanceof ConnectionException) {
                    return true;
                }

                if ($exception instanceof RequestException) {
                    return $exception->response->serverError()
                        || $exception->response->status() === 429;
                }

                return false;
            });
    }

    abstract protected function missingUrlMessage(): string;
}
