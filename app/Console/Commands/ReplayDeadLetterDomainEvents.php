<?php

namespace App\Console\Commands;

use App\Models\DomainEvent;
use App\Modules\Shared\Events\EventPublishCircuitBreaker;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('domain-events:replay-dead-letter {--limit=50 : Maksimal event dead-letter yang dikembalikan ke antrian}')]
#[Description('Kembalikan event yang gagal permanen ke antrean relay setelah perbaikan.')]
class ReplayDeadLetterDomainEvents extends Command
{
    public function handle(EventPublishCircuitBreaker $circuitBreaker): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $events = DomainEvent::query()
            ->whereNotNull('dead_lettered_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($events as $event) {
            $event->forceFill([
                'dead_lettered_at' => null,
                'attempts' => 0,
                'next_attempt_at' => null,
                'last_error' => null,
            ])->save();
        }

        // Operator hanya replay setelah tujuan diperbaiki, jadi STATUS circuit
        // ikut dikembalikan agar relay tidak langsung dilewati.
        if ($events->isNotEmpty()) {
            $circuitBreaker->recordSuccess();
        }

        $this->info("Requeued {$events->count()} dead-lettered event(s).");

        return self::SUCCESS;
    }
}
