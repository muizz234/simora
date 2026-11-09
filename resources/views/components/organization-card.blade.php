@props(['organization', 'index'])

<article class="organization-card"
    style="--org-color: {{ $organization->banner_color }}; --org-accent: {{ $organization->accent_color }}; --card-index: {{ $index }}"
    data-organization-card data-search-text="{{ strtolower($organization->name . ' ' . $organization->full_name) }}">
    <a href="{{ route('struktur.show', $organization) }}" style="display:contents"
        aria-label="Lihat struktur {{ $organization->name }}">
        <div class="org-visual">
            <div class="officer-photo-grid">
                @foreach ([['key' => 'ketua', 'title' => 'Ketua Umum'], ['key' => 'wakil', 'title' => 'Wakil Ketua Umum'], ['key' => 'sekretaris', 'title' => 'Sekretaris Umum'], ['key' => 'bendahara', 'title' => 'Bendahara Umum']] as $officer)
                    <figure class="officer-photo-card">
                        <img src="{{ asset('assets/pengurus/' . $organization->slug . '-' . $officer['key'] . '.jpg') }}"
                            alt="Foto {{ $officer['title'] }} {{ $organization->name }}" loading="lazy"
                            onerror="this.hidden = true">
                        <span class="photo-placeholder" aria-hidden="true"><span>＋</span></span>
                        <figcaption>{{ $officer['title'] }}</figcaption>
                    </figure>
                @endforeach
            </div>
            <div class="org-photo-caption"><span>ORGANISASI
                    {{ str_pad((string) $index, 2, '0', STR_PAD_LEFT) }}</span><span>FAKULTAS TEKNIK / UNUJA</span>
            </div>
        </div>
        <div class="org-information">
            <div class="org-heading"><span class="org-overline">ORGANISASI MAHASISWA ·
                    {{ strtoupper($organization->focus) }}</span>
                <h3>{{ $organization->name }}</h3>
                <p>{{ $organization->full_name }}</p>
            </div>
            <div class="org-logo-wrap"><img src="{{ $organization->logo_url }}" alt="Logo {{ $organization->name }}"
                    loading="lazy"></div>
            <p class="org-note">{{ $organization->description }}</p>
            @if ($organization->vision || $organization->mission)
                <details class="org-vision">
                    <summary>Lihat visi &amp; misi</summary>
                    <div>
                        @if ($organization->vision)
                            <p><strong>Visi</strong>{{ $organization->vision }}</p>
                            @endif @if ($organization->mission)
                                <p><strong>Misi</strong>{{ $organization->mission }}</p>
                            @endif
                    </div>
                </details>
            @endif
            <div class="org-bottom"><span>PERIODE · {{ $organization->period ?? '—' }}</span><span>Lihat Struktur
                    <b>↗</b></span></div>
        </div>
    </a>
</article>
