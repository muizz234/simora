<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationRegistrationController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $request->validate([
            'registration_url' => ['required', 'url', 'max:2048'],
        ]);

        $organization->update($data);

        return redirect()->route('panel')
            ->with('status', 'Link pendaftaran berhasil disimpan.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $organization->update(['registration_url' => null]);

        return redirect()->route('panel')
            ->with('status', 'Link pendaftaran berhasil dihapus.');
    }
}
