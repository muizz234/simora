<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_developer_sees_landing_management_menu_and_can_update_footer(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_DEVELOPER]));

        $this->get(route('panel'))
            ->assertOk()
            ->assertSee('Kelola Landing')
            ->assertSee(route('simora.landing-management'), false);

        $this->get(route('simora.landing-management'))
            ->assertOk()
            ->assertSee('Kelola Landing')
            ->assertSee('identity_text', false);

        $this->put(route('simora.landing-management.update'), [
            'identity_text' => 'Universitas Nurul Jadid',
            'tagline' => 'Kolaborasi • Inovasi • Prestasi',
            'instagram_url' => 'https://instagram.com/ftunuja',
            'tiktok_url' => 'https://tiktok.com/@ftunuja',
            'website_url' => 'https://unuja.ac.id',
        ])->assertRedirect(route('simora.landing-management'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Universitas Nurul Jadid')
            ->assertSee('https://instagram.com/ftunuja', false)
            ->assertDontSee('>ig<', false)
            ->assertDontSee('Kolaborasi • Inovasi • Prestasi');
    }

    public function test_organization_cannot_open_landing_management(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ORGANIZATION]));

        $this->get(route('simora.landing-management'))->assertForbidden();
    }
}
