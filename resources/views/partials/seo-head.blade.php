{{-- Meta SEO + performa bersama. Variabel opsional: $seoTitle, $seoDescription, $seoImage --}}
@php
    $seoTitle = $seoTitle ?? ($title ?? (config('app.name', 'SIMORA') . ' | Fakultas Teknik UNUJA'));
    $seoDescription = $seoDescription ?? 'SIMORA — Sistem Informasi Organisasi Kemahasiswaan Fakultas Teknik Universitas Nurul Jadid: kegiatan, struktural, ORGMATE, E-PROGRESS, dan SENTRA.';
    $seoImage = $seoImage ?? asset('assets/SIMORA.png');
    $canonicalUrl = $canonicalUrl ?? url()->current();
@endphp
<meta name="description" content="{{ $seoDescription }}">
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ config('app.name', 'SIMORA') }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">
