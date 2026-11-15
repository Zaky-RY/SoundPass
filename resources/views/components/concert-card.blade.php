@props([
    'concert',
])

@php
    $isSoldOut = $concert->ticket_status === 'Sold Out';
@endphp

<article class="group relative flex flex-col overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md hover:border-slate-300 transition duration-200">
    <!-- Poster Container -->
    <div class="relative aspect-[16/10] w-full overflow-hidden bg-slate-100">
        @if ($concert->poster_url)
            <img
                src="{{ $concert->poster_url }}"
                alt="Poster resmi konser {{ $concert->title }}"
                loading="lazy"
                class="h-full w-full object-cover object-center group-hover:scale-105 transition duration-500 {{ $isSoldOut ? 'grayscale contrast-75' : '' }}"
            >
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 text-white">
                <svg class="h-12 w-12 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
            </div>
        @endif

        <!-- Floating Badges -->
        <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
            <span class="inline-flex items-center rounded-full bg-slate-900/80 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur">
                {{ $concert->genre }}
            </span>
            <span class="inline-flex items-center rounded-full bg-white/90 px-2.5 py-1 text-xs font-semibold text-slate-800 backdrop-blur shadow-sm">
                {{ $concert->city }}
            </span>
        </div>

        <div class="absolute top-3 right-3">
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold border backdrop-blur shadow-sm {{ $concert->ticket_status_badge_classes }}">
                {{ $concert->ticket_status }}
            </span>
        </div>
    </div>

    <!-- Content Area -->
    <div class="flex flex-1 flex-col p-5">
        <p class="text-xs font-semibold tracking-wider uppercase text-indigo-600">
            {{ $concert->headliner }}
        </p>
        <h3 class="mt-1 text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition line-clamp-2">
            <a href="{{ route('concerts.show', $concert) }}">
                <span class="absolute inset-0 z-0"></span>
                {{ $concert->title }}
            </a>
        </h3>

        <!-- Meta Info (Date, Venue) -->
        <div class="mt-4 space-y-2 border-t border-slate-100 pt-3 text-xs text-slate-600">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ $concert->event_date->translatedFormat('d F Y') }} &bull; Open Gate {{ $concert->open_gate_time }}</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="truncate">{{ $concert->venue_name }}, {{ $concert->city }}</span>
            </div>
        </div>

        <!-- Footer Card (Price & CTA) -->
        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
            <div>
                <p class="text-[11px] text-slate-600 uppercase tracking-wider">Mulai dari</p>
                <p class="text-base font-extrabold text-slate-900">
                    @rupiah($concert->starting_price)
                </p>
            </div>

            <div class="relative z-10">
                @if ($isSoldOut)
                    <span class="inline-flex items-center rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600 border border-slate-200 cursor-not-allowed">
                        Sold Out
                    </span>
                @else
                    <a
                        href="{{ route('concerts.show', $concert) }}"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition"
                    >
                        Beli Tiket
                    </a>
                @endif
            </div>
        </div>
    </div>
</article>
