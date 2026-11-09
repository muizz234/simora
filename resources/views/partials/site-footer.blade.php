@php $footerSetting = $footerSetting ?? \App\Models\FooterSetting::current(); @endphp
<footer class="site-footer" id="kontak">
    <div class="footer-top-edge" aria-hidden="true"></div>
    <div class="footer-inner">
        <div class="footer-identity">
            <img src="{{ asset('assets/Logo FT.png') }}" alt="Logo Fakultas Teknik">
            <span class="footer-divider" aria-hidden="true"></span>
            <span class="footer-identity-text"><strong>Fakultas
                    Teknik</strong><small>{{ $footerSetting->identity_text }}</small></span>
        </div>
        <div class="footer-circuit" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
        <nav class="footer-nav" aria-label="Navigasi footer">
            <a href="#beranda">Beranda</a><a href="{{ route('struktur.index') }}">Struktural</a><a
                href="{{ route('kegiatan.index') }}">Kegiatan</a><a href="{{ route('kontak') }}">Kontak</a>
        </nav>
        <div class="footer-social-block">
            <span class="footer-social-divider" aria-hidden="true"></span>
            <div class="footer-social">
                <a href="{{ $footerSetting->instagram_url }}" aria-label="Instagram Fakultas Teknik" target="_blank"
                    rel="noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="5"></rect>
                        <circle cx="12" cy="12" r="4"></circle>
                        <circle cx="17.2" cy="6.8" r="0.8" fill="currentColor"></circle>
                    </svg></a>
                <a href="{{ $footerSetting->tiktok_url }}" aria-label="TikTok Fakultas Teknik" target="_blank"
                    rel="noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14.5 4v9.2a3.7 3.7 0 1 1-3.2-3.66"></path>
                        <path d="M14.5 4c.4 2.3 1.8 3.9 4.5 4.1"></path>
                    </svg></a>
                <a href="{{ $footerSetting->website_url }}" aria-label="Website Universitas Nurul Jadid" target="_blank"
                    rel="noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M3 12h18M12 3c2.4 2.6 2.4 15.4 0 18M12 3c-2.4 2.6-2.4 15.4 0 18"></path>
                    </svg></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom"><small class="copyright">© {{ date('Y') }} Fakultas Teknik Universitas Nurul
            Jadid</small></div>
    <div class="footer-corner" aria-hidden="true"></div>
</footer>
