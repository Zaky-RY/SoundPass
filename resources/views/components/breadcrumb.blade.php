@props([
    'items' => [],
])

<nav aria-label="Breadcrumb" class="mb-6">
    <ol class="flex flex-wrap items-center gap-2 text-sm text-slate-600">
        <li>
            <a href="{{ route('concerts.index') }}" class="flex items-center gap-1.5 text-slate-600 hover:text-indigo-600 font-medium transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Beranda</span>
            </a>
        </li>

        @foreach ($items as $item)
            <li class="flex items-center gap-2">
                <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                @if (!empty($item['url']) && !$loop->last)
                    <a href="{{ $item['url'] }}" class="text-slate-600 hover:text-indigo-600 font-medium transition truncate max-w-xs sm:max-w-md">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="font-semibold text-slate-900 truncate max-w-xs sm:max-w-md" aria-current="page">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
