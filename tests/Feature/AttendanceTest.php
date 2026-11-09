<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_record_manual_attendance(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $activity = Activity::factory()->create(['organization_id' => $organization->id]);

        $this->actingAs($account)
            ->post(route('simora.e-progress.presensi.store'), [
                'activity_id' => $activity->id,
                'name' => 'Peserta Manual',
            ])
            ->assertRedirect(route('simora.e-progress.presensi', ['activity' => $activity->id]));

        $attendance = Attendance::where('organization_id', $organization->id)->firstOrFail();

        $this->assertSame('Peserta Manual', $attendance->name);
        $this->assertSame('manual', $attendance->method);
        $this->assertNotNull($attendance->checked_at);
    }

    public function test_guest_can_check_in_via_signed_qr_url(): void
    {
        $organization = Organization::factory()->create();
        $activity = Activity::factory()->create(['organization_id' => $organization->id, 'name' => 'Seminar Nasional']);

        $signedUrl = URL::signedRoute('presensi.checkin', ['activity' => $activity->id]);

        $this->get($signedUrl)->assertOk()->assertSee('Seminar Nasional');

        $this->post($signedUrl, ['name' => 'Peserta QR'])
            ->assertOk()
            ->assertSee('Berhasil Check-in');

        $this->assertDatabaseHas('attendances', [
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'name' => 'Peserta QR',
            'method' => 'qr',
        ]);
    }

    public function test_unsigned_checkin_url_is_forbidden(): void
    {
        $activity = Activity::factory()->create();

        $this->get(route('presensi.checkin', ['activity' => $activity->id]))
            ->assertForbidden();
    }

    public function test_organization_cannot_delete_another_organizations_attendance(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreign = Attendance::factory()->create(['organization_id' => $other->id, 'name' => 'Asing']);

        $this->actingAs($account)
            ->delete(route('simora.e-progress.presensi.destroy', $foreign))
            ->assertForbidden();

        $this->assertSame('Asing', $foreign->fresh()->name);
    }

    public function test_developer_cannot_write_attendance_but_can_view_the_page(): void
    {
        $organization = Organization::factory()->create(['slug' => 'hima-ti']);
        $activity = Activity::factory()->create(['organization_id' => $organization->id, 'name' => 'Seminar']);
        Attendance::factory()->create(['organization_id' => $organization->id, 'activity_id' => $activity->id, 'name' => 'Lihat Saja']);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.e-progress.presensi', ['organization' => 'hima-ti', 'activity' => $activity->id]))
            ->assertOk()
            ->assertSee('Lihat Saja');

        $this->actingAs($developer)
            ->post(route('simora.e-progress.presensi.store'), [
                'activity_id' => $activity->id,
                'name' => 'Bala',
            ])
            ->assertForbidden();
    }
}
