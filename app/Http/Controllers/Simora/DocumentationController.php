<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Documentation;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $request->validate($this->rules(true));

        $files = $request->file('files', []);

        if (! is_array($files)) {
            $files = [$files];
        }

        if ($files === []) {
            abort(422, 'Pilih minimal satu berkas untuk diunggah.');
        }

        foreach ($files as $index => $file) {
            $record = [
                'organization_id' => $organization->id,
                'uploaded_by' => $request->user()->id,
                'activity_id' => $data['activity_id'],
                'type' => $data['type'],
                'title' => count($files) > 1 ? ($data['title'].' ('.($index + 1).')') : $data['title'],
                'file_path' => $file->storePublicly('documentation', 'public'),
            ];

            Documentation::create($record);
        }

        $count = count($files);

        return redirect()->route('simora.e-progress.dokumentasi', ['activity' => $data['activity_id']])
            ->with('status', $count > 1 ? $count.' dokumentasi berhasil diunggah.' : 'Dokumentasi berhasil diunggah.');
    }

    public function update(Request $request, Documentation $documentation): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $documentation->organization_id);

        $data = $request->validate($this->rules(false));

        if ($request->hasFile('file')) {
            $this->deleteUploadedFile($documentation->file_path);
            $data['file_path'] = $request->file('file')->storePublicly('documentation', 'public');
        }

        $documentation->update($data);

        return redirect()->route('simora.e-progress.dokumentasi', ['activity' => $documentation->activity_id])
            ->with('status', 'Dokumentasi berhasil diperbarui.');
    }

    public function destroy(Request $request, Documentation $documentation): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $documentation->organization_id);

        $activityId = $documentation->activity_id;
        $this->deleteUploadedFile($documentation->file_path);
        $documentation->delete();

        return redirect()->route('simora.e-progress.dokumentasi', ['activity' => $activityId])
            ->with('status', 'Dokumentasi berhasil dihapus.');
    }

    private function deleteUploadedFile(?string $path): void
    {
        if ($path !== null && str_starts_with($path, 'documentation/')) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(bool $requireFile): array
    {
        $rules = [
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
            'type' => ['required', Rule::in(Documentation::TYPES)],
        ];

        if ($requireFile) {
            $rules['files'] = ['required', 'array', 'min:1', 'max:10'];
            $rules['files.*'] = ['file', 'mimes:pdf,jpg,jpeg,png,webp,mp4,mov,doc,docx', 'max:20480'];
        } else {
            $rules['file'] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,mp4,mov,doc,docx', 'max:20480'];
        }

        return $rules;
    }
}
