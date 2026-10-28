@if ($paginator->hasPages())
<nav class="pagination" role="navigation" aria-label="Navigasi halaman">
    @if ($paginator->onFirstPage())<span class="pagination-arrow disabled" aria-disabled="true"><x-icon name="arrow-left" width="16" height="16"/><span>Sebelumnya</span></span>@else<a class="pagination-arrow" href="{{ $paginator->previousPageUrl() }}" rel="prev"><x-icon name="arrow-left" width="16" height="16"/><span>Sebelumnya</span></a>@endif
    <div class="pagination-pages">@foreach ($elements as $element)@if (is_string($element))<span class="pagination-ellipsis">{{ $element }}</span>@endif @if (is_array($element)) @foreach ($element as $page => $url) @if ($page == $paginator->currentPage())<span class="pagination-page current" aria-current="page">{{ $page }}</span>@else<a class="pagination-page" href="{{ $url }}" aria-label="Ke halaman {{ $page }}">{{ $page }}</a>@endif @endforeach @endif @endforeach</div>
    @if ($paginator->hasMorePages())<a class="pagination-arrow" href="{{ $paginator->nextPageUrl() }}" rel="next"><span>Berikutnya</span><x-icon name="arrow-right" width="16" height="16"/></a>@else<span class="pagination-arrow disabled" aria-disabled="true"><span>Berikutnya</span><x-icon name="arrow-right" width="16" height="16"/></span>@endif
</nav>
@endif
