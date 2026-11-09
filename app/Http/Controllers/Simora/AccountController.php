<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('organization')
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('simora.accounts.index', compact('users'));
    }

    public function create(): View
    {
        $organizations = Organization::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('simora.accounts.create', compact('organizations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAccount($request);

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->storePublicly('avatars', 'public');

            // Akun organisasi: foto yang sama dipakai sebagai logo organisasi.
            if ($data['role'] === User::ROLE_ORGANIZATION && ! empty($data['organization_id'])) {
                Organization::find($data['organization_id'])?->update(['logo_path' => $data['avatar_path']]);
            }
        }

        $data['api_token'] = Str::random(60);

        User::create($data);

        return redirect()->route('simora.accounts.index')
            ->with('status', 'Akun berhasil ditambahkan.');
    }

    public function edit(User $account): View
    {
        $organizations = Organization::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('simora.accounts.edit', [
            'account' => $account,
            'organizations' => $organizations,
        ]);
    }

    public function update(Request $request, User $account): RedirectResponse
    {
        abort_if($account->is(auth()->user()), 403, 'Akun developer sendiri tidak dapat diubah dari halaman ini.');

        $data = $this->validateAccount($request, $account);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($request->hasFile('avatar')) {
            $this->deleteUploadedAvatar($account->avatar_path);
            $data['avatar_path'] = $request->file('avatar')->storePublicly('avatars', 'public');

            // Akun organisasi: foto yang sama dipakai sebagai logo organisasi.
            if (($data['role'] ?? $account->role) === User::ROLE_ORGANIZATION && ! empty($data['organization_id'] ?? $account->organization_id)) {
                $organization = Organization::find($data['organization_id'] ?? $account->organization_id);

                if ($organization !== null) {
                    $this->deleteUploadedOrganizationLogo($organization->logo_path);
                    $organization->update(['logo_path' => $data['avatar_path']]);
                }
            }
        }

        $account->update($data);

        return redirect()->route('simora.accounts.index')
            ->with('status', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $account): RedirectResponse
    {
        abort_if($account->is(auth()->user()), 403, 'Akun developer sendiri tidak dapat dihapus dari halaman ini.');

        $this->deleteUploadedAvatar($account->avatar_path);

        $account->delete();

        return redirect()->route('simora.accounts.index')
            ->with('status', 'Akun berhasil dihapus.');
    }

    private function validateAccount(Request $request, ?User $account = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($account?->id)],
            'password' => [$account === null ? 'required' : 'nullable', 'string', 'min:8', 'max:255'],
            'role' => ['required', Rule::in(User::ROLES)],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    /**
     * Hapus file avatar upload (path avatars/...) saja;
     * avatar bawaan (assets/...) tidak disentuh.
     */
    private function deleteUploadedAvatar(?string $path): void
    {
        if ($path !== null && str_starts_with($path, 'avatars/')) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Hapus logo organisasi hasil upload (organizations/...) saja;
     * logo bawaan (assets/...) tidak disentuh.
     */
    private function deleteUploadedOrganizationLogo(?string $path): void
    {
        if ($path !== null && str_starts_with($path, 'organizations/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
