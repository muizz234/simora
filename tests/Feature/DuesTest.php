<?php

namespace Tests\Feature;

use App\Models\CashTransaction;
use App\Models\Dues;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DuesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_organization_can_record_due_and_mark_paid_records_income(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->post(route('simora.sentra.iuran.store'), [
                'name' => 'Andi Pratama',
                'proof' => UploadedFile::fake()->image('bukti.jpg'),
                'amount' => 50000,
                'status' => Dues::STATUS_UNPAID,
                'period' => 'Wajib 2026',
            ])
            ->assertRedirect(route('simora.sentra.iuran'));

        $due = Dues::where('organization_id', $organization->id)->firstOrFail();

        $this->actingAs($account)
            ->put(route('simora.sentra.iuran.markPaid', $due))
            ->assertRedirect(route('simora.sentra.iuran'));

        $due->refresh();

        $this->assertSame(Dues::STATUS_PAID, $due->status);
        $this->assertNotNull($due->paid_at);

        $this->assertDatabaseHas('cash_transactions', [
            'organization_id' => $organization->id,
            'type' => CashTransaction::TYPE_INCOME,
            'title' => 'Iuran Andi Pratama',
            'amount' => 50000,
            'category' => 'Iuran Anggota',
        ]);

        $income = CashTransaction::where('organization_id', $organization->id)->where('title', 'Iuran Andi Pratama')->firstOrFail();
        $this->assertNotNull($income->proof_path);
        Storage::disk('public')->assertExists($income->proof_path);

        $this->actingAs($account)
            ->get(route('simora.sentra.iuran'))
            ->assertOk()
            ->assertSee('Andi Pratama')
            ->assertSee('100%');
    }

    public function test_creating_due_directly_as_paid_records_income(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->post(route('simora.sentra.iuran.store'), [
                'name' => 'Sinta',
                'proof' => UploadedFile::fake()->image('bukti.jpg'),
                'amount' => 75000,
                'status' => Dues::STATUS_PAID,
                'period' => 'Wajib 2026',
                'paid_at' => now()->format('Y-m-d'),
            ])
            ->assertRedirect(route('simora.sentra.iuran'));

        $this->assertDatabaseHas('cash_transactions', [
            'organization_id' => $organization->id,
            'type' => CashTransaction::TYPE_INCOME,
            'title' => 'Iuran Sinta',
            'amount' => 75000,
            'category' => 'Iuran Anggota',
        ]);

        $this->actingAs($account)
            ->get(route('simora.sentra.kas'))
            ->assertOk()
            ->assertSee('Iuran Sinta')
            ->assertSee('Rp75.000');
    }

    public function test_editing_unpaid_due_to_paid_records_income_once(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $due = Dues::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Bima',
            'amount' => 60000,
            'status' => Dues::STATUS_UNPAID,
        ]);

        $this->actingAs($account)
            ->put(route('simora.sentra.iuran.update', $due), [
                'name' => 'Bima',
                'amount' => 60000,
                'status' => Dues::STATUS_PAID,
                'period' => $due->period,
            ])
            ->assertRedirect(route('simora.sentra.iuran'));

        $this->assertSame(1, CashTransaction::where('organization_id', $organization->id)->where('title', 'Iuran Bima')->count());

        // Edit lagi tanpa mengubah status: tidak boleh menambah pemasukan kedua.
        $this->actingAs($account)
            ->put(route('simora.sentra.iuran.update', $due->fresh()), [
                'name' => 'Bima',
                'amount' => 65000,
                'status' => Dues::STATUS_PAID,
                'period' => $due->period,
            ])
            ->assertRedirect();

        $this->assertSame(1, CashTransaction::where('organization_id', $organization->id)->where('title', 'Iuran Bima')->count());
    }

    public function test_marking_paid_twice_is_rejected(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $due = Dues::factory()->create([
            'organization_id' => $organization->id,
            'status' => Dues::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $this->actingAs($account)
            ->put(route('simora.sentra.iuran.markPaid', $due))
            ->assertStatus(422);

        $this->assertSame(0, CashTransaction::where('title', 'Iuran '.$due->name)->count());
    }

    public function test_organization_cannot_manage_another_organizations_due(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreign = Dues::factory()->create(['organization_id' => $other->id, 'name' => 'Asing']);

        $this->actingAs($account)
            ->put(route('simora.sentra.iuran.markPaid', $foreign))
            ->assertForbidden();

        $this->actingAs($account)
            ->delete(route('simora.sentra.iuran.destroy', $foreign))
            ->assertForbidden();

        $this->assertSame('Asing', $foreign->fresh()->name);
    }

    public function test_developer_cannot_write_dues_but_can_view_them(): void
    {
        $organization = Organization::factory()->create(['slug' => 'hima-ti']);
        Dues::factory()->create(['organization_id' => $organization->id, 'name' => 'Lihat Saja']);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.sentra.iuran', ['organization' => 'hima-ti']))
            ->assertOk()
            ->assertSee('Lihat Saja');

        $this->actingAs($developer)
            ->post(route('simora.sentra.iuran.store'), [
                'name' => 'Bala',
                'proof' => UploadedFile::fake()->image('bukti.jpg'),
                'amount' => 10000,
                'status' => Dues::STATUS_UNPAID,
            ])
            ->assertForbidden();
    }

    public function test_proof_is_required_for_dues(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->post(route('simora.sentra.iuran.store'), [
                'name' => 'Tanpa Bukti',
                'amount' => 10000,
                'status' => Dues::STATUS_UNPAID,
            ])
            ->assertSessionHasErrors('proof');

        $this->assertDatabaseMissing('dues', ['name' => 'Tanpa Bukti']);
    }
}
