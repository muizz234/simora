<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Evaluation;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EvaluationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $this->normalize($request->validate($this->rules()));
        $data['organization_id'] = $organization->id;
        $data['created_by'] = $request->user()->id;

        Evaluation::create($data);

        return redirect()->route('simora.e-progress.evaluasi', ['activity' => $data['activity_id']])
            ->with('status', 'Evaluasi/LPJ berhasil disimpan.');
    }

    public function update(Request $request, Evaluation $evaluation): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $evaluation->organization_id);

        $evaluation->update($this->normalize($request->validate($this->rules())));

        return redirect()->route('simora.e-progress.evaluasi', ['activity' => $evaluation->activity_id])
            ->with('status', 'Evaluasi/LPJ berhasil diperbarui.');
    }

    public function destroy(Request $request, Evaluation $evaluation): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $evaluation->organization_id);

        $activityId = $evaluation->activity_id;
        $evaluation->delete();

        return redirect()->route('simora.e-progress.evaluasi', ['activity' => $activityId])
            ->with('status', 'Evaluasi/LPJ berhasil dihapus.');
    }

    private function normalize(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        return $data;
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
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:10000'],
            'type' => ['required', Rule::in([Evaluation::TYPE_EVALUATION, Evaluation::TYPE_REPORT])],
            'status' => ['required', Rule::in([Evaluation::STATUS_DRAFT, Evaluation::STATUS_DONE])],
        ];
    }
}
