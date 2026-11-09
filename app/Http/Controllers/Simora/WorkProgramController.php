<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\WorkProgram;
use App\Support\OrganizationScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkProgramController extends Controller
{
    public function index(Request $request): View
    {
        $context = $this->organizationContext($request);

        $query = WorkProgram::query();

        // Developer: mode monitoring (semua organisasi atau pilihan ?organization=).
        // Role organization: hanya organisasinya sendiri (seperti E-PROGRESS).
        if ($context['isDeveloper']) {
            $query->when($context['selectedOrganization'] !== null, fn ($q) => $q->where('organization_id', $context['selectedOrganization']->id));
        } else {
            $query->where('organization_id', $request->user()->organization_id);
        }

        $workPrograms = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('simora.E-PROGRESS.proker', array_merge($context, compact('workPrograms')));
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $request->validate($this->rules());
        $data['organization_id'] = $organization->id;
        $data['created_by'] = $request->user()->id;

        WorkProgram::create($data);

        return redirect()->route('simora.e-progress.proker')
            ->with('status', 'Program kerja berhasil ditambahkan.');
    }

    public function update(Request $request, WorkProgram $workProgram): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $workProgram->organization_id);

        $workProgram->update($this->normalize($request->validate($this->rules())));

        return redirect()->route('simora.e-progress.proker')
            ->with('status', 'Program kerja berhasil diperbarui.');
    }

    public function destroy(Request $request, WorkProgram $workProgram): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $workProgram->organization_id);

        $workProgram->delete();

        return redirect()->route('simora.e-progress.proker')
            ->with('status', 'Program kerja berhasil dihapus.');
    }

    /**
     * Konteks organisasi seperti pola E-PROGRESS.
     *
     * @return array{isDeveloper: bool, organizationsList: Collection, selectedOrganization: ?Organization, orgName: string}
     */
    private function organizationContext(Request $request): array
    {
        $isDeveloper = $request->user()?->role === 'developer';
        $organizationsList = $isDeveloper
            ? Organization::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug'])
            : collect([]);
        $selectedOrganization = $isDeveloper
            ? $organizationsList->firstWhere('slug', $request->query('organization'))
            : null;

        $orgName = $selectedOrganization?->name
            ?? ($isDeveloper ? 'Semua Organisasi' : $request->user()?->organization?->name ?? $organizationsList->first()?->name ?? 'Semua Organisasi');

        return compact('isDeveloper', 'organizationsList', 'selectedOrganization', 'orgName');
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
            'status' => ['required', Rule::in(WorkProgram::STAGES)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
