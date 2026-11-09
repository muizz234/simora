<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use App\Models\WorkProgram;
use App\Support\OrganizationScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tests\TestCase;

class OrganizationScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_role_is_scoped_to_its_own_organization_only(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $user = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);

        $ids = OrganizationScope::forUser(WorkProgram::query(), $user)
            ->get()
            ->pluck('organization_id')
            ->all();

        $this->assertNotContains($other->id, $ids);
    }

    public function test_organization_role_cannot_access_another_organization(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $user = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);

        $this->expectException(AccessDeniedHttpException::class);

        OrganizationScope::assertCanAccessOrganization($user, $other->id);
    }

    public function test_developer_can_access_every_organization(): void
    {
        $organization = Organization::factory()->create();
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        OrganizationScope::assertCanAccessOrganization($developer, $organization->id);
        $this->assertNull(OrganizationScope::organizationIdFor($developer));
        $this->assertSame($organization->id, OrganizationScope::organizationIdFor($developer, $organization->id));
        $this->assertSame($organization->id, OrganizationScope::organizationIdFor(
            User::factory()->create(['role' => User::ROLE_ORGANIZATION, 'organization_id' => $organization->id]),
            999
        ));
    }
}
