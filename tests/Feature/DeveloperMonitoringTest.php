<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeveloperMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_developer_sees_monitoring_sidebar_and_org_selector_in_sentra_and_orgmate(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI', 'slug' => 'hima-ti']);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.sentra'))
            ->assertOk()
            ->assertSee('Monitoring')
            ->assertSee('Monitoring Keuangan')
            ->assertSee(route('simora.sentra', ['organization' => 'hima-ti']), false);

        $this->actingAs($developer)
            ->get(route('simora.sentra.kas', ['organization' => 'hima-ti']))
            ->assertOk()
            ->assertSee('Organisasi:')
            ->assertSee('value="hima-ti" selected', false);

        $this->actingAs($developer)
            ->get(route('simora.orgmate'))
            ->assertOk()
            ->assertSee('Monitoring')
            ->assertSee('Monitoring Organisasi')
            ->assertSee(route('simora.orgmate', ['organization' => 'hima-ti']), false);
    }

    public function test_organization_role_does_not_see_developer_monitoring_menus_or_org_selector(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI', 'slug' => 'hima-ti']);
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->get(route('simora.sentra.kas'))
            ->assertOk()
            ->assertDontSee('Monitoring')
            ->assertDontSee('name="organization"');

        $this->actingAs($account)
            ->get(route('simora.orgmate'))
            ->assertOk()
            ->assertDontSee('Monitoring');
    }

    public function test_organization_role_query_with_another_org_slug_is_ignored(): void
    {
        $own = Organization::factory()->create(['name' => 'HIMA-TI', 'slug' => 'hima-ti']);
        $other = Organization::factory()->create(['name' => 'HIMATRO', 'slug' => 'himatro']);
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);

        // Role organization selalu terikat ke organisasi login, slug lain diabaikan.
        $this->actingAs($account)
            ->get(route('simora.sentra.kas', ['organization' => 'himatro']))
            ->assertOk()
            ->assertDontSee('name="organization"');
    }
}
