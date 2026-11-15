@extends('layouts.app')

@section('title', 'Pilih Tiket & Pemesanan: ' . $concert->title . ' — SoundPass')

@section('content')
@php
    $tiersData = $concert->ticketTiers->map(fn ($t) => [
        'id' => $t->id,
        'name' => $t->name,
        'description' => $t->description,
        'price' => (int) $t->price,
        'remaining_quota' => (int) $t->remaining_quota,
        'total_quota' => (int) $t->total_quota,
    ])->values()->all();

    $oldQuantities = [];
    if (old('items')) {
        foreach (old('items') as $oldItem) {
            if (isset($oldItem['ticket_tier_id']) && isset($oldItem['quantity'])) {
                $oldQuantities[(int)$oldItem['ticket_tier_id']] = (int)$oldItem['quantity'];
            }
        }
    }
@endphp

<div
    x-data="bookingEngine()"
    class="space-y-8 pb-24 lg:pb-0"
>
    <!-- Breadcrumbs -->
    <x-breadcrumb :items="[
        ['label' => $concert->title, 'url' => route('concerts.show', $concert)],
        ['label' => 'Pilih Tiket & Formulir', 'url' => null]
    ]" />

    <!-- Form Section -->
    <form method="POST" action="{{ route('bookings.store') }}" id="bookingForm" class="space-y-8">
        @csrf
        <input type="hidden" name="concert_id" value="{{ $concert->id }}">

        <!-- Dynamic Hidden Inputs for Selected Ticket Tiers -->
        <template x-for="(item, idx) in selectedTiers" :key="item.tier.id">
            <div>
                <input type="hidden" :name="'items[' + idx + '][ticket_tier_id]'" :value="item.tier.id">
                <input type="hidden" :name="'items[' + idx + '][quantity]'" :value="item.qty">
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left Col (lg:col-span-7): Tier Selection & Identity Form -->
            <div class="lg:col-span-7 space-y-8">

                <!-- 1. Ticket Tier Stepper Selection -->
                <div class="rounded-3xl bg-white p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">1. Pilih Kategori Tiket</h2>
                            <p class="text-xs text-slate-600 mt-0.5">
                                Maksimal pembelian 4 tiket per transaksi / NIK pemesan.
                            </p>
                        </div>

                        <!-- Ticket Counter Badge -->
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold transition"
                                :class="totalQuantity >= 4 ? 'bg-amber-100 text-amber-800 border border-amber-300' : (totalQuantity > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-600')"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                <span>Dipilih: <strong x-text="totalQuantity">0</strong> / 4 Tiket</span>
                            </span>
                        </div>
                    </div>

                    <!-- Tiers List -->
                    <fieldset class="space-y-4">
                        <legend class="sr-only">Pilihan Kategori Tiket Konser</legend>

                        <template x-for="tier in tiers" :key="tier.id">
                            <div
                                class="rounded-2xl border p-4 sm:p-5 transition duration-200"
                                :class="{
                                    'bg-indigo-50/50 border-indigo-600 ring-1 ring-indigo-600 shadow-sm': quantities[tier.id] > 0,
                                    'bg-white border-slate-200 hover:border-slate-300 shadow-sm': quantities[tier.id] === 0 && tier.remaining_quota > 0,
                                    'bg-slate-100 border-slate-200 opacity-60 cursor-not-allowed': tier.remaining_quota <= 0
                                }"
                            >
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <!-- Tier Info -->
                                    <div class="space-y-1 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-base font-bold text-slate-900" x-text="tier.name"></h3>
                                            <template x-if="tier.remaining_quota <= 0">
                                                <span class="rounded-md bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-800 border border-rose-200">
                                                    SOLD OUT
                                                </span>
                                            </template>
                                            <template x-if="tier.remaining_quota > 0 && tier.remaining_quota <= 20">
                                                <span class="rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 border border-amber-200" x-text="'Tersisa ' + tier.remaining_quota + ' tiket'">
                                                </span>
                                            </template>
                                        </div>

                                        <p class="text-xs text-slate-600 leading-relaxed" x-text="tier.description || 'Zona menonton resmi ' + tier.name"></p>

                                        <p class="text-xs font-medium text-slate-600 pt-0.5">
                                            Harga: <strong class="text-slate-900 font-extrabold text-sm" x-text="formatRupiah(tier.price)"></strong> / tiket
                                        </p>
                                    </div>

                                    <!-- Stepper Controls (Min 40x40px for Mobile Touch Friendliness) -->
                                    <div class="flex items-center justify-end sm:justify-center">
                                        <template x-if="tier.remaining_quota > 0">
                                            <div class="inline-flex items-center rounded-xl bg-slate-50 border border-slate-200 p-1 shadow-inner">
                                                <!-- Button Decrement (-) -->
                                                <button
                                                    type="button"
                                                    @click="decrement(tier)"
                                                    :disabled="!canDecrement(tier)"
                                                    class="h-10 w-10 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-700 shadow-xs hover:bg-slate-100 active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed disabled:active:scale-100 transition"
                                                    :aria-label="'Kurangi jumlah tiket ' + tier.name"
                                                >
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
                                                    </svg>
                                                </button>

                                                <!-- Quantity Display -->
                                                <span
                                                    class="w-10 text-center font-extrabold text-base text-slate-900 select-none"
                                                    x-text="quantities[tier.id] || 0"
                                                    aria-live="polite"
                                                >
                                                    0
                                                </span>

                                                <!-- Button Increment (+) -->
                                                <button
                                                    type="button"
                                                    @click="increment(tier)"
                                                    :disabled="!canIncrement(tier)"
                                                    class="h-10 w-10 flex items-center justify-center rounded-lg bg-indigo-600 text-white shadow-xs hover:bg-indigo-700 active:scale-95 disabled:bg-slate-300 disabled:opacity-40 disabled:cursor-not-allowed disabled:active:scale-100 transition"
                                                    :aria-label="'Tambah jumlah tiket ' + tier.name"
                                                >
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>

                                        <template x-if="tier.remaining_quota <= 0">
                                            <span class="inline-flex items-center rounded-xl bg-slate-200/80 px-4 py-2.5 text-xs font-bold text-slate-600" aria-disabled="true">
                                                Tiket Habis
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </fieldset>
                </div>

                <!-- 2. Customer Identity Form -->
                <div class="rounded-3xl bg-white p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">2. Data Identitas Pemesan</h2>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Data wajib sesuai KTP/Paspor fisik untuk proses verifikasi e-ticket dan penukaran wristband di venue.
                        </p>
                    </div>

                    <!-- Identity Notice Alert -->
                    <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-xs text-amber-900 flex items-start gap-3">
                        <svg class="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="space-y-0.5 leading-relaxed">
                            <p class="font-bold">Penting: 1 NIK Berlaku Maksimal 4 Tiket</p>
                            <p class="text-amber-800">
                                Pastikan NIK yang Anda masukkan valid (tepat 16 digit). Pemegang KTP fisik wajib hadir saat penukaran wristband.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Customer Name -->
                        <div class="sm:col-span-2">
                            <label for="customer_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Nama Lengkap (Sesuai KTP / Paspor) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="customer_name"
                                id="customer_name"
                                x-model="customer_name"
                                required
                                minlength="3"
                                maxlength="100"
                                placeholder="Contoh: Budi Santoso"
                                class="mt-1.5 block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-none focus:ring-1 focus:ring-indigo-600"
                            >
                            @error('customer_name')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Identity Number (NIK) -->
                        <div class="sm:col-span-2">
                            <label for="identity_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Nomor Induk Kependudukan (NIK 16 Digit) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="identity_number"
                                id="identity_number"
                                x-model="identity_number"
                                required
                                minlength="16"
                                maxlength="16"
                                pattern="[0-9]{16}"
                                inputmode="numeric"
                                placeholder="Contoh: 3273012345670001"
                                class="mt-1.5 block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-mono tracking-wider text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-none focus:ring-1 focus:ring-indigo-600"
                            >
                            <div class="flex items-center justify-between mt-1 text-[11px] text-slate-600">
                                <span>Wajib tepat 16 digit angka KTP.</span>
                                <span :class="identity_number.length === 16 ? 'text-emerald-600 font-bold' : 'text-slate-600'" x-text="identity_number.length + ' / 16 digit'"></span>
                            </div>
                            @error('identity_number')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Customer Email -->
                        <div>
                            <label for="customer_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Alamat Email Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="email"
                                name="customer_email"
                                id="customer_email"
                                x-model="customer_email"
                                required
                                maxlength="150"
                                placeholder="nama@email.com"
                                class="mt-1.5 block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-none focus:ring-1 focus:ring-indigo-600"
                            >
                            <p class="mt-1 text-[11px] text-slate-600">Salinan e-ticket akan dikirimkan ke email ini.</p>
                            @error('customer_email')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Customer Phone -->
                        <div>
                            <label for="customer_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Nomor WhatsApp / Seluler <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="tel"
                                name="customer_phone"
                                id="customer_phone"
                                x-model="customer_phone"
                                required
                                placeholder="Contoh: 081234567890"
                                class="mt-1.5 block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-600 focus:outline-none focus:ring-1 focus:ring-indigo-600"
                            >
                            <p class="mt-1 text-[11px] text-slate-600">Format nomor seluler Indonesia (+62/62/08).</p>
                            @error('customer_phone')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Col (lg:col-span-5): Sticky Order Summary & Checkout Card -->
            <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-24">

                <!-- Summary Card -->
                <div class="rounded-3xl bg-white p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight border-b border-slate-100 pb-3">
                        Ringkasan Pemesanan
                    </h3>

                    <!-- Concert Mini Info -->
                    <div class="flex gap-4 items-center rounded-2xl bg-slate-50 p-3.5 border border-slate-100">
                        @if ($concert->poster_url)
                            <img src="{{ $concert->poster_url }}" alt="{{ $concert->title }}" class="h-16 w-16 rounded-xl object-cover flex-shrink-0">
                        @endif
                        <div class="min-w-0 flex-1">
                            <span class="inline-block rounded-md bg-indigo-100 text-indigo-700 px-2 py-0.5 text-[10px] font-bold">
                                {{ $concert->genre }}
                            </span>
                            <h4 class="text-xs font-bold text-slate-900 truncate mt-1">{{ $concert->title }}</h4>
                            <p class="text-[11px] text-slate-600 truncate">{{ $concert->event_date->translatedFormat('d F Y') }} &bull; {{ $concert->venue_name }}</p>
                        </div>
                    </div>

                    <!-- Selected Tickets Breakdown -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700 uppercase tracking-wider">
                            <span>Tiket Dipilih</span>
                            <span x-text="totalQuantity + ' Tiket'">0 Tiket</span>
                        </div>

                        <template x-if="selectedTiers.length === 0">
                            <div class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-xs text-slate-600">
                                Belum ada kategori tiket yang dipilih. Silakan tentukan jumlah pada daftar di samping.
                            </div>
                        </template>

                        <div class="space-y-2">
                            <template x-for="item in selectedTiers" :key="item.tier.id">
                                <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-100">
                                    <div>
                                        <p class="font-bold text-slate-900" x-text="item.tier.name"></p>
                                        <p class="text-[11px] text-slate-600" x-text="item.qty + ' × ' + formatRupiah(item.tier.price)"></p>
                                    </div>
                                    <span class="font-extrabold text-slate-900" x-text="formatRupiah(item.subtotal)"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Cost Breakdown Details -->
                    <div class="space-y-2 border-t border-slate-100 pt-4 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Subtotal Tiket</span>
                            <span class="font-bold text-slate-900" x-text="formatRupiah(subtotal)">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="flex items-center gap-1">
                                Pajak Hiburan Daerah (10%)
                                <span class="cursor-help text-slate-400" title="Pajak daerah 10% sesuai regulasi industri hiburan">ⓘ</span>
                            </span>
                            <span class="font-bold text-slate-900" x-text="formatRupiah(taxAmount)">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="flex items-center gap-1">
                                Biaya Layanan Platform
                                <span class="cursor-help text-slate-400" title="Rp 10.000 per lembar tiket">ⓘ</span>
                            </span>
                            <span class="font-bold text-slate-900" x-text="formatRupiah(adminFee)">Rp 0</span>
                        </div>

                        <div class="border-t border-slate-200 pt-3 flex items-baseline justify-between">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Total Pembayaran</span>
                                <p class="text-[11px] text-slate-600">Sudah termasuk pajak & biaya layanan</p>
                            </div>
                            <span class="text-xl font-black text-indigo-600" x-text="formatRupiah(totalAmount)">
                                Rp 0
                            </span>
                        </div>
                    </div>

                    <!-- Desktop Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="!isFormValid || totalQuantity === 0"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 py-4 px-6 text-sm font-bold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 active:scale-95 disabled:bg-slate-300 disabled:shadow-none disabled:cursor-not-allowed disabled:active:scale-100 transition"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Konfirmasi & Terbitkan E-Ticket</span>
                        </button>

                        <p class="mt-3 text-center text-[11px] text-slate-600 flex items-center justify-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Transaksi Terenkripsi & Kuota Terkunci Atomik</span>
                        </p>
                    </div>
                </div>

            </div>

        </div>

        <!-- Mobile Sticky Bottom Bar (Thumb-friendly CTA per DESIGN.md) -->
        <div class="fixed bottom-0 inset-x-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur-md p-4 lg:hidden shadow-2xl">
            <div class="flex items-center justify-between gap-4 max-w-md mx-auto">
                <div>
                    <p class="text-[10px] text-slate-600 uppercase tracking-wider">
                        Total (<span x-text="totalQuantity">0</span> Tiket)
                    </p>
                    <p class="text-lg font-black text-indigo-600" x-text="formatRupiah(totalAmount)">
                        Rp 0
                    </p>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="!isFormValid || totalQuantity === 0"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-6 py-3.5 text-xs font-bold text-white shadow-md hover:bg-indigo-700 active:scale-95 disabled:bg-slate-300 disabled:cursor-not-allowed disabled:active:scale-100 transition"
                    >
                        Bayar & Terbitkan
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

