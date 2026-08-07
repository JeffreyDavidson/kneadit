@if ($paginator->hasPages())
    <nav
        role="navigation"
        aria-label="{{ __('Pagination Navigation') }}"
        class="flex items-center justify-between gap-2"
    >
        @if ($paginator->onFirstPage())
            <span class="inline-flex cursor-not-allowed items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm leading-5 font-medium text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a
                href="{{ $paginator->previousPageUrl() }}"
                rel="prev"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm leading-5 font-medium text-gray-800 ring-gray-300 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-700 focus:border-blue-300 focus:ring focus:outline-none active:bg-gray-100 active:text-gray-800 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-900 dark:hover:text-gray-200 dark:focus:border-blue-700 dark:active:bg-gray-700 dark:active:text-gray-300"
            >
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                rel="next"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm leading-5 font-medium text-gray-800 ring-gray-300 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-700 focus:border-blue-300 focus:ring focus:outline-none active:bg-gray-100 active:text-gray-800 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-900 dark:hover:text-gray-200 dark:focus:border-blue-700 dark:active:bg-gray-700 dark:active:text-gray-300"
            >
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex cursor-not-allowed items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm leading-5 font-medium text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
