<?php

namespace Tests\Unit;

use App\Models\Organization;
use App\Models\User;
use App\Models\WorkProgram;
use App\Support\OrganizationScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tests\TestCase;

class OrganizationScopeUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_for_user_scopes_organization_role_and_passes_developer_through(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        WorkProgram::factory()->create(['organization_id' => $own->id]);
        WorkProgram::factory()->create(['organization_id' => $other->id]);

        $orgUser = User::factory()->create(['role' => User::ROLE_ORGANIZATION, 'organization_id' => $own->id]);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $scoped = OrganizationScope::forUser(WorkProgram::query(), $orgUser)->pluck('organization_id')->all();
        $this->assertSame([$own->id], $scoped);

        $all = OrganizationScope::forUser(WorkProgram::query(), $developer)->count();
        $this->assertSame(2, $all);
    }

    public function test_assert_can_access_organization(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $orgUser = User::factory()->create(['role' => User::ROLE_ORGANIZATION, 'organization_id' => $own->id]);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        OrganizationScope::assertCanAccessOrganization($orgUser, $own->id);
        OrganizationScope::assertCanAccessOrganization($developer, $other->id);
        OrganizationScope::assertCanAccessOrganization($developer, null);
        $this->addToAssertionCount(3);

        foreach ([$other->id, null] as $foreignId) {
            try {
                OrganizationScope::assertCanAccessOrganization($orgUser, $foreignId);
                $this->fail('Seharusnya melempar AccessDeniedHttpException.');
            } catch (AccessDeniedHttpException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_organization_id_for(): void
    {
        $organization = Organization::factory()->create();
        $orgUser = User::factory()->create(['role' => User::ROLE_ORGANIZATION, 'organization_id' => $organization->id]);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);
        $orphan = User::factory()->create(['role' => User::ROLE_ORGANIZATION, 'organization_id' => null]);

        $this->assertSame($organization->id, OrganizationScope::organizationIdFor($orgUser, 999));
        $this->assertNull(OrganizationScope::organizationIdFor($orphan));
        $this->assertSame(999, OrganizationScope::organizationIdFor($developer, 999));
        $this->assertNull(OrganizationScope::organizationIdFor($developer));
    }
}
