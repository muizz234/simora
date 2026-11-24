<?php

namespace App\Modules\Shared\Events;

use Illuminate\Support\Facades\Cache;

/**
 * Circuit breaker untuk relay outbox.
 *
 * Setelah beberapa kegagalan berturut-turut, relay berhenti mencoba selama
 * cooldown agar service yang sedang sakit tidak dihujani retry. Setelah
 * cooldown, tepat satu percobaan diperbolehkan (half-open).
 */
final class EventPublishCircuitBreaker
{
    private const CACHE_KEY = 'microservices:outbox:circuit';

    private const STATUS_CLOSED = 'closed';

    private const STATUS_OPEN = 'open';

    private const STATUS_HALF_OPEN = 'half_open';

    public function isOpen(): bool
    {
        $state = Cache::get(self::CACHE_KEY);

        if (! is_array($state)) {
            return false;
        }

        $status = (string) ($state['status'] ?? self::STATUS_CLOSED);

        if ($status === self::STATUS_HALF_OPEN) {
            return true;
        }

        if ($status !== self::STATUS_OPEN) {
            return false;
        }

        if (now()->timestamp < (int) ($state['retry_at'] ?? 0)) {
            return true;
        }

        Cache::forever(self::CACHE_KEY, [
            'status' => self::STATUS_HALF_OPEN,
            'failures' => (int) ($state['failures'] ?? 0),
            'retry_at' => now()->timestamp + $this->cooldownSeconds(),
        ]);

        return false;
    }

    public function recordSuccess(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function recordFailure(): void
    {
        $state = Cache::get(self::CACHE_KEY, []);
        $failures = (int) (is_array($state) ? ($state['failures'] ?? 0) : 0) + 1;
        $threshold = max(1, (int) config('microservices.circuit_breaker.failure_threshold', 5));

        if ($failures >= $threshold) {
            Cache::forever(self::CACHE_KEY, [
                'status' => self::STATUS_OPEN,
                'failures' => $failures,
                'retry_at' => now()->timestamp + $this->cooldownSeconds(),
            ]);

            return;
        }

        Cache::forever(self::CACHE_KEY, [
            'status' => self::STATUS_CLOSED,
            'failures' => $failures,
            'retry_at' => 0,
        ]);
    }

    public function status(): string
    {
        $state = Cache::get(self::CACHE_KEY);

        return is_array($state) ? (string) ($state['status'] ?? self::STATUS_CLOSED) : self::STATUS_CLOSED;
    }

    private function cooldownSeconds(): int
    {
        return max(1, (int) config('microservices.circuit_breaker.cooldown_seconds', 60));
    }
}
