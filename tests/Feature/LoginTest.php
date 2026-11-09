<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_the_empty_panel(): void
    {
        $this->get(route('panel'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_sign_in_and_reach_the_empty_panel(): void
    {
        $user = User::factory()->create([
            'email' => 'mahasiswa@example.test',
            'password' => 'secret-password',
            'role' => User::ROLE_DEVELOPER,
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'secret-password',
        ])
            ->assertRedirect(route('panel'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('panel'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('Dashboard')
            ->assertSee('ORGMATE')
            ->assertSee('E-PROGRESS')
            ->assertSee('SENTRA');
    }

    public function test_invalid_credentials_return_to_login_with_an_error(): void
    {
        User::factory()->create(['email' => 'mahasiswa@example.test']);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'mahasiswa@example.test',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_can_sign_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_authenticated_user_can_open_each_module_dashboard(): void
    {
        foreach ([User::ROLE_DEVELOPER, User::ROLE_ORGANIZATION] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));

            foreach ([
                'simora.orgmate' => ['ORGMATE', 'Dashboard Organisasi'],
                'simora.e-progress' => ['E-PROGRESS', 'Dashboard Kegiatan'],
                'simora.sentra' => ['SENTRA', 'Dashboard Keuangan'],
            ] as $routeName => [$moduleName, $dashboardTitle]) {
                // Assertion struktural: status + identitas modul + judul dashboard
                // aktual (bukan pola rapuh "Dashboard {KODE}").
                $this->get(route($routeName))
                    ->assertOk()
                    ->assertSee($moduleName)
                    ->assertSee($dashboardTitle);
            }
        }
    }

    public function test_users_without_a_management_role_cannot_open_management_dashboards(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get(route('panel'))
            ->assertForbidden();

        $this->get(route('simora.orgmate'))->assertForbidden();
    }

    public function test_organization_menu_is_available_only_to_developers(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_DEVELOPER]))
            ->get(route('panel'))
            ->assertOk()
            ->assertSee('Organisasi')
            ->assertSee(route('simora.organizations.index'));

        $this->actingAs(User::factory()->create(['role' => User::ROLE_ORGANIZATION]))
            ->get(route('panel'))
            ->assertOk()
            ->assertDontSee(route('simora.organizations.index'));

        $this->get(route('simora.organizations.index'))->assertForbidden();
    }

    public function test_developer_can_create_update_and_delete_organizations_with_a_logo(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => User::ROLE_DEVELOPER]));
        $this->get(route('simora.organizations.create'))->assertOk()->assertSee('Tambah Organisasi');

        $this->post(route('simora.organizations.store'), [
            'name' => 'HIMA Uji',
            'slug' => 'hima-uji',
            'full_name' => 'Himpunan Mahasiswa Uji',
            'banner_color' => '#171717',
            'accent_color' => '#3687ff',
            'focus' => 'Teknik Uji',
            'period' => '2025/2026',
            'registration_url' => 'https://example.com/daftar',
            'description' => 'Organisasi untuk pengujian fitur.',
            'vision' => 'Menjadi organisasi yang berkembang.',
            'mission' => "Berinovasi\nBerkolaborasi",
            'sort_order' => 4,
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertRedirect(route('simora.organizations.index'));

        $organization = Organization::query()->where('slug', 'hima-uji')->firstOrFail();
        $this->get(route('simora.organizations.index'))->assertOk()->assertSee('HIMA Uji');
        $this->get(route('simora.organizations.edit', $organization))->assertOk()->assertSee('Edit HIMA Uji');
        $originalLogoPath = $organization->logo_path;
        $this->assertDatabaseHas('organizations', [
            'name' => 'HIMA Uji',
            'period' => '2025/2026',
            'vision' => 'Menjadi organisasi yang berkembang.',
        ]);
        $this->get(route('simora.organizations.index'))->assertOk()->assertSee('Periode · 2025/2026');
        Storage::disk('public')->assertExists($originalLogoPath);

        $this->put(route('simora.organizations.update', $organization), [
            'name' => 'HIMA Uji Baru',
            'slug' => 'hima-uji',
            'full_name' => 'Himpunan Mahasiswa Uji Baru',
            'banner_color' => '#082B65',
            'accent_color' => '#168cff',
            'focus' => 'Teknik Elektro',
            'period' => '2026/2027',
            'registration_url' => 'https://example.com/daftar-baru',
            'description' => 'Deskripsi organisasi diperbarui.',
            'vision' => 'Visi baru.',
            'mission' => 'Misi baru.',
            'sort_order' => 2,
        ])->assertRedirect(route('simora.organizations.index'));

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'HIMA Uji Baru',
            'period' => '2026/2027',
            'registration_url' => 'https://example.com/daftar-baru',
            'mission' => 'Misi baru.',
        ]);
        $this->assertSame($originalLogoPath, $organization->fresh()->logo_path);

        $this->delete(route('simora.organizations.destroy', $organization))
            ->assertRedirect(route('simora.organizations.index'));

        $this->assertDatabaseMissing('organizations', ['id' => $organization->id]);
        Storage::disk('public')->assertMissing($originalLogoPath);
    }

    public function test_landing_page_displays_organizations_from_the_database(): void
    {
        Organization::factory()->create([
            'name' => 'HIMA Dinamis',
            'slug' => 'hima-dinamis',
            'full_name' => 'Himpunan Mahasiswa Dinamis',
            'logo_path' => 'assets/himati.jpg',
            'vision' => 'Visi organisasi dinamis.',
            'mission' => 'Misi organisasi dinamis.',
            'period' => '2025/2026',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('HIMA Dinamis')
            ->assertSee('Himpunan Mahasiswa Dinamis')
            ->assertSee('PERIODE · 2025/2026')
            ->assertSee('Visi organisasi dinamis.')
            ->assertSee('Misi organisasi dinamis.');
    }
}
