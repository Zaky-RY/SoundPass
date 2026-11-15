<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SoundPass — Sistem Pemesanan Tiket Konser & Festival Musik')</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>

    <!-- Vite Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="flex min-h-full flex-col text-slate-800 antialiased" x-data="{ checkTicketModal: false, bookingCodeInput: '' }">

    <!-- Sticky Navbar -->
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur-md transition-all print:hidden">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <!-- Brand Logo -->
            <div class="flex items-center gap-8">
                <a href="{{ route('concerts.index') }}" class="flex items-center gap-2.5 group">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-200 group-hover:scale-105 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </span>
                    <div class="leading-tight">
                        <span class="text-xl font-extrabold tracking-tight text-slate-900 group-hover:text-indigo-600 transition">Sound<span class="text-indigo-600">Pass</span></span>
                        <span class="block text-[10px] font-semibold uppercase tracking-widest text-slate-600">Official Concert Tickets</span>
                    </div>
                </a>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden md:flex items-center gap-6 text-sm font-semibold text-slate-600">
                    <a href="{{ route('concerts.index') }}" class="hover:text-indigo-600 transition {{ request()->routeIs('concerts.*') ? 'text-indigo-600' : '' }}">
                        Jelajah Konser
                    </a>
                    <a href="{{ route('bookings.check') }}" class="hover:text-indigo-600 transition flex items-center gap-1.5 {{ request()->routeIs('bookings.check') ? 'text-indigo-600' : '' }}">
                        <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Cek E-Ticket</span>
                    </a>
                </nav>
            </div>

            <!-- Right Actions (Auth / Search Quick Jump) -->
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('bookings.check') }}"
                    class="md:hidden inline-flex items-center justify-center rounded-xl p-2 text-slate-600 hover:bg-slate-100 transition"
                    aria-label="Cek E-Ticket"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </a>

                @auth
                    <div class="flex items-center gap-3">
                        <div class="hidden sm:flex flex-col text-right">
                            <span class="text-xs font-bold text-slate-900">{{ auth()->user()->name }}</span>
                            <span class="text-[11px] text-slate-600">{{ auth()->user()->email }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') ?? '#' }}" class="inline">
                            @csrf
                            <button type="submit" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                                Keluar
                            </button>
                        </form>
                    </div>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Gate Online
                    </span>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="mx-auto flex-1 w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <!-- Global Alert Banners (Flash Messages) -->
        @if (session('success'))
            <x-alert-banner type="success" :message="session('success')" />
        @endif

        @if (session('error'))
            <x-alert-banner type="error" :message="session('error')" />
        @endif

        @if (session('warning'))
            <x-alert-banner type="warning" :message="session('warning')" />
        @endif

        @if ($errors->any())
            <x-alert-banner type="error">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert-banner>
        @endif

        @yield('content')
    </main>

    <!-- Footer (Hidden on Print Mode) -->
    <footer class="print:hidden border-t border-slate-200 bg-white text-slate-600 mt-auto">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Col 1: Brand Info -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white font-bold text-sm">SP</span>
                        <span class="text-lg font-extrabold text-slate-900 tracking-tight">Sound<span class="text-indigo-600">Pass</span></span>
                    </div>
                    <p class="text-xs leading-relaxed text-slate-600">
                        Platform resmi pemesanan tiket konser musik & festival berskala nasional dan internasional. Sistem tiket terintegrasi anti-calo dengan validasi NIK dan e-ticket ber-QR Code.
                    </p>
                    <p class="text-xs text-slate-600">
                        &copy; {{ date('Y') }} SoundPass Indonesia. Hak Cipta Dilindungi.
                    </p>
                </div>

                <!-- Col 2: Panduan Penukaran Wristband -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">Panduan Penukaran Wristband</h4>
                    <ul class="space-y-2 text-xs text-slate-600">
                        <li class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Tunjukkan e-ticket (berisi QR Code & Kode Booking unik) baik dalam bentuk digital maupun cetak.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Wajib membawa KTP/Paspor fisik asli dengan NIK yang sama persis seperti yang didaftarkan.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="h-4 w-4 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Loket dibuka mulai H-1 hingga hari H konser pukul 11:00 WIB di venue resmi.</span>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Customer Care & Keamanan -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">Bantuan & Layanan Konsumen</h4>
                    <p class="text-xs text-slate-600">
                        Mengalami kendala pemesanan atau pertanyaan seputar tiket konser? Hubungi Customer Support kami:
                    </p>
                    <div class="space-y-1.5 text-xs font-medium text-slate-700">
                        <p class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>bantuan@soundpass.id</span>
                        </p>
                        <p class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>WhatsApp: +62 812-3456-7890 (09:00 - 21:00 WIB)</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal Cek E-Ticket -->
    <div
        x-show="checkTicketModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        @keydown.escape.window="checkTicketModal = false"
    >
        <div
            @click.away="checkTicketModal = false"
            x-show="checkTicketModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-slate-100"
        >
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 font-bold">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </span>
                    <h3 class="text-base font-bold text-slate-900">Periksa Status E-Ticket</h3>
                </div>
                <button type="button" @click="checkTicketModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="mt-3 text-xs text-slate-600 leading-relaxed">
                Masukkan kode booking 10 karakter yang tertera pada konfirmasi pesanan Anda (contoh: <strong class="text-slate-800">SND-DEMO01</strong>) untuk melihat e-ticket.
            </p>

            <form
                @submit.prevent="if (bookingCodeInput.trim()) { window.location.href = '/bookings/' + encodeURIComponent(bookingCodeInput.trim().toUpperCase()); }"
                class="mt-4 space-y-4"
            >
                <div>
                    <label for="modal_booking_code" class="block text-xs font-semibold text-slate-700">Kode Booking</label>
                    <input
                        type="text"
                        id="modal_booking_code"
                        x-model="bookingCodeInput"
                        placeholder="Contoh: SND-DEMO01"
                        maxlength="15"
                        required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm uppercase tracking-wider font-mono text-slate-900 placeholder:text-slate-400 focus:border-indigo-600 focus:outline-none focus:ring-1 focus:ring-indigo-600"
                    >
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button
                        type="button"
                        @click="checkTicketModal = false"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition"
                    >
                        Buka E-Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
