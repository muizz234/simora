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

        $this->assertWithinWindow($activity);

        $name = trim($data['name']);

        // Tolak duplikat per user/activity (autentikasi) dan per nama/activity (guest QR)
        // agar double-submit QR tidak menggandakan baris presensi.
        $isDuplicate = Attendance::query()
            ->where('activity_id', $activity->id)
            ->where(function ($q) use ($name, $request) {
                $q->where('name', $name);

                if ($request->user() !== null) {
                    $q->orWhere('user_id', $request->user()->id);
                }
            })
            ->exists();

        if ($isDuplicate) {
            return view('simora.E-PROGRESS.presensi-checkin', [
                'activity' => $activity,
                'checkedIn' => true,
            ]);
        }

        Attendance::create([
            'organization_id' => $activity->organization_id,
            'activity_id' => $activity->id,
            'user_id' => $request->user()?->id,
            'name' => $name,
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

        $activity = Activity::findOrFail($data['activity_id']);

        $data['organization_id'] = $organization->id;
        $data['checked_at'] = now();
        $data['method'] = 'manual';
        $data['user_id'] = $request->user()->id;

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

    private function assertWithinWindow(Activity $activity): void
    {
        $now = now();

        abort_if(
            $activity->start_at !== null && $now->isBefore($activity->start_at),
            422,
            'Presensi belum dibuka. Kegiatan belum dimulai.'
        );

        abort_if(
            $activity->end_at !== null && $now->isAfter($activity->end_at),
            422,
            'Presensi telah ditutup. Kegiatan sudah selesai.'
        );
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
