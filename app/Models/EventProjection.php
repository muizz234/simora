<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Salinan data milik konsumen event (read model).
 *
 * Baris dibuat/diperbarui oleh DomainEventProjector saat event diterima,
 * sehingga service konsumen tidak perlu membaca database service sumber.
 */
#[Fillable([
    'subject_type',
    'subject_id',
    'organization_id',
    'label',
    'payload',
    'last_event_at',
])]
class EventProjection extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'last_event_at' => 'datetime',
        ];
    }
}
