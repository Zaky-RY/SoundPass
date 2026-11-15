@extends('layouts.app')

@section('title', 'Cek E-Ticket & Pulihkan Akses Sesi — SoundPass')

@section('content')
<div class="space-y-8 max-w-2xl mx-auto py-4">

    <!-- Breadcrumbs -->
    <x-breadcrumb :items="[
        ['label' => 'Cek E-Ticket', 'url' => null]
    ]" />

    <!-- Card Container -->
    <div class="overflow-hidden rounded-3xl bg-white border border-slate-200 shadow-sm">
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 sm:p-8 text-white">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600/90 text-white shadow-inner">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </span>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">Cek & Buka E-Ticket</h1>
                    <p class="text-xs text-slate-300 mt-0.5">Pulihkan sesi akses e-ticket konser Anda dengan verifikasi kode booking.</p>
                </div>
            </div>
        </div>

        <!-- Form Body -->
        <div class="p-6 sm:p-8 space-y-6">

            <!-- Security Notice -->
            <div class="rounded-2xl bg-indigo-50/70 border border-indigo-200 p-4 text-xs text-indigo-950 flex items-start gap-3">
                <svg class="h-5 w-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <div class="space-y-0.5 leading-relaxed">
                    <p class="font-bold text-indigo-900">Perlindungan Privasi E-Ticket</p>
                    <p class="text-indigo-800">
                        Untuk menjaga kerahasiaan tiket Anda dari pihak tidak berwenang, sistem memerlukan konfirmasi NIK atau alamat email yang digunakan saat pemesanan.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('bookings.verify') }}" class="space-y-6">
                @csrf

                <!-- Booking Code Input -->
                <div>
                    <label for="booking_code" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Kode Booking <span class="text-rose-500">*</span>
                    </label>
                    <div class="mt-1.5 relative">
                        <input
                            type="text"
                            name="booking_code"
                            id="booking_code"
                            value="{{ old('booking_code') }}"
                            required
                            maxlength="20"
                            placeholder="Contoh: SND-DEMO01"
                            class="block w-full uppercase font-mono tracking-wider rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-none focus:ring-1 focus:ring-indigo-600 @error('booking_code') border-rose-500 @enderror"
                        >
                    </div>
                    <p class="mt-1 text-[11px] text-slate-600">Kode alfanumerik yang tertera pada konfirmasi pemesanan Anda.</p>
                    @error('booking_code')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Identity / Email Input -->
                <div>
                    <label for="identity" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        NIK (16 Digit) atau Alamat Email Pemesan <span class="text-rose-500">*</span>
                    </label>
                    <div class="mt-1.5 relative">
                        <input
                            type="text"
                            name="identity"
                            id="identity"
                            value="{{ old('identity') }}"
                            required
                            maxlength="150"
                            placeholder="Contoh: 3273012345670001 atau nama@email.com"
                            class="block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-none focus:ring-1 focus:ring-indigo-600 @error('identity') border-rose-500 @enderror"
                        >
                    </div>
                    <p class="mt-1 text-[11px] text-slate-600">Masukkan salah satu: Nomor NIK KTP fisik atau email yang Anda daftarkan saat memesan.</p>
                    @error('identity')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 py-3.5 px-6 text-sm font-bold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 active:scale-95 transition"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Verifikasi & Buka E-Ticket</span>
                    </button>
                </div>
            </form>

            <!-- Demo Hint -->
            <div class="pt-4 border-t border-slate-100 text-center text-xs text-slate-600">
                <p>
                    Ingin mencoba akun demo? Gunakan kode booking <strong class="font-mono text-slate-800">SND-DEMO01</strong> dengan NIK <strong class="font-mono text-slate-800">3273015508990004</strong> atau email <strong class="text-slate-800">penonton@soundpass.id</strong>.
                </p>
            </div>

        </div>
    </div>

</div>
@endsection
