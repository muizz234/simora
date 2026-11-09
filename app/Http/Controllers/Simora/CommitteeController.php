<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Committee;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommitteeController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $this->normalize($request->validate($this->rules()));
        $data['organization_id'] = $organization->id;

        Committee::create($data);

        return redirect()->route('simora.e-progress.kepanitiaan', ['activity' => $data['activity_id']])
            ->with('status', 'Panitia berhasil ditambahkan.');
    }

    public function update(Request $request, Committee $committee): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $committee->organization_id);

        $data = $this->normalize($request->validate($this->rules(), false));
        $committee->update($data);

        return redirect()->route('simora.e-progress.kepanitiaan', ['activity' => $committee->activity_id])
            ->with('status', 'Panitia berhasil diperbarui.');
    }

    public function destroy(Request $request, Committee $committee): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $committee->organization_id);

        $activityId = $committee->activity_id;
        $committee->delete();

        return redirect()->route('simora.e-progress.kepanitiaan', ['activity' => $activityId])
            ->with('status', 'Panitia berhasil dihapus.');
    }

    /**
     * String kosong dari form menjadi null agar INSERT bersih.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(bool $requireActivity = true): array
    {
        return [
            'activity_id' => [
                $requireActivity ? 'required' : 'nullable',
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
            'role' => ['required', 'string', 'max:255'],
        ];
    }
}