@push('scripts')
<script>
    function bookingEngine() {
        return {
            tiers: @json($tiersData),
            quantities: {
                @foreach($concert->ticketTiers as $tier)
                    {{ $tier->id }}: {{ $oldQuantities[$tier->id] ?? 0 }},
                @endforeach
            },
            customer_name: '{{ addslashes((string) old('customer_name', auth()->user()?->name ?? '')) }}',
            identity_number: '{{ addslashes((string) old('identity_number', '')) }}',
            customer_email: '{{ addslashes((string) old('customer_email', auth()->user()?->email ?? '')) }}',
            customer_phone: '{{ addslashes((string) old('customer_phone', '')) }}',

            get totalQuantity() {
                return Object.values(this.quantities).reduce((acc, q) => acc + (parseInt(q) || 0), 0);
            },

            canIncrement(tier) {
                const currentQty = this.quantities[tier.id] || 0;
                return tier.remaining_quota > currentQty && this.totalQuantity < 4;
            },

            canDecrement(tier) {
                const currentQty = this.quantities[tier.id] || 0;
                return currentQty > 0;
            },

            increment(tier) {
                if (this.canIncrement(tier)) {
                    this.quantities[tier.id] = (this.quantities[tier.id] || 0) + 1;
                }
            },

            decrement(tier) {
                if (this.canDecrement(tier)) {
                    this.quantities[tier.id] = Math.max(0, (this.quantities[tier.id] || 0) - 1);
                }
            },

            get selectedTiers() {
                return this.tiers
                    .filter(t => (this.quantities[t.id] || 0) > 0)
                    .map(t => {
                        const qty = this.quantities[t.id];
                        return {
                            tier: t,
                            qty: qty,
                            subtotal: qty * t.price
                        };
                    });
            },

            get subtotal() {
                return this.selectedTiers.reduce((acc, item) => acc + item.subtotal, 0);
            },

            get taxAmount() {
                return Math.round(this.subtotal * 0.10);
            },

            get adminFee() {
                return 10000 * this.totalQuantity;
            },

            get totalAmount() {
                return this.subtotal + this.taxAmount + this.adminFee;
            },

            get isFormValid() {
                const total = this.totalQuantity;
                const nameValid = this.customer_name.trim().length >= 3;
                const nikValid = /^\d{16}$/.test(this.identity_number.trim());
                const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.customer_email.trim());
                const phoneValid = /^(\+62|62|0)8[1-9][0-9]{6,10}$/.test(this.customer_phone.trim());

                return total > 0 && total <= 4 && nameValid && nikValid && emailValid && phoneValid;
            },

            formatRupiah(num) {
                return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
            }
        };
    }
</script>
@endpush
@endsection
