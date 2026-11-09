<?php

namespace Tests\Feature;

use App\Models\CashTransaction;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CashTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_add_income_and_expense_and_balance_is_computed(): void
    {
        Storage::fake('public');
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->post(route('simora.sentra.kas.store'), [
                'type' => CashTransaction::TYPE_INCOME,
                'title' => 'Dana sponsorship',
                'amount' => 1000000,
                'date' => now()->format('Y-m-d'),
                'category' => 'Sponsorship',
                'proof' => UploadedFile::fake()->image('struk.jpg'),
            ])
            ->assertRedirect(route('simora.sentra.kas'));

        $this->actingAs($account)
            ->post(route('simora.sentra.kas.store'), [
                'type' => CashTransaction::TYPE_EXPENSE,
                'title' => 'Cetak spanduk',
                'amount' => 250000,
                'date' => now()->format('Y-m-d'),
                'proof' => UploadedFile::fake()->image('nota.jpg'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('cash_transactions', [
            'organization_id' => $organization->id,
            'title' => 'Dana sponsorship',
            'type' => CashTransaction::TYPE_INCOME,
        ]);

        $stored = CashTransaction::where('organization_id', $organization->id)->firstOrFail();
        Storage::disk('public')->assertExists($stored->proof_path);

        $this->actingAs($account)
            ->get(route('simora.sentra.kas'))
            ->assertOk()
            ->assertSee('Dana sponsorship')
            ->assertSee('Rp750.000');

        $this->actingAs($account)
            ->get(route('simora.sentra'))
            ->assertOk();
    }

    public function test_proof_is_required_for_cash_transactions(): void
    {
        Storage::fake('public');
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->post(route('simora.sentra.kas.store'), [
                'type' => CashTransaction::TYPE_INCOME,
                'title' => 'Tanpa Bukti',
                'amount' => 100000,
                'date' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('proof');

        $this->assertDatabaseMissing('cash_transactions', ['title' => 'Tanpa Bukti']);
    }

    public function test_amount_must_be_numeric_and_title_required(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->post(route('simora.sentra.kas.store'), [
                'type' => CashTransaction::TYPE_INCOME,
                'title' => '',
                'amount' => 'bukan-angka',
                'date' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors(['title', 'amount']);
    }

    public function test_organization_cannot_manage_another_organizations_transaction(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreign = CashTransaction::factory()->create(['organization_id' => $other->id, 'title' => 'Asing']);

        $this->actingAs($account)
            ->put(route('simora.sentra.kas.update', $foreign), [
                'type' => CashTransaction::TYPE_INCOME,
                'title' => 'Diubah',
                'amount' => 100,
                'date' => now()->format('Y-m-d'),
            ])
            ->assertForbidden();

        $this->actingAs($account)
            ->delete(route('simora.sentra.kas.destroy', $foreign))
            ->assertForbidden();

        $this->assertSame('Asing', $foreign->fresh()->title);
    }

    public function test_developer_cannot_write_cash_but_can_view_it(): void
    {
        $organization = Organization::factory()->create(['slug' => 'hima-ti']);
        CashTransaction::factory()->create(['organization_id' => $organization->id, 'title' => 'Lihat Saja']);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.sentra.kas', ['organization' => 'hima-ti']))
            ->assertOk()
            ->assertSee('Lihat Saja');

        $this->actingAs($developer)
            ->post(route('simora.sentra.kas.store'), [
                'type' => CashTransaction::TYPE_INCOME,
                'title' => 'Bala',
                'amount' => 100,
                'date' => now()->format('Y-m-d'),
                'proof' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertForbidden();
    }
}
