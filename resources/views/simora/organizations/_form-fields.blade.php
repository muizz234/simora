<div class="org-fields-grid">
    <div class="field-group"><label for="name">Nama organisasi</label><input id="name" name="name"
            value="{{ old('name', $organization->name) }}" required>
        @error('name')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="slug">Kode URL / slug</label><input id="slug" name="slug"
            value="{{ old('slug', $organization->slug) }}" required>
        @error('slug')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group field-wide"><label for="full_name">Nama lengkap</label><input id="full_name"
            name="full_name" value="{{ old('full_name', $organization->full_name) }}" required>
        @error('full_name')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="focus">Bidang / program studi</label><input id="focus" name="focus"
            value="{{ old('focus', $organization->focus) }}" required>
        @error('focus')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="period">Periode <span>(opsional, contoh: 2025/2026)</span></label><input
            id="period" name="period" value="{{ old('period', $organization->period) }}">
        @error('period')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="registration_url">Link pendaftaran <span>(opsional)</span></label><input
            id="registration_url" name="registration_url" type="url"
            value="{{ old('registration_url', $organization->registration_url) }}">
        @error('registration_url')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="instagram_url">Instagram <span>(opsional, tampil di
                Kontak)</span></label><input id="instagram_url" name="instagram_url" type="url"
            value="{{ old('instagram_url', $organization->instagram_url) }}">
        @error('instagram_url')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="tiktok_url">TikTok <span>(opsional, tampil di Kontak)</span></label><input
            id="tiktok_url" name="tiktok_url" type="url"
            value="{{ old('tiktok_url', $organization->tiktok_url) }}">
        @error('tiktok_url')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="youtube_url">YouTube <span>(opsional, tampil di Kontak)</span></label><input
            id="youtube_url" name="youtube_url" type="url"
            value="{{ old('youtube_url', $organization->youtube_url) }}">
        @error('youtube_url')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="whatsapp_url">WhatsApp <span>(opsional, tampil di Kontak)</span></label><input
            id="whatsapp_url" name="whatsapp_url" type="url"
            value="{{ old('whatsapp_url', $organization->whatsapp_url) }}">
        @error('whatsapp_url')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="facebook_url">Facebook <span>(opsional, tampil di Kontak)</span></label><input
            id="facebook_url" name="facebook_url" type="url"
            value="{{ old('facebook_url', $organization->facebook_url) }}">
        @error('facebook_url')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="x_url">X <span>(opsional, tampil di Kontak)</span></label><input
            id="x_url" name="x_url" type="url" value="{{ old('x_url', $organization->x_url) }}">
        @error('x_url')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="email">Email <span>(opsional, tampil di Kontak)</span></label><input
            id="email" name="email" type="email" value="{{ old('email', $organization->email) }}">
        @error('email')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="telegram_url">Telegram <span>(opsional, tampil di Kontak)</span></label><input
            id="telegram_url" name="telegram_url" type="url"
            value="{{ old('telegram_url', $organization->telegram_url) }}">
        @error('telegram_url')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="sort_order">Urutan tampil</label><input id="sort_order" name="sort_order"
            type="number" min="0" value="{{ old('sort_order', $organization->sort_order ?? 0) }}">
        @error('sort_order')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="banner_color">Warna banner</label>
        <div class="color-field"><input id="banner_color" name="banner_color" type="color"
                value="{{ old('banner_color', $organization->banner_color ?? '#171717') }}"><span>{{ old('banner_color', $organization->banner_color ?? '#171717') }}</span>
        </div>
        @error('banner_color')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="accent_color">Warna aksen</label>
        <div class="color-field"><input id="accent_color" name="accent_color" type="color"
                value="{{ old('accent_color', $organization->accent_color ?? '#3687ff') }}"><span>{{ old('accent_color', $organization->accent_color ?? '#3687ff') }}</span>
        </div>
        @error('accent_color')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group field-wide"><label for="description">Deskripsi</label>
        <textarea id="description" name="description" rows="4" required>{{ old('description', $organization->description) }}</textarea>
        @error('description')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group field-wide"><label for="vision">Visi <span>(opsional)</span></label>
        <textarea id="vision" name="vision" rows="3">{{ old('vision', $organization->vision) }}</textarea>
        @error('vision')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group field-wide"><label for="mission">Misi <span>(opsional, tulis tiap poin pada baris
                baru)</span></label>
        <textarea id="mission" name="mission" rows="4">{{ old('mission', $organization->mission) }}</textarea>
        @error('mission')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group field-wide"><label for="logo">Logo organisasi @if ($organization->exists)
                <span>(opsional, kosongkan jika tidak diganti)</span>
            @endif
        </label>
        <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp"
            @required(!$organization->exists)>
        @error('logo')
            <small class="field-error">{{ $message }}</small>
            @enderror @if ($organization->logo_url)
                <div class="logo-preview"><img src="{{ $organization->logo_url }}" alt="Logo saat ini"><span>Logo saat
                        ini</span></div>
            @endif
        </div>
    </div>
