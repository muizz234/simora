<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_kontak_shows_cards_with_only_configured_socials(): void
    {
        $withSocials = Organization::factory()->create([
            'name' => 'HIMA-TI',
            'instagram_url' => 'https://instagram.com/himati',
            'whatsapp_url' => 'https://wa.me/6281234567890',
        ]);

        Organization::factory()->create(['name' => 'HIMATIKA']);

        $this->get(route('kontak'))
            ->assertOk()
            ->assertSee('Kontak')
            ->assertSee('HIMA-TI')
            ->assertSee('https://instagram.com/himati', false)
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertDontSee('https://tiktok.com/@himati', false)
            ->assertSee('Kontak belum tersedia');
    }

    public function test_organization_can_set_which_socials_to_show(): void
    {
        $organization = Organization::factory()->create(['instagram_url' => null]);
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->get(route('panel'))
            ->assertOk()
            ->assertSee('Kontak Sosial Media');

        $this->put(route('simora.my-socials.update'), [
            'instagram_url' => 'https://instagram.com/baru',
            'tiktok_url' => 'https://tiktok.com/@baru',
            'youtube_url' => null,
            'whatsapp_url' => null,
        ])->assertRedirect(route('panel'));

        $organization->refresh();
        $this->assertSame('https://instagram.com/baru', $organization->instagram_url);
        $this->assertSame('https://tiktok.com/@baru', $organization->tiktok_url);
        $this->assertNull($organization->youtube_url);

        $this->get(route('kontak'))
            ->assertOk()
            ->assertSee('https://instagram.com/baru', false)
            ->assertSee('https://tiktok.com/@baru', false);
    }

    public function test_developer_can_set_socials_via_organization_crud(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_DEVELOPER]));

        $this->post(route('simora.organizations.store'), [
            'name' => 'HIMA Uji',
            'slug' => 'hima-uji',
            'full_name' => 'Himpunan Mahasiswa Uji',
            'banner_color' => '#171717',
            'accent_color' => '#3687ff',
            'focus' => 'Teknik Uji',
            'description' => 'Uji.',
            'instagram_url' => 'https://instagram.com/uji',
            'tiktok_url' => 'https://tiktok.com/@uji',
            'youtube_url' => 'https://youtube.com/@uji',
            'whatsapp_url' => 'https://wa.me/6280000000000',
            'logo' => \Illuminate\Http\UploadedFile::fake()->image('logo.png'),
        ])->assertRedirect(route('simora.organizations.index'));

        $this->assertDatabaseHas('organizations', [
            'name' => 'HIMA Uji',
            'instagram_url' => 'https://instagram.com/uji',
            'whatsapp_url' => 'https://wa.me/6280000000000',
        ]);
    }
}
