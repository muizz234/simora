<?php

namespace App\Support;

use App\Models\DomainEvent;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;

/**
 * Mencatat event domain ke tabel outbox (domain_events) untuk sinkronisasi
 * antar modul ORGMATE, E-PROGRESS, dan SENTRA.
 */
class DomainEventRecorder
{
    /**
     * @param  class-string|null  $subjectType
     * @param  array<string, mixed>|null  $payload
     */
    public static function record(string $type, ?string $subjectType = null, ?int $subjectId = null, ?int $organizationId = null, ?array $payload = null): DomainEvent
    {
        return DomainEvent::create([
            'type' => $type,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'organization_id' => $organizationId,
            'payload' => $payload,
            'occurred_at' => now(),
        ]);
    }

    /**
     * Catat event untuk model Eloquent ( subject + organisasi diambil dari model).
     *
     * @param  array<string, mixed>|null  $payload
     */
    public static function forModel(string $type, Model $model, ?array $payload = null): DomainEvent
    {
        $organizationId = null;

        if ($model instanceof Organization) {
            $organizationId = $model->getKey() !== null ? (int) $model->getKey() : null;
        } elseif (isset($model->organization_id) && $model->organization_id !== null) {
            $organizationId = (int) $model->organization_id;
        }

        return self::record($type, $model::class, $model->getKey(), $organizationId, $payload);
    }
}
