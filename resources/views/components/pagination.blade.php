@props(['paginator'])

@if ($paginator->hasPages())
    <div class="ldr-pagination">
        <p class="ldr-pagination__meta">
            {{ __('ui.common.showing', [
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ]) }}
        </p>

        <nav class="ldr-pagination__nav" aria-label="Navigasi halaman">
            @if ($paginator->onFirstPage())
                <span class="ldr-pagination__link is-disabled" aria-disabled="true">
                    <x-icon name="chevron-left" :size="18" />
                    <span>{{ __('ui.common.previous') }}</span>
                </span>
            @else
                <a class="ldr-pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    <x-icon name="chevron-left" :size="18" />
                    <span>{{ __('ui.common.previous') }}</span>
                </a>
            @endif

            <ul class="ldr-pagination__pages">
                @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                    <li>
                        @if ($page === $paginator->currentPage())
                            <span class="ldr-pagination__page is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="ldr-pagination__page" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    </li>
                @endforeach
            </ul>

            @if ($paginator->hasMorePages())
                <a class="ldr-pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    <span>{{ __('ui.common.next') }}</span>
                    <x-icon name="chevron-right" :size="18" />
                </a>
            @else
                <span class="ldr-pagination__link is-disabled" aria-disabled="true">
                    <span>{{ __('ui.common.next') }}</span>
                    <x-icon name="chevron-right" :size="18" />
                </span>
            @endif
        </nav>
    </div>
@endif
