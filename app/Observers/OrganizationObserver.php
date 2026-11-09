<?php

namespace App\Observers;

use App\Models\Organization;
use App\Support\DomainEventRecorder;

class OrganizationObserver
{
    public function created(Organization $organization): void
    {
        DomainEventRecorder::forModel('organization.created', $organization, [
            'name' => $organization->name,
            'slug' => $organization->slug,
        ]);
    }

    public function updated(Organization $organization): void
    {
        DomainEventRecorder::forModel('organization.updated', $organization, [
            'name' => $organization->name,
            'slug' => $organization->slug,
            'vision' => $organization->vision,
            'mission' => $organization->mission,
            'registration_url' => $organization->registration_url,
        ]);
    }

    public function deleted(Organization $organization): void
    {
        // organization_id sengaja null agar event tetap tersimpan
        // setelah organisasi dihapus (FK nullOnDelete).
        DomainEventRecorder::record('organization.deleted', Organization::class, $organization->getKey(), null, [
            'name' => $organization->name,
            'slug' => $organization->slug,
        ]);
    }
}
