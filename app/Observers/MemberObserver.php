<?php

namespace App\Observers;

use App\Models\Member;
use App\Modules\Shared\Events\DomainEventType;
use App\Support\DomainEventRecorder;

class MemberObserver
{
    public function created(Member $member): void
    {
        DomainEventRecorder::forModel(DomainEventType::OrgmateMemberCreated, $member, $this->payload($member));
    }

    public function updated(Member $member): void
    {
        DomainEventRecorder::forModel(DomainEventType::OrgmateMemberUpdated, $member, $this->payload($member));
    }

    public function deleted(Member $member): void
    {
        DomainEventRecorder::forModel(DomainEventType::OrgmateMemberDeleted, $member, $this->payload($member));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Member $member): array
    {
        return [
            'name' => $member->name,
            'status' => $member->status,
            'division' => $member->division,
            'study_program' => $member->study_program,
        ];
    }
}
