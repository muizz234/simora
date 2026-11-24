<?php

namespace App\Modules\Shared\Events;

use App\Models\DomainEvent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Mengirim event outbox ke transport yang dikonfigurasi.
 *
 * Transport "log" dipakai selama modul masih satu proses; transport "http"
 * menjadi jembatan ke broker/webhook saat modul mulai dipisah.
 */
final class DomainEventPublisher
{
    public function publish(DomainEvent $event): void
    {
        $payload = [
            'event_id' => $event->event_id,
            'type' => $event->type,
            'subject_type' => $event->subject_type,
            'subject_id' => $event->subject_id,
            'organization_id' => $event->organization_id,
            'payload' => $event->payload,
            'occurred_at' => $event->occurred_at?->toIso8601String(),
        ];

        if ((string) config('microservices.event_transport', 'log') === 'http') {
            $this->sendHttp($payload);
        } else {
            Log::info('domain-event.relayed', $payload);
        }

        $event->forceFill(['published_at' => now()])->save();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendHttp(array $payload): void
    {
        $webhook = (string) config('microservices.event_webhook');

        if ($webhook === '') {
            throw new RuntimeException('MICROSERVICE_EVENT_WEBHOOK belum dikonfigurasi.');
        }

        Http::acceptJson()
            ->connectTimeout(3)
            ->timeout((int) config('microservices.timeout', 5))
            ->post($webhook, $payload)
            ->throw();
    }
}
