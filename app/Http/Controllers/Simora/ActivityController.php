<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\WorkProgram;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $this->normalize($request->validate($this->rules()));
        $data['organization_id'] = $organization->id;
        $data['created_by'] = $request->user()->id;

        Activity::create($data);

        return redirect()->route('simora.e-progress.kalender')
            ->with('status', 'Kegiatan berhasil ditambahkan.');
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $activity->organization_id);

        $activity->update($this->normalize($request->validate($this->rules())));

        return redirect()->route('simora.e-progress.kalender')
            ->with('status', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Request $request, Activity $activity): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $activity->organization_id);

        $activity->delete();

        return redirect()->route('simora.e-progress.kalender')
            ->with('status', 'Kegiatan berhasil dihapus.');
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
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'work_program_id' => [
                'nullable',
                'exists:work_programs,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value !== null) {
                        $belongs = WorkProgram::where('id', $value)
                            ->where('organization_id', auth()->user()->organization_id)
                            ->exists();

                        if (! $belongs) {
                            $fail('Program kerja harus milik organisasi Anda.');
                        }
                    }
                },
            ],
            'status' => ['required', Rule::in(Activity::STAGES ?? ['perencanaan', 'pelaksanaan', 'evaluasi', 'pelaporan', 'selesai'])],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
        ];
    }
}
