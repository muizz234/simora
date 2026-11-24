<?php

namespace App\Observers;

use App\Models\User;
use App\Modules\Shared\Events\DomainEventType;
use App\Support\DomainEventRecorder;

class UserObserver
{
    public function created(User $user): void
    {
        DomainEventRecorder::forModel(DomainEventType::UserRegistered, $user, [
            'name' => $user->name,
            'role' => $user->role,
        ]);
    }

    public function updated(User $user): void
    {
        if (! $user->wasChanged('role') && ! $user->wasChanged('organization_id') && ! $user->wasChanged('avatar_path')) {
            return;
        }

        DomainEventRecorder::forModel(DomainEventType::UserUpdated, $user, [
            'name' => $user->name,
            'role' => $user->role,
        ]);
    }

    public function deleted(User $user): void
    {
        DomainEventRecorder::forModel(DomainEventType::UserRemoved, $user, [
            'name' => $user->name,
            'role' => $user->role,
        ]);
    }
}
