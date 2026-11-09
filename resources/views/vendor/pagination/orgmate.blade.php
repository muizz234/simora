@if ($paginator->hasPages())
    <nav class="orgmate-pagination" aria-label="Navigasi halaman">
        @if ($paginator->onFirstPage())
            <span class="orgmate-page-btn is-disabled">‹ Sebelumnya</span>
        @else
            <a class="orgmate-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Sebelumnya</a>
        @endif

        @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if ($page == $paginator->currentPage())
                <span class="orgmate-page-btn is-active" aria-current="page">{{ $page }}</span>
            @else
                <a class="orgmate-page-btn" href="{{ $url }}">{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="orgmate-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya ›</a>
        @else
            <span class="orgmate-page-btn is-disabled">Berikutnya ›</span>
        @endif
    </nav>
@endif
