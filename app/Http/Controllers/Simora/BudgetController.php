<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\WorkProgram;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $this->normalize($request->validate($this->rules()));
        $data['organization_id'] = $organization->id;

        Budget::create($data);

        return redirect()->route('simora.sentra.rab', ['periode' => $data['year']])
            ->with('status', 'Anggaran RAB berhasil ditambahkan.');
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $budget->organization_id);

        $data = $this->normalize($request->validate($this->rules()));
        $budget->update($data);

        return redirect()->route('simora.sentra.rab', ['periode' => $budget->year])
            ->with('status', 'Anggaran RAB berhasil diperbarui.');
    }

    public function destroy(Request $request, Budget $budget): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $budget->organization_id);

        $year = $budget->year;
        $budget->delete();

        return redirect()->route('simora.sentra.rab', ['periode' => $year])
            ->with('status', 'Anggaran RAB berhasil dihapus.');
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
            'program_name' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:2000', 'max:2035'],
            'allocated' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'realized' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
        ];
    }
}
