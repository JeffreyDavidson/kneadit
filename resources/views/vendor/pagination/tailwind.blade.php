@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">

        {{-- Mobile: Previous / Next --}}
        <div class="flex gap-3 items-center justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-5 py-2.5 text-sm font-medium rounded-full cursor-not-allowed" style="background: var(--warm-200); color: var(--warm-400);">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-5 py-2.5 text-sm font-medium rounded-full transition-all duration-200 hover:scale-105" style="background: var(--warm-800); color: var(--warm-200);">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            <span class="text-sm font-medium" style="color: var(--warm-500);">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-5 py-2.5 text-sm font-medium rounded-full transition-all duration-200 hover:scale-105" style="background: var(--warm-800); color: var(--warm-200);">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="inline-flex items-center px-5 py-2.5 text-sm font-medium rounded-full cursor-not-allowed" style="background: var(--warm-200); color: var(--warm-400);">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="hidden sm:flex sm:flex-col sm:items-center sm:gap-4">
            <div class="flex items-center gap-2">

                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full cursor-not-allowed transition-all duration-200" style="background: var(--warm-200); color: var(--warm-400);" aria-hidden="true">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-10 h-10 rounded-full transition-all duration-200 hover:scale-110" style="background: var(--warm-200); color: var(--warm-700);" aria-label="{{ __('pagination.previous') }}"
                       onmouseover="this.style.background='var(--warm-800)';this.style.color='var(--warm-200)'"
                       onmouseout="this.style.background='var(--warm-200)';this.style.color='var(--warm-700)'">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif

                {{-- Page Numbers --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true">
                            <span class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium" style="color: var(--warm-400);">{{ $element }}</span>
                        </span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page">
                                    <span class="inline-flex items-center justify-center w-10 h-10 text-sm font-bold rounded-full" style="background: var(--warm-500); color: var(--warm-900);">{{ $page }}</span>
                                </span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium rounded-full transition-all duration-200 hover:scale-110" style="background: transparent; color: var(--warm-600);"
                                   onmouseover="this.style.background='var(--warm-200)';this.style.color='var(--warm-800)'"
                                   onmouseout="this.style.background='transparent';this.style.color='var(--warm-600)'"
                                   aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-10 h-10 rounded-full transition-all duration-200 hover:scale-110" style="background: var(--warm-200); color: var(--warm-700);" aria-label="{{ __('pagination.next') }}"
                       onmouseover="this.style.background='var(--warm-800)';this.style.color='var(--warm-200)'"
                       onmouseout="this.style.background='var(--warm-200)';this.style.color='var(--warm-700)'">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full cursor-not-allowed" style="background: var(--warm-200); color: var(--warm-400);" aria-hidden="true">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </span>
                @endif
            </div>

            <p class="text-sm" style="color: var(--warm-500);">
                Showing {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }}
            </p>
        </div>
    </nav>
@endif
