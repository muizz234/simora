<?php

namespace Tests\Unit;

use App\Models\Activity;
use App\Models\Budget;
use App\Models\Mahasiswa;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModelCastsTest extends TestCase
{
    use RefreshDatabase;

    public function test_budget_percentage_calculation(): void
    {
        $this->assertSame(0.0, (new Budget(['allocated' => 0, 'realized' => 500]))->percentage);
        $this->assertSame(25.0, (new Budget(['allocated' => 5000000, 'realized' => 1250000]))->percentage);
        $this->assertSame(33.33, (new Budget(['allocated' => 3, 'realized' => 1]))->percentage);
        $this->assertSame(150.0, (new Budget(['allocated' => 100, 'realized' => 150]))->percentage);
    }

    public function test_datetime_casts(): void
    {
        $activity = Activity::factory()->create([
            'start_at' => '2026-09-18 09:00:00',
            'end_at' => '2026-09-18 12:00:00',
        ]);
        $this->assertInstanceOf(\DateTimeInterface::class, $activity->start_at);
        $this->assertSame('2026-09-18 09:00:00', $activity->start_at->format('Y-m-d H:i:s'));

        $proposal = Proposal::factory()->create(['decided_at' => '2026-09-01 10:00:00']);
        $this->assertInstanceOf(\DateTimeInterface::class, $proposal->decided_at);

        $mahasiswa = Mahasiswa::factory()->create(['synced_at' => '2026-09-02 08:30:00']);
        $this->assertInstanceOf(\DateTimeInterface::class, $mahasiswa->synced_at);

        $user = User::factory()->create(['email_verified_at' => '2026-09-03 07:00:00']);
        $this->assertInstanceOf(\DateTimeInterface::class, $user->email_verified_at);
    }

    public function test_password_is_hashed_and_api_token_is_hidden(): void
    {
        $user = User::factory()->create([
            'password' => 'plain-secret',
            'api_token' => 'token-rahasia-uji',
        ]);

        $this->assertTrue(Hash::check('plain-secret', $user->password));
        $this->assertNotSame('plain-secret', $user->password);
        $this->assertArrayNotHasKey('api_token', $user->toArray());
        $this->assertArrayNotHasKey('password', $user->toArray());
    }
}
