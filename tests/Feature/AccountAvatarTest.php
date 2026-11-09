<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_developer_can_create_an_account_with_a_profile_photo(): void
    {
        Storage::fake('public');
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);
        $organization = Organization::factory()->create();

        $this->actingAs($developer)
            ->post(route('simora.accounts.store'), [
                'name' => 'Ketua HIMA-TI',
                'email' => 'ketua@example.com',
                'password' => 'password123',
                'role' => User::ROLE_ORGANIZATION,
                'organization_id' => $organization->id,
                'avatar' => UploadedFile::fake()->image('ketua.jpg'),
            ])
            ->assertRedirect(route('simora.accounts.index'));

        $account = User::where('email', 'ketua@example.com')->firstOrFail();

        $this->assertNotNull($account->avatar_path);
        Storage::disk('public')->assertExists($account->avatar_path);
        $this->assertNotNull($account->api_token);
    }

    public function test_developer_can_replace_an_accounts_profile_photo_and_the_old_file_is_deleted(): void
    {
        Storage::fake('public');
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'avatar_path' => 'avatars/lama.jpg',
        ]);
        Storage::disk('public')->put('avatars/lama.jpg', 'isi-lama');

        $this->actingAs($developer)
            ->put(route('simora.accounts.update', $account), [
                'name' => $account->name,
                'email' => $account->email,
                'role' => User::ROLE_ORGANIZATION,
                'organization_id' => null,
                'avatar' => UploadedFile::fake()->image('baru.jpg'),
            ])
            ->assertRedirect(route('simora.accounts.index'));

        $account->refresh();

        $this->assertStringStartsWith('avatars/', $account->avatar_path);
        Storage::disk('public')->assertExists($account->avatar_path);
        Storage::disk('public')->assertMissing('avatars/lama.jpg');
    }

    public function test_uploading_an_avatar_for_an_organization_account_also_updates_the_organization_logo(): void
    {
        Storage::fake('public');
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);
        $organization = Organization::factory()->create(['logo_path' => 'assets/himati.jpg']);
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
            'avatar_path' => 'assets/himati.jpg',
        ]);

        $this->actingAs($developer)
            ->put(route('simora.accounts.update', $account), [
                'name' => $account->name,
                'email' => $account->email,
                'role' => User::ROLE_ORGANIZATION,
                'organization_id' => $organization->id,
                'avatar' => UploadedFile::fake()->image('logo-baru.png'),
            ])
            ->assertRedirect(route('simora.accounts.index'));

        $organization->refresh();
        $account->refresh();

        $this->assertSame($account->avatar_path, $organization->logo_path);
        Storage::disk('public')->assertExists($organization->logo_path);
        // Avatar akun = logo organisasi (satu foto yang sama).
        $this->assertSame($organization->logo_url, $account->avatar_url);
    }

    public function test_account_form_rejects_a_non_image_avatar(): void
    {
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->post(route('simora.accounts.store'), [
                'name' => 'Akun Tes',
                'email' => 'tes@example.com',
                'password' => 'password123',
                'role' => 'user',
                'organization_id' => null,
                'avatar' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('avatar');
    }

    public function test_account_form_without_a_photo_keeps_the_initials_fallback(): void
    {
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.accounts.create'))
            ->assertOk()
            ->assertSee('Foto profil (avatar)');
    }
}
