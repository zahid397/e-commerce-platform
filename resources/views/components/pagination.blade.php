@props(['paginator'])
@if($paginator->total() > 0)
    <nav class="pagination" aria-label="Pagination">
        <span>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} of {{ number_format($paginator->total()) }}</span>
        @if($paginator->hasPages())
            <div class="pagination-controls">
                @if($paginator->previousPageUrl())<a class="button button-sm button-secondary" href="{{ $paginator->previousPageUrl() }}" rel="prev"><x-icon name="arrow-left" :size="15"/> Previous</a>@endif
                <span>Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
                @if($paginator->nextPageUrl())<a class="button button-sm button-secondary" href="{{ $paginator->nextPageUrl() }}" rel="next">Next <x-icon name="arrow-right" :size="15"/></a>@endif
            </div>
        @endif
    </nav>
@endif
