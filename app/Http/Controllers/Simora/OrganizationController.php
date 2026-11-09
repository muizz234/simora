<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(): View
    {
        $organizations = Organization::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);

        return view('simora.organizations.index', compact('organizations'));
    }

    public function create(): View
    {
        $organization = new Organization;

        return view('simora.organizations.create', compact('organization'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateOrganization($request);
        $logo = $data['logo'];
        unset($data['logo']);
        $data['sort_order'] ??= 0;
        $data['logo_path'] = $logo->storePublicly('organizations', 'public');

        Organization::create($data);

        return redirect()->route('simora.organizations.index')->with('status', 'Organisasi berhasil ditambahkan.');
    }

    public function edit(Organization $organization): View
    {
        return view('simora.organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $data = $this->validateOrganization($request, $organization);
        $logo = $data['logo'] ?? null;
        unset($data['logo']);

        if ($logo instanceof UploadedFile) {
            $previousLogo = $organization->logo_path;
            $data['logo_path'] = $logo->storePublicly('organizations', 'public');
            $this->deleteUploadedLogo($previousLogo);
        }

        $organization->update($data);

        return redirect()->route('simora.organizations.index')->with('status', 'Organisasi berhasil diperbarui.');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        $this->deleteUploadedLogo($organization->logo_path);
        $organization->delete();

        return redirect()->route('simora.organizations.index')->with('status', 'Organisasi berhasil dihapus.');
    }

    private function validateOrganization(Request $request, ?Organization $organization = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('organizations', 'name')->ignore($organization?->id)],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash:ascii', Rule::unique('organizations', 'slug')->ignore($organization?->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'banner_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'focus' => ['required', 'string', 'max:120'],
            'period' => ['nullable', 'string', 'max:60'],
            'registration_url' => ['nullable', 'url', 'max:2048'],
            'instagram_url' => ['nullable', 'url', 'max:2048'],
            'tiktok_url' => ['nullable', 'url', 'max:2048'],
            'youtube_url' => ['nullable', 'url', 'max:2048'],
            'whatsapp_url' => ['nullable', 'url', 'max:2048'],
            'facebook_url' => ['nullable', 'url', 'max:2048'],
            'x_url' => ['nullable', 'url', 'max:2048'],
            'email' => ['nullable', 'email', 'max:255'],
            'telegram_url' => ['nullable', 'url', 'max:2048'],
            'description' => ['required', 'string', 'max:2000'],
            'vision' => ['nullable', 'string', 'max:5000'],
            'mission' => ['nullable', 'string', 'max:5000'],
            'logo' => [$organization === null ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);
    }

    private function deleteUploadedLogo(?string $path): void
    {
        if ($path !== null && str_starts_with($path, 'organizations/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
