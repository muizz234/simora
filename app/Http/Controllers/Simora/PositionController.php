<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Member;
use App\Models\Organization;
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
        unset($data['mahasiswa_id']);
        $data['organization_id'] = $organization->id;
        $data['member_id'] = $this->resolveMemberId($organization, $request->input('mahasiswa_id'), $data['member_id'] ?? null);

        Position::create($data);

        return redirect()->route('simora.orgmate.struktur')
            ->with('status', 'Jabatan berhasil ditambahkan.');
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $position->organization_id);

        $organization = $request->user()->organization;
        $data = $this->normalize($request->validate($this->rules($request)));
        unset($data['mahasiswa_id']);
        $data['member_id'] = $this->resolveMemberId($organization, $request->input('mahasiswa_id'), $data['member_id'] ?? null);

        $position->update($data);

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
     * Jadikan pilihan mahasiswa (SSO) sebagai member organisasi:
     * cari by NIM di organisasi ini, buat bila belum ada.
     * Pilihan member_id langsung (legacy) tetap dihormati.
     */
    private function resolveMemberId(?Organization $organization, mixed $mahasiswaId, ?int $memberId): ?int
    {
        if ($mahasiswaId === null || $mahasiswaId === '' || $organization === null) {
            return $memberId;
        }

        $mahasiswa = Mahasiswa::find((int) $mahasiswaId);

        if ($mahasiswa === null) {
            return $memberId;
        }

        $member = Member::query()
            ->where('organization_id', $organization->id)
            ->where('nim', $mahasiswa->nim)
            ->first();

        if ($member !== null) {
            return $member->id;
        }

        $created = Member::create([
            'organization_id' => $organization->id,
            'name' => $mahasiswa->name,
            'nim' => $mahasiswa->nim,
            'faculty' => $mahasiswa->faculty,
            'study_program' => $mahasiswa->study_program,
            'status' => Member::STATUS_ACTIVE,
        ]);

        return $created->id;
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
            'mahasiswa_id' => [
                'nullable',
                'integer',
                'exists:mahasiswas,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    if ($value !== null) {
                        $mahasiswa = Mahasiswa::find($value);
                        $focus = trim((string) ($request->user()->organization?->focus ?? ''));

                        if ($mahasiswa === null || strtolower((string) $mahasiswa->study_program) !== strtolower($focus)) {
                            $fail('Pemegang jabatan harus mahasiswa prodi organisasi Anda.');
                        }
                    }
                },
            ],
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
