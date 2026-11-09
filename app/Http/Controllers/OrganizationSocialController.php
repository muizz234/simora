<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationSocialController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $organization->update($request->validate([
            'instagram_url' => ['nullable', 'url', 'max:2048'],
            'tiktok_url' => ['nullable', 'url', 'max:2048'],
            'youtube_url' => ['nullable', 'url', 'max:2048'],
            'whatsapp_url' => ['nullable', 'url', 'max:2048'],
            'facebook_url' => ['nullable', 'url', 'max:2048'],
            'x_url' => ['nullable', 'url', 'max:2048'],
            'email' => ['nullable', 'email', 'max:255'],
            'telegram_url' => ['nullable', 'url', 'max:2048'],
        ]));

        return redirect()->route('panel')
            ->with('status', 'Kontak sosial media berhasil disimpan.');
    }
}
