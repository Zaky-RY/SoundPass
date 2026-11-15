@extends('layouts.app')

@section('title', 'Jelajah Jadwal Konser & Festival Musik — SoundPass')

@section('content')
<div class="space-y-8">

    <!-- Hero Header & Search Section -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-8 sm:p-12 text-white shadow-xl">
        <!-- Background Decorative Orbs -->
        <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-violet-500/20 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-3xl space-y-4">
            <span class="inline-flex items-center gap-2 rounded-full bg-indigo-500/20 border border-indigo-400/30 px-3.5 py-1 text-xs font-semibold text-indigo-300 backdrop-blur">
                <span class="h-2 w-2 rounded-full bg-indigo-400 animate-pulse"></span>
                Official Ticketing Partner 2026
            </span>
            <h1 class="text-3xl font-extrabold tracking-tight sm:text-5xl leading-tight">
                Rasakan Dentuman Musik di Panggung Favoritmu.
            </h1>
            <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl">
                Temukan jadwal konser dan festival musik terbaru. Nikmati pemesanan instan, alokasi kursi transparan, dan jaminan e-ticket ber-QR Code dengan verifikasi NIK resmi.
            </p>

            <!-- Search Form -->
            <form method="GET" action="{{ route('concerts.index') }}" class="pt-2">
                @if (request('genre'))
                    <input type="hidden" name="genre" value="{{ request('genre') }}">
                @endif
                @if (request('city'))
                    <input type="hidden" name="city" value="{{ request('city') }}">
                @endif
                @if (request('time_range'))
                    <input type="hidden" name="time_range" value="{{ request('time_range') }}">
                @endif

                <div class="flex flex-col sm:flex-row items-stretch gap-2 rounded-2xl bg-white/10 p-2 backdrop-blur-md border border-white/20 shadow-lg">
                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            type="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Cari konser, nama artis / headliner, atau venue..."
                            class="block w-full rounded-xl bg-transparent pl-10 pr-4 py-3 text-sm text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        >
                    </div>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-md hover:bg-indigo-500 transition"
                    >
                        Cari Konser
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filters Bar (Genre, City, Time Range) -->
    <div class="space-y-4 rounded-2xl bg-white p-5 border border-slate-200 shadow-sm">
        <!-- Genre Tabs -->
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Kategori Genre</span>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('concerts.index', array_merge(request()->except('genre', 'page'))) }}"
                    class="rounded-xl px-3.5 py-1.5 text-xs font-semibold transition {{ empty($selectedGenre) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                >
                    Semua Genre
                </a>
                @foreach ($genres as $genreOption)
                    <a
                        href="{{ route('concerts.index', array_merge(request()->except('genre', 'page'), ['genre' => $genreOption])) }}"
                        class="rounded-xl px-3.5 py-1.5 text-xs font-semibold transition {{ $selectedGenre === $genreOption ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                    >
                        {{ $genreOption }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-100 pt-3 flex flex-wrap items-center justify-between gap-4">
            <!-- Filter Kota -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600 mr-1">Kota:</span>
                <a
                    href="{{ route('concerts.index', array_merge(request()->except('city', 'page'))) }}"
                    class="rounded-lg px-2.5 py-1 text-xs font-medium transition {{ empty($selectedCity) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}"
                >
                    Semua Kota
                </a>
                @foreach ($cities as $cityOption)
                    <a
                        href="{{ route('concerts.index', array_merge(request()->except('city', 'page'), ['city' => $cityOption])) }}"
                        class="rounded-lg px-2.5 py-1 text-xs font-medium transition {{ $selectedCity === $cityOption ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        {{ $cityOption }}
                    </a>
                @endforeach
            </div>

            <!-- Filter Rentang Waktu -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600 mr-1">Waktu:</span>
                @php
                    $timeRanges = [
                        '' => 'Semua',
                        'minggu_ini' => 'Minggu Ini',
                        'bulan_ini' => 'Bulan Ini',
                        'mendatang' => 'Mendatang',
                    ];
                @endphp
                @foreach ($timeRanges as $key => $label)
                    <a
                        href="{{ route('concerts.index', array_merge(request()->except('time_range', 'page'), $key ? ['time_range' => $key] : [])) }}"
                        class="rounded-lg px-2.5 py-1 text-xs font-medium transition {{ ($selectedTimeRange === $key || (empty($selectedTimeRange) && $key === '')) ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach

                @if ($selectedGenre || $selectedCity || $selectedTimeRange || $search)
                    <a
                        href="{{ route('concerts.index') }}"
                        class="ml-2 inline-flex items-center gap-1 text-xs font-semibold text-rose-600 hover:text-rose-700 transition"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Reset Filter</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Results Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Katalog Konser Musik</h2>
            <p class="text-xs text-slate-600 mt-0.5">
                Menampilkan {{ $concerts->total() }} acara konser yang tersedia untuk dipesan.
            </p>
        </div>
    </div>

    <!-- Concerts Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($concerts as $concert)
            <x-concert-card :concert="$concert" />
        @empty
            <div class="col-span-full rounded-2xl bg-white border border-slate-200 p-12 text-center shadow-sm">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-slate-900">Tidak ada konser ditemukan</h3>
                <p class="mt-1 text-xs text-slate-600 max-w-sm mx-auto">
                    Kombinasi kata kunci atau filter yang Anda pilih saat ini tidak memiliki jadwal acara. Silakan atur ulang pencarian Anda.
                </p>
                <div class="mt-6">
                    <a
                        href="{{ route('concerts.index') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition"
                    >
                        Tampilkan Semua Konser
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($concerts->hasPages())
        <div class="pt-6">
            {{ $concerts->links() }}
        </div>
    @endif

</div>
@endsection
