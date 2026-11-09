<div class="org-fields-grid">
    <div class="field-group"><label for="name">Nama akun</label><input id="name" name="name"
            value="{{ old('name', $account?->name) }}" required>
        @error('name')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="email">Email</label><input id="email" name="email" type="email"
            value="{{ old('email', $account?->email) }}" required>
        @error('email')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="password">Password @if ($account)
                <span>(opsional, kosongkan jika tidak diganti)</span>
            @endif
        </label>
        <input id="password" name="password" type="password" autocomplete="new-password" @required(!$account)>
        @error('password')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group"><label for="role">Role</label><select id="role" name="role" required>
            <option value="developer" @selected(old('role', $account?->role) === 'developer')>Developer</option>
            <option value="organization" @selected(old('role', $account?->role) === 'organization')>Organisasi</option>
            <option value="user" @selected(old('role', $account?->role) === 'user')>User biasa</option>
        </select>
        @error('role')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
    <div class="field-group field-wide"><label for="avatar">Foto profil (avatar)
            <span>(opsional, JPG/PNG/WebP maks. 2 MB — kosongkan jika tidak diganti. Untuk akun organisasi, foto ini juga menjadi logo organisasi.)</span></label>
        <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp">
        @error('avatar')
            <small class="field-error">{{ $message }}</small>
        @enderror
        @if ($account?->avatar_url)
            <div class="logo-preview"><img src="{{ $account->avatar_url }}" alt="Foto profil saat ini"><span>Foto profil saat ini</span></div>
        @endif
    </div>
    <div class="field-group field-wide"><label for="organization_id">Organisasi terkait
            <span>(opsional)</span></label><select id="organization_id" name="organization_id">
            <option value="">Tanpa organisasi</option>
            @foreach ($organizations as $organization)
                <option value="{{ $organization->id }}" @selected(old('organization_id', $account?->organization_id) == $organization->id)>{{ $organization->name }} —
                    {{ $organization->full_name }}</option>
            @endforeach
        </select>
        @error('organization_id')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
</div>
