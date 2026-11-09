<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PublicPresensiTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_signed_url_is_rejected(): void
    {
        $activity = Activity::factory()->create();
        $expiredUrl = URL::temporarySignedRoute('presensi.checkin', now()->subHour(), ['activity' => $activity->id]);

        $this->get($expiredUrl)->assertForbidden();
    }

    public function test_tampered_signed_url_is_rejected(): void
    {
        $activity = Activity::factory()->create();
        $signedUrl = URL::signedRoute('presensi.checkin', ['activity' => $activity->id]);
        $tamperedUrl = $signedUrl.'&signature=0000000000000000000000000000000000000000000000000000000000000000';

        $this->get($tamperedUrl)->assertForbidden();
    }

    public function test_signed_checkin_requires_name(): void
    {
        $activity = Activity::factory()->create(['organization_id' => Organization::factory()->create()->id]);
        $signedUrl = URL::signedRoute('presensi.checkin.store', ['activity' => $activity->id]);

        $this->post($signedUrl, ['name' => ''])
            ->assertSessionHasErrors('name')
            ->assertStatus(302);

        $this->assertSame(0, $activity->attendances()->count());
    }

    public function test_signed_checkin_for_unknown_activity_is_not_found(): void
    {
        $signedUrl = URL::signedRoute('presensi.checkin', ['activity' => 999999]);

        $this->get($signedUrl)->assertNotFound();
    }

    public function test_guest_cannot_open_checkin_without_signature(): void
    {
        $activity = Activity::factory()->create();

        $this->get(route('presensi.checkin', ['activity' => $activity->id]))->assertForbidden();
        $this->post(route('presensi.checkin.store', ['activity' => $activity->id]), ['name' => 'Tanpa Tanda'])->assertForbidden();
    }
}
