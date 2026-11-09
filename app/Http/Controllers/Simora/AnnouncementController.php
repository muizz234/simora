<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $request->validate($this->rules());
        $data['organization_id'] = $organization->id;
        $data['created_by'] = $request->user()->id;

        Announcement::create($data);

        return redirect()->route('simora.orgmate.pengumuman')
            ->with('status', 'Pengumuman berhasil diterbitkan.');
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $announcement->organization_id);

        $announcement->update($request->validate($this->rules()));

        return redirect()->route('simora.orgmate.pengumuman')
            ->with('status', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $announcement->organization_id);

        $announcement->delete();

        return redirect()->route('simora.orgmate.pengumuman')
            ->with('status', 'Pengumuman berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:10000'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
