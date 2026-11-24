<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\DomainEvent;
use App\Modules\Shared\Events\DomainEventProjector;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Penerima domain event dari service lain (kebalikan DomainEventPublisher).
 *
 * Event yang diterima disimpan sekali (idempoten berdasarkan event_id),
 * diproyeksikan ke read model lokal, lalu ditandai published_at agar command
 * domain-events:relay tidak mengirimnya kembali — mencegah loop antar service.
 */
class EventIngestController extends Controller
{
    public function store(Request $request, DomainEventProjector $projector): JsonResponse
    {
        $data = $request->validate([
            'event_id' => ['required', 'uuid'],
            'type' => ['required', 'string', 'max:255'],
            'subject_type' => ['nullable', 'string', 'max:255'],
            'subject_id' => ['nullable', 'integer'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'payload' => ['nullable', 'array'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $event = DB::transaction(function () use ($data, $projector): DomainEvent {
            $event = DomainEvent::firstOrCreate(
                ['event_id' => $data['event_id']],
                [
                    'type' => $data['type'],
                    'subject_type' => $data['subject_type'] ?? null,
                    'subject_id' => isset($data['subject_id']) ? (int) $data['subject_id'] : null,
                    'organization_id' => isset($data['organization_id']) ? (int) $data['organization_id'] : null,
                    'payload' => $data['payload'] ?? null,
                    'occurred_at' => isset($data['occurred_at']) ? Carbon::parse($data['occurred_at']) : now(),
                    'published_at' => now(),
                ],
            );

            if ($event->wasRecentlyCreated) {
                $projector->handle($event);
            }

            return $event;
        });

        return response()->json([
            'data' => [
                'id' => $event->id,
                'event_id' => $event->event_id,
                'duplicate' => ! $event->wasRecentlyCreated,
            ],
        ], $event->wasRecentlyCreated ? 201 : 200);
    }
}
