<?php

namespace App\Observers;

use App\Models\Position;
use App\Modules\Shared\Events\DomainEventType;
use App\Support\DomainEventRecorder;

class PositionObserver
{
    public function created(Position $position): void
    {
        DomainEventRecorder::forModel(DomainEventType::OrgmatePositionCreated, $position, $this->payload($position));
    }

    public function updated(Position $position): void
    {
        DomainEventRecorder::forModel(DomainEventType::OrgmatePositionUpdated, $position, $this->payload($position));
    }

    public function deleted(Position $position): void
    {
        DomainEventRecorder::forModel(DomainEventType::OrgmatePositionDeleted, $position, $this->payload($position));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Position $position): array
    {
        return [
            'name' => $position->name,
            'group_type' => $position->group_type,
            'member_id' => $position->member_id,
            'sort_order' => $position->sort_order,
        ];
    }
}
