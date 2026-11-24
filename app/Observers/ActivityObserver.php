<?php

namespace App\Observers;

use App\Models\Activity;
use App\Modules\Shared\Events\DomainEventType;
use App\Support\DomainEventRecorder;

class ActivityObserver
{
    public function created(Activity $activity): void
    {
        DomainEventRecorder::forModel(DomainEventType::EProgressActivityCreated, $activity, $this->payload($activity));
    }

    public function updated(Activity $activity): void
    {
        DomainEventRecorder::forModel(DomainEventType::EProgressActivityUpdated, $activity, $this->payload($activity));
    }

    public function deleted(Activity $activity): void
    {
        DomainEventRecorder::forModel(DomainEventType::EProgressActivityDeleted, $activity, $this->payload($activity));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Activity $activity): array
    {
        return [
            'name' => $activity->name,
            'work_program_id' => $activity->work_program_id,
            'status' => $activity->status,
            'start_at' => $activity->start_at?->toIso8601String(),
            'end_at' => $activity->end_at?->toIso8601String(),
        ];
    }
}
