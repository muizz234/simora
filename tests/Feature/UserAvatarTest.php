<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_avatar_has_null_avatar_url(): void
    {
        $user = User::factory()->create(['avatar_path' => null]);

        $this->assertNull($user->avatar_url);
    }

    public function test_user_with_asset_avatar_resolves_public_url(): void
    {
        $user = User::factory()->create(['avatar_path' => 'assets/SIMORA.png']);

        $this->assertStringStartsWith(asset('assets/SIMORA.png'), $user->avatar_url);
    }

    public function test_user_with_uploaded_avatar_resolves_storage_url(): void
    {
        $user = User::factory()->create(['avatar_path' => 'avatars/member.jpg']);

        $this->assertStringStartsWith(url('storage/avatars/member.jpg'), $user->avatar_url);
    }

    public function test_organization_account_avatar_is_the_organization_logo(): void
    {
        $organization = Organization::factory()->create(['logo_path' => 'assets/himati.jpg']);
        $user = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
            'avatar_path' => 'avatars/foto-lain.jpg',
        ]);

        $this->assertSame($organization->logo_url, $user->avatar_url);

        // Logo organisasi berubah -> avatar akun ikut berubah (satu foto yang sama).
        $organization->update(['logo_path' => 'assets/himatro.jpg']);

        $this->assertSame($organization->fresh()->logo_url, $user->fresh()->avatar_url);
    }

    public function test_seeder_creates_one_organization_account_per_organization_with_its_logo_as_avatar(): void
    {
        $organization = Organization::factory()->create([
            'name' => 'HIMA-TI',
            'slug' => 'hima-ti',
            'logo_path' => 'assets/himati.jpg',
        ]);
        $other = Organization::factory()->create([
            'name' => 'HIMATRO',
            'slug' => 'himatro',
            'logo_path' => 'assets/himatro.jpg',
        ]);

        $this->seed(UserSeeder::class);

        $this->assertDatabaseHas('users', [
            'name' => 'HIMA-TI',
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
            'avatar_path' => 'assets/himati.jpg',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'HIMATRO',
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $other->id,
            'avatar_path' => 'assets/himatro.jpg',
        ]);
        $this->assertDatabaseHas('users', [
            'role' => User::ROLE_DEVELOPER,
            'avatar_path' => 'assets/SIMORA.png',
        ]);
    }

    public function test_seeder_is_idempotent_and_preserves_existing_password(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI', 'slug' => 'hima-ti']);
        $existing = User::factory()->create([
            'email' => 'simora.himati@gmail.com',
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $originalHash = $existing->password;

        $this->seed(UserSeeder::class);
        $this->seed(UserSeeder::class);

        $this->assertSame(1, User::where('email', 'simora.himati@gmail.com')->count());
        $this->assertSame($originalHash, $existing->fresh()->password);
    }

    public function test_logged_in_user_with_avatar_sees_profile_picture_in_orgmate_layout(): void
    {
        $user = User::factory()->withAvatar()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($user)
            ->get(route('simora.orgmate'))
            ->assertOk()
            ->assertSee('Foto profil', false)
            ->assertSee(asset('assets/SIMORA.png'), false);
    }

    public function test_logged_in_user_with_avatar_sees_profile_picture_in_sentra_sidebar(): void
    {
        $user = User::factory()->withAvatar()->create(['role' => User::ROLE_DEVELOPER]);

        $response = $this->actingAs($user)->get(route('simora.sentra'));

        $response->assertOk();
        // Sidebar (orgmate-user-mini) dan topbar sama-sama menampilkan foto profil.
        $this->assertSame(2, substr_count($response->getContent(), 'Foto profil'));
        $response->assertSee(asset('assets/SIMORA.png'), false);
    }
}
