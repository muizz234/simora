<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Archive;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ArchiveController extends Controller
{
    private const TYPES = ['sk', 'surat_masuk', 'surat_keluar'];

    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $request->validate($this->rules());

        $data['organization_id'] = $organization->id;
        $data['uploaded_by'] = $request->user()->id;

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->storePublicly('archives', 'public');
        }

        Archive::create($data);

        return redirect()->route('simora.orgmate.arsip')
            ->with('status', 'Arsip berhasil ditambahkan.');
    }

    public function update(Request $request, Archive $archive): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $archive->organization_id);

        $data = $request->validate($this->rules());

        if ($request->hasFile('file')) {
            $this->deleteUploadedFile($archive->file_path);
            $data['file_path'] = $request->file('file')->storePublicly('archives', 'public');
        }

        $archive->update($data);

        return redirect()->route('simora.orgmate.arsip')
            ->with('status', 'Arsip berhasil diperbarui.');
    }

    public function destroy(Request $request, Archive $archive): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $archive->organization_id);

        $this->deleteUploadedFile($archive->file_path);

        $archive->delete();

        return redirect()->route('simora.orgmate.arsip')
            ->with('status', 'Arsip berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(self::TYPES)],
            'number' => ['nullable', 'string', 'max:120'],
            'date' => ['nullable', 'date'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:10240'],
        ];
    }

    private function deleteUploadedFile(?string $path): void
    {
        if ($path !== null && str_starts_with($path, 'archives/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
