<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Position;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $this->normalize($request->validate($this->rules($request)));
        $data['organization_id'] = $organization->id;

        Position::create($data);

        return redirect()->route('simora.orgmate.struktur')
            ->with('status', 'Jabatan berhasil ditambahkan.');
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $position->organization_id);

        $position->update($this->normalize($request->validate($this->rules($request))));

        return redirect()->route('simora.orgmate.struktur')
            ->with('status', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Request $request, Position $position): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $position->organization_id);

        $position->delete();

        return redirect()->route('simora.orgmate.struktur')
            ->with('status', 'Jabatan berhasil dihapus.');
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
    private function rules(Request $request): array
    {
        $organizationId = $request->user()->organization_id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'group_type' => ['nullable', Rule::in(Position::GROUPS)],
            'member_id' => [
                'nullable',
                'integer',
                'exists:members,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($organizationId) {
                    if ($value !== null) {
                        $belongs = Member::where('id', $value)
                            ->where('organization_id', $organizationId)
                            ->exists();

                        if (! $belongs) {
                            $fail('Pemegang jabatan harus anggota organisasi Anda.');
                        }
                    }
                },
            ],
        ];
    }
}
