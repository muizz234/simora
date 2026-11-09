<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Kelola Landing | SIMORA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/SIMORA.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}"><link rel="stylesheet" href="/assets/css/panel.css?v=5"><link rel="stylesheet" href="/assets/css/organization-admin.css?v=2"></head>
<body class="panel-page">
    <x-panel-header active="landing" />
    <main class="org-form-main">
        <a class="admin-header-back" style="display:inline-block;margin-bottom:16px" href="{{ route('panel') }}">← Kembali ke Dashboard</a>
        <div class="org-form-heading"><p class="panel-kicker">PENGELOLAAN LANDING PAGE</p><h1>Kelola Landing — Footer</h1><p>Ubah teks identitas dan link sosial media footer. Perubahan langsung tampil di semua menu landing.</p></div>
        @if(session('status'))<div class="admin-alert">{{ session('status') }}</div>@endif
        <form class="org-form" method="POST" action="{{ route('simora.landing-management.update') }}">
            @csrf @method('PUT')
            <div class="org-fields-grid">
                <div class="field-group field-wide"><label for="identity_text">Teks identitas footer</label><input id="identity_text" name="identity_text" value="{{ old('identity_text', $footerSetting->identity_text) }}" required>@error('identity_text')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field-group field-wide"><label for="tagline">Tagline footer</label><input id="tagline" name="tagline" value="{{ old('tagline', $footerSetting->tagline) }}" required>@error('tagline')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field-group"><label for="instagram_url">Link Instagram</label><input id="instagram_url" name="instagram_url" type="url" value="{{ old('instagram_url', $footerSetting->instagram_url) }}" required>@error('instagram_url')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field-group"><label for="tiktok_url">Link TikTok</label><input id="tiktok_url" name="tiktok_url" type="url" value="{{ old('tiktok_url', $footerSetting->tiktok_url) }}" required>@error('tiktok_url')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field-group"><label for="website_url">Link Website UNUJA</label><input id="website_url" name="website_url" type="url" value="{{ old('website_url', $footerSetting->website_url) }}" required>@error('website_url')<small class="field-error">{{ $message }}</small>@enderror</div>
            </div>
            <div class="org-form-actions"><a class="admin-secondary" href="{{ route('panel') }}">Batal</a><button type="submit" class="admin-primary">Simpan Pengaturan Footer</button></div>
        </form>
    </main>
</body>
</html>
