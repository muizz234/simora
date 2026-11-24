<?php

namespace App\Console\Commands;

use App\Models\DomainEvent;
use App\Modules\Shared\Events\DomainEventPublisher;
use App\Modules\Shared\Events\EventPublishCircuitBreaker;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

#[Signature('domain-events:relay {--limit=100 : Maksimal event yang diproses}')]
#[Description('Relay event domain yang belum terkirim dari outbox domain_events.')]
class RelayDomainEvents extends Command
{
    public function handle(DomainEventPublisher $publisher, EventPublishCircuitBreaker $circuitBreaker): int
    {
        if ($circuitBreaker->isOpen()) {
            $this->warn('Circuit breaker outbox sedang OPEN; relay dilewati.');

            return self::SUCCESS;
        }

        $limit = max(1, (int) $this->option('limit'));

        $events = DomainEvent::query()
            ->whereNull('published_at')
            ->whereNull('dead_lettered_at')
            ->where(function ($query): void {
                $query->whereNull('next_attempt_at')
                    ->orWhere('next_attempt_at', '<=', now());
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $relayed = 0;
        $retried = 0;
        $deadLettered = 0;

        foreach ($events as $event) {
            try {
                $publisher->publish($event);
                $circuitBreaker->recordSuccess();
                $relayed++;
            } catch (Throwable $exception) {
                $circuitBreaker->recordFailure();
                $attempts = ((int) $event->attempts) + 1;
                $maxAttempts = max(1, (int) config('microservices.outbox.max_attempts', 5));
                $isDead = $attempts >= $maxAttempts;

                $event->forceFill([
                    'attempts' => $attempts,
                    'last_error' => Str::limit($exception->getMessage(), 1000),
                    'next_attempt_at' => $isDead
                        ? null
                        : now()->addSeconds($this->backoffSeconds($attempts)),
                    'dead_lettered_at' => $isDead ? now() : null,
                ])->save();

                Log::warning('domain-event.relay.failed', [
                    'event_id' => $event->event_id,
                    'type' => $event->type,
                    'attempts' => $attempts,
                    'dead_lettered' => $isDead,
                    'error' => $exception->getMessage(),
                ]);

                $isDead ? $deadLettered++ : $retried++;
            }
        }

        $this->info("Relayed {$relayed}; retried {$retried}; dead-lettered {$deadLettered}.");

        return self::SUCCESS;
    }

    private function backoffSeconds(int $attempts): int
    {
        $base = max(1, (int) config('microservices.outbox.backoff_seconds', 30));

        return $base * (2 ** min($attempts - 1, 4));
    }
}
