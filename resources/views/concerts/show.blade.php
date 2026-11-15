@extends('layouts.app')

@section('title', $concert->title . ' — SoundPass')

@section('content')
@php
    $isSoldOut = $concert->ticket_status === 'Sold Out';
@endphp

<div x-data="{ mapModal: false }" class="space-y-8 pb-20 md:pb-0">

    <!-- Breadcrumbs -->
    <x-breadcrumb :items="[
        ['label' => $concert->title, 'url' => null]
    ]" />

    <!-- Hero Concert Overview -->
    <div class="overflow-hidden rounded-3xl bg-white border border-slate-200 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 sm:p-8 lg:p-10">
            <!-- Left: Big Poster -->
            <div class="lg:col-span-5 flex flex-col">
                <div class="relative aspect-[3/4] w-full overflow-hidden rounded-2xl bg-slate-100 shadow-md">
                    @if ($concert->poster_url)
                        <img
                            src="{{ $concert->poster_url }}"
                            alt="Poster resmi {{ $concert->title }}"
                            class="h-full w-full object-cover object-center {{ $isSoldOut ? 'grayscale contrast-75' : '' }}"
                        >
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 text-white">
                            <svg class="h-16 w-16 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                        </div>
                    @endif

                    <!-- Floating Status Badge -->
                    <div class="absolute top-4 right-4">
                        <span class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-bold border backdrop-blur shadow-sm {{ $concert->ticket_status_badge_classes }}">
                            {{ $concert->ticket_status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Event Highlights & Quick Info -->
            <div class="lg:col-span-7 flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <!-- Badges -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700">
                            {{ $concert->genre }}
                        </span>
                        <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            {{ $concert->city }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                            <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            Maks. 4 Tiket / NIK
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        {{ $concert->title }}
                    </h1>

                    <div class="flex items-center gap-2 text-slate-700">
                        <span class="text-xs uppercase font-bold tracking-wider text-indigo-600">Headliner:</span>
                        <span class="text-base font-bold text-slate-900">{{ $concert->headliner }}</span>
                    </div>

                    <!-- Meta Grid (Date, Venue) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4 border border-slate-100">
                            <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs font-semibold text-slate-600">Tanggal & Waktu</p>
                                <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $concert->event_date->translatedFormat('l, d F Y') }}</p>
                                <p class="text-xs text-slate-600 mt-0.5">Open Gate: {{ $concert->open_gate_time }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4 border border-slate-100">
                            <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs font-semibold text-slate-600">Lokasi Acara</p>
                                <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $concert->venue_name }}</p>
                                <p class="text-xs text-slate-600 mt-0.5">{{ $concert->city }}, Indonesia</p>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="pt-4 border-t border-slate-100">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Deskripsi Acara</h2>
                        <p class="text-sm leading-relaxed text-slate-700 whitespace-pre-line">
                            {{ $concert->description }}
                        </p>
                    </div>
                </div>

                <!-- Desktop CTA Box -->
                <div class="hidden md:flex items-center justify-between gap-6 rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 p-6 text-white shadow-md">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-indigo-300">Harga Tiket Mulai Dari</p>
                        <p class="text-2xl font-black text-white mt-0.5">
                            @rupiah($concert->starting_price)
                        </p>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Total Kuota Tersisa: <strong class="text-white">{{ $concert->total_remaining_quota }} tiket</strong>
                        </p>
                    </div>

                    <div>
                        @if ($isSoldOut)
                            <button
                                type="button"
                                disabled
                                class="inline-flex items-center justify-center rounded-xl bg-slate-700 px-6 py-3.5 text-sm font-bold text-slate-400 cursor-not-allowed border border-slate-600"
                            >
                                Tiket Habis (Sold Out)
                            </button>
                        @else
                            <a
                                href="{{ route('bookings.create', $concert) }}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 transition active:scale-95"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                <span>Beli Tiket Sekarang</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Tiers & Stage Map Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Col Left (8): Daftar Kategori Tiket & Fasilitas -->
        <div class="lg:col-span-8 space-y-6">
            <div class="rounded-3xl bg-white p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Kategori Tiket & Fasilitas</h2>
                    <p class="text-xs text-slate-600 mt-1">
                        Pilih zona menonton yang Anda inginkan. Sisa kuota diperbarui secara real-time dan dikunci aman dengan sistem atomik saat pemesanan.
                    </p>
                </div>

                <div class="space-y-4">
                    @foreach ($concert->ticketTiers as $tier)
                        @php
                            $tierSoldOut = $tier->remaining_quota <= 0;
                        @endphp
                        <div class="relative rounded-2xl border p-5 transition {{ $tierSoldOut ? 'bg-slate-50 border-slate-200 opacity-70' : 'bg-white border-slate-200 hover:border-indigo-300 shadow-sm' }}">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-base font-bold text-slate-900">{{ $tier->name }}</h3>
                                        @if ($tierSoldOut)
                                            <span class="rounded-md bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-800 border border-rose-200">
                                                SOLD OUT
                                            </span>
                                        @elseif ($tier->remaining_quota <= 25)
                                            <span class="rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 border border-amber-200">
                                                Tersisa {{ $tier->remaining_quota }} Tiket
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed max-w-lg">
                                        {{ $tier->description ?? 'Akses resmi penonton konser untuk kategori ' . $tier->name }}
                                    </p>
                                </div>

                                <div class="text-left sm:text-right flex-shrink-0">
                                    <p class="text-lg font-extrabold text-slate-900">
                                        @rupiah($tier->price)
                                    </p>
                                    <p class="text-[11px] text-slate-600 mt-0.5">
                                        @if ($tierSoldOut)
                                            <span class="text-rose-600 font-semibold">Habis Terjual</span>
                                        @else
                                            Sisa Kuota: <strong class="text-slate-800">{{ $tier->remaining_quota }}</strong> / {{ $tier->total_quota }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Syarat & Ketentuan Acara -->
            <div class="rounded-3xl bg-white p-6 sm:p-8 border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </span>
                    <h2 class="text-base font-bold text-slate-900">Syarat & Regulasi Konser</h2>
                </div>

                <ul class="space-y-3 text-xs leading-relaxed text-slate-600 list-disc list-inside">
                    <li><strong class="text-slate-800">Verifikasi NIK Fisik:</strong> Penonton wajib membawa Kartu Identitas Fisik asli (KTP / Paspor) yang nomornya tertera pada e-ticket saat penukaran wristband di gate venue.</li>
                    <li><strong class="text-slate-800">Batas Maksimal Pembelian:</strong> Setiap 1 Nomor Induk Kependudukan (NIK) dibatasi maksimal membeli 4 tiket per transaksi guna mencegah praktik calo.</li>
                    <li><strong class="text-slate-800">Barang Terlarang:</strong> Dilarang keras membawa kamera profesional (DSLR / Mirrorless / Lensa Tele), tongsis/monopod, senjata tajam, minuman beralkohol, serta zat terlarang ke dalam area pertunjukan.</li>
                    <li><strong class="text-slate-800">Non-Refundable:</strong> Tiket yang telah berhasil dibeli dan terbit tidak dapat dikembalikan, ditukar, atau diuangkan kembali (kecuali terdapat pembatalan resmi dari pihak promotor).</li>
                    <li><strong class="text-slate-800">Kedatangan di Venue:</strong> Pintu gerbang dibuka sesuai jam Open Gate ({{ $concert->open_gate_time }}). Dihimbau hadir minimal 90 menit lebih awal untuk kelancaran antrean body checking dan penukaran gelang wristband.</li>
                </ul>
            </div>
        </div>

        <!-- Col Right (4): Stage & Seat Map Layout -->
        <div class="lg:col-span-4 space-y-6">
            <div class="rounded-3xl bg-white p-6 border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900">Denah Panggung & Kursi</h2>
                    <span class="text-[11px] font-medium text-indigo-600">Stage Layout</span>
                </div>

                <div class="relative group cursor-pointer overflow-hidden rounded-2xl bg-slate-100 border border-slate-200" @click="mapModal = true">
                    @if ($concert->stage_map_url)
                        <img
                            src="{{ $concert->stage_map_url }}"
                            alt="Denah panggung {{ $concert->title }}"
                            class="w-full h-56 object-cover object-center group-hover:scale-105 transition duration-300"
                        >
                    @else
                        <div class="flex h-56 w-full items-center justify-center bg-slate-200 text-slate-400">
                            <span class="text-xs">Gambar denah belum tersedia</span>
                        </div>
                    @endif

                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center backdrop-blur-xs">
                        <span class="rounded-xl bg-white/95 px-3 py-1.5 text-xs font-bold text-slate-900 shadow">
                            Perbesar Denah (Lightbox)
                        </span>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Klik gambar denah di atas untuk melihat pembagian blok kategori dan tata letak gerbang masuk venue.
                </p>
            </div>

            <!-- Fast Summary Box -->
            <div class="rounded-3xl bg-indigo-50 border border-indigo-100 p-6 space-y-4">
                <h3 class="text-sm font-bold text-indigo-950">Informasi Pemesanan</h3>
                <ul class="space-y-2 text-xs text-indigo-900/80">
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-indigo-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>E-Ticket SVG QR Code instan</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-indigo-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Pajak hiburan 10% & admin Rp 10.000/tiket</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-indigo-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Cetak & simpan salinan e-ticket PDF</span>
                    </li>
                </ul>

                @if (!$isSoldOut)
                    <div class="pt-2">
                        <a
                            href="{{ route('bookings.create', $concert) }}"
                            class="block w-full text-center rounded-xl bg-indigo-600 py-3 text-xs font-bold text-white shadow hover:bg-indigo-700 transition"
                        >
                            Lanjutkan ke Pemilihan Tiket
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stage Map Lightbox Modal (Alpine.js) -->
    <div
        x-show="mapModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/80 backdrop-blur-md flex items-center justify-center p-4"
        @keydown.escape.window="mapModal = false"
    >
        <div
            @click.away="mapModal = false"
            x-show="mapModal"
            x-transition
            class="relative w-full max-w-4xl rounded-2xl bg-white p-4 shadow-2xl border border-slate-700"
        >
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Stage & Seat Map: {{ $concert->title }}</h3>
                <button type="button" @click="mapModal = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-3 max-h-[75vh] overflow-auto rounded-xl bg-slate-50 flex items-center justify-center">
                @if ($concert->stage_map_url)
                    <img
                        src="{{ $concert->stage_map_url }}"
                        alt="Denah panggung {{ $concert->title }}"
                        class="w-full h-auto max-h-[70vh] object-contain"
                    >
                @endif
            </div>
        </div>
    </div>

    <!-- Mobile Sticky Bottom Bar (Thumb-friendly CTA per DESIGN.md) -->
    <div class="fixed bottom-0 inset-x-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur-md p-4 md:hidden shadow-2xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-[10px] text-slate-600 uppercase tracking-wider">Mulai dari</p>
                <p class="text-base font-black text-slate-900">
                    @rupiah($concert->starting_price)
                </p>
            </div>

            <div>
                @if ($isSoldOut)
                    <button
                        type="button"
                        disabled
                        class="rounded-xl bg-slate-200 px-5 py-3 text-xs font-bold text-slate-600 cursor-not-allowed"
                    >
                        Sold Out
                    </button>
                @else
                    <a
                        href="{{ route('bookings.create', $concert) }}"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-6 py-3 text-xs font-bold text-white shadow-md hover:bg-indigo-700 transition"
                    >
                        Beli Tiket
                    </a>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
