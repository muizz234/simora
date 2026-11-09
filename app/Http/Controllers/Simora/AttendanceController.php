<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Attendance;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Halaman publik setelah peserta memindai QR code (signed URL, tanpa login).
     */
    public function checkin(Request $request, Activity $activity): View
    {
        return view('simora.E-PROGRESS.presensi-checkin', [
            'activity' => $activity,
            'checkedIn' => false,
        ]);
    }

    public function checkinStore(Request $request, Activity $activity): View
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Attendance::create([
            'organization_id' => $activity->organization_id,
            'activity_id' => $activity->id,
            'name' => $data['name'],
            'checked_at' => now(),
            'method' => 'qr',
        ]);

        return view('simora.E-PROGRESS.presensi-checkin', [
            'activity' => $activity,
            'checkedIn' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $request->validate($this->rules());
        $data['organization_id'] = $organization->id;
        $data['checked_at'] = now();
        $data['method'] = 'manual';

        Attendance::create($data);

        return redirect()->route('simora.e-progress.presensi', ['activity' => $data['activity_id']])
            ->with('status', 'Presensi manual berhasil dicatat.');
    }

    public function destroy(Request $request, Attendance $attendance): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $attendance->organization_id);

        $activityId = $attendance->activity_id;
        $attendance->delete();

        return redirect()->route('simora.e-progress.presensi', ['activity' => $activityId])
            ->with('status', 'Data presensi berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'activity_id' => [
                'required',
                'exists:activities,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value !== null) {
                        $belongs = Activity::where('id', $value)
                            ->where('organization_id', auth()->user()->organization_id)
                            ->exists();

                        if (! $belongs) {
                            $fail('Kegiatan harus milik organisasi Anda.');
                        }
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
