<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_developer_sees_account_menu_and_organization_user_does_not(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_DEVELOPER]))
            ->get(route('panel'))
            ->assertOk()
            ->assertSee('Akun')
            ->assertSee(route('simora.accounts.index'));

        $this->actingAs(User::factory()->create(['role' => User::ROLE_ORGANIZATION]))
            ->get(route('panel'))
            ->assertOk()
            ->assertDontSee(route('simora.accounts.index'));
    }

    public function test_developer_can_create_edit_and_delete_account_linked_to_organization(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI', 'slug' => 'hima-ti']);
        $this->actingAs(User::factory()->create(['role' => User::ROLE_DEVELOPER]));

        $this->get(route('simora.accounts.create'))->assertOk()->assertSee('Tambah Akun');

        $this->post(route('simora.accounts.store'), [
            'name' => 'HIMA-TI',
            'email' => 'simora.himati@gmail.com',
            'password' => 'himati123',
            'role' => 'organization',
            'organization_id' => $organization->id,
        ])->assertRedirect(route('simora.accounts.index'));

        $account = User::query()->where('email', 'simora.himati@gmail.com')->firstOrFail();
        $this->assertSame($organization->id, $account->organization_id);
        $this->assertSame('organization', $account->role);

        $this->get(route('simora.accounts.index'))->assertOk()->assertSee('simora.himati@gmail.com')->assertSee('HIMA-TI');
        $this->get(route('simora.accounts.edit', $account))->assertOk()->assertSee('Edit Akun');

        $this->put(route('simora.accounts.update', $account), [
            'name' => 'HIMA-TI Baru',
            'email' => 'simora.himati@gmail.com',
            'password' => 'himati456',
            'role' => 'organization',
            'organization_id' => $organization->id,
        ])->assertRedirect(route('simora.accounts.index'));

        $this->assertDatabaseHas('users', ['name' => 'HIMA-TI Baru', 'organization_id' => $organization->id]);

        $this->delete(route('simora.accounts.destroy', $account))
            ->assertRedirect(route('simora.accounts.index'));

        $this->assertDatabaseMissing('users', ['id' => $account->id]);
    }

    public function test_organization_user_cannot_open_account_management(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ORGANIZATION]));

        $this->get(route('simora.accounts.index'))->assertForbidden();
        $this->get(route('simora.accounts.create'))->assertForbidden();
    }

    public function test_developer_cannot_delete_their_own_account(): void
    {
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->delete(route('simora.accounts.destroy', $developer))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $developer->id]);
    }
}
