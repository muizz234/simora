<?php

namespace App\Modules\Shared\Events;

use App\Models\DomainEvent;
use App\Models\EventProjection;

/**
 * Mengubah event yang diterima menjadi read model lokal (projection).
 *
 * Pembaruan bersifat idempoten dan berurutan berdasarkan occurred_at: event
 * lama yang diterima terlambat tidak boleh menimpa keadaan terbaru.
 */
final class DomainEventProjector
{
    /**
     * @return array<int, string>
     */
    private const LABEL_KEYS = ['name', 'title', 'program_name', 'full_name'];

    public function handle(DomainEvent $event): void
    {
        if ($event->subject_type === null || $event->subject_id === null) {
            return;
        }

        if ($this->isRemoval($event->type)) {
            EventProjection::query()
                ->where('subject_type', $event->subject_type)
                ->where('subject_id', $event->subject_id)
                ->delete();

            return;
        }

        $occurredAt = $event->occurred_at ?? $event->created_at ?? now();

        $existing = EventProjection::query()
            ->where('subject_type', $event->subject_type)
            ->where('subject_id', $event->subject_id)
            ->first();

        if ($existing !== null && $existing->last_event_at->greaterThanOrEqualTo($occurredAt)) {
            return;
        }

        EventProjection::query()->updateOrCreate(
            [
                'subject_type' => $event->subject_type,
                'subject_id' => $event->subject_id,
            ],
            [
                'organization_id' => $event->organization_id,
                'label' => $this->labelFrom($event->payload),
                'payload' => $event->payload,
                'last_event_at' => $occurredAt,
            ],
        );
    }

    private function isRemoval(string $type): bool
    {
        return str_ends_with($type, '.deleted') || str_ends_with($type, '.removed');
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function labelFrom(?array $payload): ?string
    {
        if ($payload === null) {
            return null;
        }

        foreach (self::LABEL_KEYS as $key) {
            $value = $payload[$key] ?? null;

            if (is_scalar($value) && (string) $value !== '') {
                return mb_substr((string) $value, 0, 255);
            }
        }

        return null;
    }
}
