<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_link_appears_on_landing_only_after_organization_saves_it(): void
    {
        $organization = Organization::factory()->create([
            'name' => 'HIMA-TI',
            'registration_url' => null,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Link pendaftaran belum tersedia');

        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->put(route('simora.my-registration.update'), [
                'registration_url' => 'https://example.com/daftar-hima-ti',
            ])
            ->assertRedirect(route('panel'));

        $this->assertSame('https://example.com/daftar-hima-ti', $organization->fresh()->registration_url);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://example.com/daftar-hima-ti', false)
            ->assertSee('Link Pendaftaran', false);
    }

    public function test_organization_can_only_update_its_own_registration_link(): void
    {
        $own = Organization::factory()->create(['registration_url' => null]);
        $other = Organization::factory()->create(['registration_url' => null]);
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);

        $this->actingAs($account)
            ->put(route('simora.my-registration.update'), [
                'registration_url' => 'https://example.com/daftar',
            ])
            ->assertRedirect(route('panel'));

        $this->assertNull($other->fresh()->registration_url);
    }

    public function test_developer_and_unlinked_organization_cannot_use_self_service_registration(): void
    {
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);
        $unlinked = User::factory()->create(['role' => User::ROLE_ORGANIZATION, 'organization_id' => null]);

        $this->actingAs($developer)
            ->put(route('simora.my-registration.update'), ['registration_url' => 'https://example.com/x'])
            ->assertForbidden();

        $this->actingAs($unlinked)
            ->put(route('simora.my-registration.update'), ['registration_url' => 'https://example.com/x'])
            ->assertForbidden();
    }
}
