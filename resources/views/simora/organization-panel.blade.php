<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f7fb">
    <title>Dashboard Organisasi | SIMORA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/SIMORA.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="/assets/css/panel.css?v=5">
    <link rel="stylesheet" href="/assets/css/organization-panel.css?v=3">
    <script src="/assets/js/clock.js" defer></script>
</head>
<body class="panel-page">
    <x-panel-header active="dashboard" />
    <main class="panel-main">
        @if($user->organization)
        <section class="panel-hero">
            <div class="panel-hero-copy">
                <p class="panel-kicker">SIMORA · PANEL ORGANISASI — {{ $greeting }},</p>
                <h1>{{ $user->organization->name }}</h1>
                <p class="panel-hero-sub">{{ $user->organization->full_name }} · Periode {{ $user->organization->period ?? '—' }}</p>
            </div>
            <div class="panel-hero-meta">
                <span class="panel-hero-date">{{ $todayLabel }}</span>
                <span class="panel-hero-clock" data-live-clock>—</span>
                <span class="access-badge"><i></i> Akses Organization</span>
            </div>
            <div class="panel-hero-decor" aria-hidden="true"><i class="d1"></i><i class="d2"></i></div>
        </section>

        <section class="panel-stats" aria-label="Statistik organisasi">
            <article class="panel-stat panel-stat--blue">
                <span class="panel-stat-icon">▤</span>
                <div><strong data-count="{{ $stats['proker'] }}">0</strong><small>Program Kerja</small></div>
            </article>
            <article class="panel-stat panel-stat--teal">
                <span class="panel-stat-icon">◐</span>
                <div><strong data-count="{{ $stats['kegiatan'] }}">0</strong><small>Kegiatan</small></div>
            </article>
            <article class="panel-stat panel-stat--green">
                <span class="panel-stat-icon">✦</span>
                <div><strong>Rp{{ number_format($stats['saldoKas'], 0, ',', '.') }}</strong><small>Saldo Kas</small></div>
            </article>
            <article class="panel-stat panel-stat--purple">
                <span class="panel-stat-icon">👥</span>
                <div><strong data-count="{{ $stats['iuranLunas'] }}/{{ $stats['iuranTotal'] }}">0</strong><small>Iuran Lunas</small></div>
            </article>
        </section>
        @endif

        <section class="welcome-grid" aria-label="Profil organisasi">
            <article class="developer-card org-card" style="--org-color: {{ $user->organization?->banner_color ?? '#315eaa' }}; --org-accent: {{ $user->organization?->accent_color ?? '#3687ff' }}">
                <div class="developer-top org-top">
                    @if($user->organization)
                        <img class="org-logo-big" src="{{ $user->organization->logo_url }}" alt="Logo {{ $user->organization->name }}">
                    @else
                        <div class="developer-avatar">@if ($user->avatar_url)<img src="{{ $user->avatar_url }}" alt="Foto profil {{ $user->name }}">@else{{ strtoupper(substr($user->name, 0, 2)) }}@endif</div>
                    @endif
                    <div class="developer-identity">
                        <span>SELAMAT DATANG DI ORGANISASI</span>
                        <h2>{{ $user->organization?->name ?? $user->name }}</h2>
                        <p>{{ $user->organization?->full_name ?? $user->email }}</p>
                    </div>
                    <div class="developer-role"><span>ROLE</span><strong>Organisation</strong>@if($user->organization)<small style="color:#d6e6f7;font-size:9px">{{ $user->organization->focus }}</small>@endif</div>
                </div>
                <div class="developer-details org-details">
                    <div><span>Bidang</span><strong>{{ $user->organization?->focus ?? '—' }}</strong></div>
                    <div><span>Periode</span><strong>{{ $user->organization?->period ?? '—' }}</strong></div>
                    <div><span>Akun organisasi</span><strong>{{ $memberCount }} akun</strong></div>
                </div>
                <div class="welcome-note"><span class="note-icon">✦</span><div><strong>Selamat datang, {{ $user->name }}!</strong><p>{{ $user->organization ? 'Kelola operasional dan kegiatan organisasi melalui modul di bawah.' : 'Akun ini belum terhubung ke organisasi. Hubungi developer untuk menghubungkannya.' }}</p></div></div>
            </article>

            <aside class="workspace-card org-workspace" style="--org-color: {{ $user->organization?->banner_color ?? '#315eaa' }}; --org-accent: {{ $user->organization?->accent_color ?? '#3687ff' }}">
                <div class="workspace-icon">♧</div>
                <p class="workspace-label">APLIKASI MANAGEMENT ORGANISASI</p>
                <h2>Aplikasi Management<br>Organisasi.</h2>
                <p class="workspace-copy">Jalankan kegiatan dan operasional organisasi melalui aplikasi di bawah.</p>
                <a href="#aplikasi" class="workspace-link">Lihat aplikasi <span>↓</span></a>
                <div class="workspace-orbit"></div>
            </aside>
        </section>

        @if(session('status'))<div class="admin-alert" style="margin-top:16px">{{ session('status') }}</div>@endif

        @if($user->organization)
            <section class="org-profile-section" aria-label="Link pendaftaran organisasi">
                <h3 style="margin:0 0 8px;color:#0f2a4d;font-size:13px">Link Pendaftaran Open Recruitment</h3>
                <p style="margin:0 0 12px;color:#64748b;font-size:11px;line-height:1.5">Link ini akan tampil pada card Open Recruitment di landing page hanya setelah disimpan oleh organisasi Anda.</p>
                <form method="POST" action="{{ route('simora.my-registration.update') }}" style="display:flex;gap:8px;flex-wrap:wrap">
                    @csrf @method('PUT')
                    <input name="registration_url" type="url" value="{{ old('registration_url', $user->organization->registration_url) }}" placeholder="https://..." required style="flex:1;min-width:220px;min-height:39px;border:1px solid #dce4ed;border-radius:7px;padding:9px 11px;font-size:11px">
                    <button type="submit" style="min-height:39px;padding:0 14px;border:0;border-radius:7px;background:#1265d8;color:#fff;font-size:11px;font-weight:700;cursor:pointer">Simpan Link</button>
                </form>
                @error('registration_url')<p style="color:#bd3441;font-size:10px;margin:8px 0 0">{{ $message }}</p>@enderror
                @if($user->organization->registration_url)
                    <p style="margin:10px 0 0;font-size:10px;color:#64748b">Link aktif: <a href="{{ $user->organization->registration_url }}" target="_blank" rel="noreferrer">{{ $user->organization->registration_url }}</a></p>
                    <form method="POST" action="{{ route('simora.my-registration.destroy') }}" style="margin-top:8px" onsubmit="return confirm('Hapus link pendaftaran?')">@csrf @method('DELETE')<button type="submit" style="background:none;border:0;color:#b52f39;font-size:10px;font-weight:700;cursor:pointer;padding:0">Hapus link pendaftaran</button></form>
                @endif
            </section>

            <section class="org-profile-section" aria-label="Kontak sosial media organisasi">
                <h3 style="margin:0 0 8px;color:#0f2a4d;font-size:13px">Kontak Sosial Media</h3>
                <p style="margin:0 0 12px;color:#64748b;font-size:11px;line-height:1.5">Pilih ikon sosial media yang ingin ditampilkan, lalu isi link-nya. Ikon yang tidak dipilih tidak akan tampil di halaman Kontak.</p>
                <form method="POST" action="{{ route('simora.my-socials.update') }}" style="display:grid;gap:12px" data-social-picker>
                    @csrf @method('PUT')
                    <div class="social-icon-picker" role="group" aria-label="Pilih sosial media">
                        <button type="button" class="social-pick {{ $user->organization->instagram_url ? 'selected' : '' }}" data-social-target="instagram" aria-pressed="{{ $user->organization->instagram_url ? 'true' : 'false' }}"><img src="https://cdn.simpleicons.org/instagram/315eaa" alt="Instagram"><span>Instagram</span></button>
                        <button type="button" class="social-pick {{ $user->organization->tiktok_url ? 'selected' : '' }}" data-social-target="tiktok" aria-pressed="{{ $user->organization->tiktok_url ? 'true' : 'false' }}"><img src="https://cdn.simpleicons.org/tiktok/315eaa" alt="TikTok"><span>TikTok</span></button>
                        <button type="button" class="social-pick {{ $user->organization->youtube_url ? 'selected' : '' }}" data-social-target="youtube" aria-pressed="{{ $user->organization->youtube_url ? 'true' : 'false' }}"><img src="https://cdn.simpleicons.org/youtube/315eaa" alt="YouTube"><span>YouTube</span></button>
                        <button type="button" class="social-pick {{ $user->organization->whatsapp_url ? 'selected' : '' }}" data-social-target="whatsapp" aria-pressed="{{ $user->organization->whatsapp_url ? 'true' : 'false' }}"><img src="https://cdn.simpleicons.org/whatsapp/315eaa" alt="WhatsApp"><span>WhatsApp</span></button>
                        <button type="button" class="social-pick {{ $user->organization->facebook_url ? 'selected' : '' }}" data-social-target="facebook" aria-pressed="{{ $user->organization->facebook_url ? 'true' : 'false' }}"><img src="https://cdn.simpleicons.org/facebook/315eaa" alt="Facebook"><span>Facebook</span></button>
                        <button type="button" class="social-pick {{ $user->organization->x_url ? 'selected' : '' }}" data-social-target="x" aria-pressed="{{ $user->organization->x_url ? 'true' : 'false' }}"><img src="https://cdn.simpleicons.org/x/315eaa" alt="X"><span>X</span></button>
                        <button type="button" class="social-pick {{ $user->organization->email ? 'selected' : '' }}" data-social-target="email" aria-pressed="{{ $user->organization->email ? 'true' : 'false' }}"><img src="https://cdn.simpleicons.org/gmail/315eaa" alt="Email"><span>Email</span></button>
                        <button type="button" class="social-pick {{ $user->organization->telegram_url ? 'selected' : '' }}" data-social-target="telegram" aria-pressed="{{ $user->organization->telegram_url ? 'true' : 'false' }}"><img src="https://cdn.simpleicons.org/telegram/315eaa" alt="Telegram"><span>Telegram</span></button>
                    </div>
                    <div class="social-link-fields">
                        <label class="social-link-field {{ $user->organization->instagram_url ? '' : 'is-hidden' }}" data-social-field="instagram">Instagram <input name="instagram_url" type="url" value="{{ old('instagram_url', $user->organization->instagram_url) }}" placeholder="https://instagram.com/..."></label>
                        <label class="social-link-field {{ $user->organization->tiktok_url ? '' : 'is-hidden' }}" data-social-field="tiktok">TikTok <input name="tiktok_url" type="url" value="{{ old('tiktok_url', $user->organization->tiktok_url) }}" placeholder="https://tiktok.com/@..."></label>
                        <label class="social-link-field {{ $user->organization->youtube_url ? '' : 'is-hidden' }}" data-social-field="youtube">YouTube <input name="youtube_url" type="url" value="{{ old('youtube_url', $user->organization->youtube_url) }}" placeholder="https://youtube.com/@..."></label>
                        <label class="social-link-field {{ $user->organization->whatsapp_url ? '' : 'is-hidden' }}" data-social-field="whatsapp">WhatsApp <input name="whatsapp_url" type="url" value="{{ old('whatsapp_url', $user->organization->whatsapp_url) }}" placeholder="https://wa.me/..."></label>
                        <label class="social-link-field {{ $user->organization->facebook_url ? '' : 'is-hidden' }}" data-social-field="facebook">Facebook <input name="facebook_url" type="url" value="{{ old('facebook_url', $user->organization->facebook_url) }}" placeholder="https://facebook.com/..."></label>
                        <label class="social-link-field {{ $user->organization->x_url ? '' : 'is-hidden' }}" data-social-field="x">X <input name="x_url" type="url" value="{{ old('x_url', $user->organization->x_url) }}" placeholder="https://x.com/..."></label>
                        <label class="social-link-field {{ $user->organization->email ? '' : 'is-hidden' }}" data-social-field="email">Email <input name="email" type="email" value="{{ old('email', $user->organization->email) }}" placeholder="organisasi@example.com"></label>
                        <label class="social-link-field {{ $user->organization->telegram_url ? '' : 'is-hidden' }}" data-social-field="telegram">Telegram <input name="telegram_url" type="url" value="{{ old('telegram_url', $user->organization->telegram_url) }}" placeholder="https://t.me/..."></label>
                    </div>
                    <button type="submit" style="justify-self:start;min-height:39px;padding:0 14px;border:0;border-radius:7px;background:#1265d8;color:#fff;font-size:11px;font-weight:700;cursor:pointer">Simpan Kontak</button>
                </form>
                <script>
                    document.querySelectorAll('[data-social-picker]').forEach((form) => {
                        form.querySelectorAll('.social-pick').forEach((button) => {
                            button.addEventListener('click', () => {
                                const isSelected = button.classList.toggle('selected');
                                button.setAttribute('aria-pressed', String(isSelected));
                                const field = form.querySelector('[data-social-field="' + button.dataset.socialTarget + '"]');
                                field?.classList.toggle('is-hidden', !isSelected);
                                if (!isSelected) {
                                    const input = field?.querySelector('input');
                                    if (input) input.value = '';
                                }
                            });
                        });
                    });
                </script>
            </section>
        @endif

        @if($user->organization)
            <section class="org-profile-section" aria-label="Visi dan misi organisasi">
                <h3 style="margin:0 0 8px;color:#0f2a4d;font-size:13px">Visi &amp; Misi Organisasi</h3>
                <p class="org-profile-description" style="margin:0 0 14px">{{ $user->organization->description }}</p>
                <p style="margin:0 0 12px;color:#64748b;font-size:11px;line-height:1.5">Isi visi dan misi organisasi Anda. Setelah disimpan, visi dan misi akan tampil di landing publik (kartu organisasi &amp; halaman struktur).</p>
                <form method="POST" action="{{ route('simora.my-vision-mission.update') }}" class="org-vision-form">
                    @csrf @method('PUT')
                    <div class="org-vision-grid">
                        <div class="field-group"><label for="visionInput">Visi</label>
                            <textarea id="visionInput" name="vision" rows="3" placeholder="Tuliskan visi organisasi...">{{ old('vision', $user->organization->vision) }}</textarea>
                            @error('vision')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="field-group"><label for="missionInput">Misi <span>(tulis tiap poin pada baris baru)</span></label>
                            <textarea id="missionInput" name="mission" rows="4" placeholder="Tuliskan misi organisasi...">{{ old('mission', $user->organization->mission) }}</textarea>
                            @error('mission')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <button type="submit" class="org-vision-save">Simpan Visi &amp; Misi</button>
                </form>
            </section>
        @endif

        @if($user->organization && $recentActivities->isNotEmpty())
            <section class="panel-card" aria-label="Kegiatan terbaru">
                <div class="panel-card-head">
                    <h2>Kegiatan Terbaru</h2>
                    <a href="{{ route('simora.e-progress') }}">Lihat Semua →</a>
                </div>
                @foreach ($recentActivities as $activity)
                    <div class="panel-recent">
                        <span class="panel-recent-avatar">{{ strtoupper(substr($activity->name, 0, 2)) }}</span>
                        <span class="panel-recent-body"><strong>{{ $activity->name }}</strong><small>{{ $activity->workProgram?->name ?? 'Tanpa proker' }} · {{ $activity->start_at ? $activity->start_at->locale('id')->translatedFormat('d M Y') : 'Jadwal belum diatur' }}</small></span>
                        <span class="badge blue">{{ ucfirst($activity->status) }}</span>
                    </div>
                @endforeach
            </section>
        @endif

        <section class="modules-section" id="aplikasi">
            <div class="modules-heading"><div class="modules-title"><span class="modules-title-icon" aria-hidden="true">✦</span><h2>Layanan &amp; Sistem Terintegrasi</h2></div><span>3 aplikasi</span></div>
            <div class="module-grid">
                <a class="module-card orgmate" href="{{ route('simora.orgmate') }}"><span class="module-symbol"><img src="{{ asset('assets/orgmate-logo.png') }}" alt="Logo ORGMATE" class="module-logo"></span><span><span class="module-index">APLIKASI 01</span><h3>ORGMATE</h3><p>Manajemen Organisasi dan Keanggotaan (Master Data)</p></span><span class="module-arrow">↗</span></a>
                <a class="module-card eprogress" href="{{ route('simora.e-progress') }}"><span class="module-symbol"><img src="{{ asset('assets/e-progress-logo.png') }}" alt="Logo E-PROGRESS" class="module-logo"></span><span><span class="module-index">APLIKASI 02</span><h3>E-PROGRESS</h3><p>Manajemen Kegiatan dan Program Kerja</p></span><span class="module-arrow">↗</span></a>
                <a class="module-card sentra" href="{{ route('simora.sentra') }}"><span class="module-symbol"><img src="{{ asset('assets/sentra-logo.png') }}" alt="Logo SENTRA" class="module-logo"></span><span><span class="module-index">APLIKASI 03</span><h3>SENTRA</h3><p>Manajemen Keuangan</p></span><span class="module-arrow">↗</span></a>
            </div>
        </section>
    </main>

    <footer class="panel-footer"><span>SIMORA · Fakultas Teknik Universitas Nurul Jadid</span><span>Kolaborasi <i>•</i> Inovasi <i>•</i> Prestasi</span></footer>

    <script>
        // Animasi angka statistik (count-up).
        document.querySelectorAll('[data-count]').forEach((el) => {
            const raw = el.dataset.count || '0';
            const [target, total] = raw.split('/').map(Number);
            const duration = 700;
            const start = performance.now();
            const step = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const value = Math.round(target * progress).toLocaleString('id-ID');
                el.textContent = total !== undefined ? value + '/' + total.toLocaleString('id-ID') : value;
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        });
    </script>
</body>
</html>
