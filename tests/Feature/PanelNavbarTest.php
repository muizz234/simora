<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelNavbarTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_menu_moves_with_the_current_page_for_developer(): void
    {
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);
        $organization = Organization::factory()->create();
        $this->actingAs($developer);

        $this->get(route('panel'))
            ->assertOk()
            ->assertSee('aria-current="page"', false)
            ->assertSee('class="selected" href="'.route('panel').'"', false);

        $this->get(route('simora.organizations.index'))
            ->assertOk()
            ->assertSee('class="selected" href="'.route('simora.organizations.index').'"', false);

        $this->get(route('simora.organizations.create'))
            ->assertOk()
            ->assertSee('class="selected" href="'.route('simora.organizations.index').'"', false);

        $this->get(route('simora.organizations.edit', $organization))
            ->assertOk()
            ->assertSee('class="selected" href="'.route('simora.organizations.index').'"', false);

        $this->get(route('simora.accounts.index'))
            ->assertOk()
            ->assertSee('class="selected" href="'.route('simora.accounts.index').'"', false);

        $account = User::factory()->create();
        $this->get(route('simora.accounts.edit', $account))
            ->assertOk()
            ->assertSee('class="selected" href="'.route('simora.accounts.index').'"', false);

        $this->get(route('simora.orgmate'))
            ->assertOk()
            ->assertSee('class="selected" href="'.route('panel').'#workspace"', false);

        $this->get(route('simora.kalender-kerja'))->assertForbidden();
    }

    public function test_organization_menu_shows_kalender_kerja_instead_of_workspace(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ORGANIZATION]));

        $this->get(route('panel'))
            ->assertOk()
            ->assertSee('Kalender Kerja')
            ->assertSee(route('simora.kalender-kerja'))
            ->assertSee('Layanan &amp; Sistem Terintegrasi', false)
            ->assertSee('APLIKASI MANAGEMENT ORGANISASI')
            ->assertDontSee('Workspace')
            ->assertDontSee(route('simora.accounts.index'))
            ->assertDontSee(route('simora.organizations.index'));

        $this->get(route('simora.kalender-kerja'))
            ->assertOk()
            ->assertSee('Kalender Kerja')
            ->assertSee('class="selected" href="'.route('simora.kalender-kerja').'"', false);

        $this->get(route('simora.sentra'))
            ->assertOk()
            ->assertSee('Dashboard SENTRA')
            ->assertSee('Dashboard Keuangan')
            ->assertDontSee('Workspace');
    }
}
