<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $this->normalize($request->validate($this->rules()));

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->storePublicly('avatars', 'public');
        }

        $data['organization_id'] = $organization->id;

        Member::create($data);

        return redirect()->route('simora.orgmate.anggota')
            ->with('status', 'Data anggota berhasil ditambahkan.');
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $member->organization_id);

        $data = $this->normalize($request->validate($this->rules($member)));

        if ($request->hasFile('avatar')) {
            $this->deleteUploadedAvatar($member->avatar_path);
            $data['avatar_path'] = $request->file('avatar')->storePublicly('avatars', 'public');
        }

        $member->update($data);

        return redirect()->route('simora.orgmate.anggota')
            ->with('status', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Request $request, Member $member): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $member->organization_id);

        $this->deleteUploadedAvatar($member->avatar_path);

        $member->delete();

        return redirect()->route('simora.orgmate.anggota')
            ->with('status', 'Data anggota berhasil dihapus.');
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
    private function rules(?Member $member = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nim' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('members', 'nim')->ignore($member?->id)->where(fn ($query) => $query->where('organization_id', $member?->organization_id ?? $this->currentOrganizationId())),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'faculty' => ['nullable', 'string', 'max:120'],
            'division' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in([Member::STATUS_ACTIVE, Member::STATUS_INACTIVE])],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * Hapus file foto upload (path avatars/...) saja;
     * foto bawaan (assets/...) tidak disentuh.
     */
    private function deleteUploadedAvatar(?string $path): void
    {
        if ($path !== null && str_starts_with($path, 'avatars/')) {
            Storage::disk('public')->delete($path);
        }
    }

    private function currentOrganizationId(): ?int
    {
        return auth()->user()?->organization_id;
    }
}
