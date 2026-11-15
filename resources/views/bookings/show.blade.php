@extends('layouts.app')

@section('title', 'E-Ticket Resmi: ' . $booking->booking_code . ' — SoundPass')

@section('content')
<div class="space-y-8 max-w-4xl mx-auto">

    <!-- Breadcrumb (Hidden on Print) -->
    <div class="print:hidden">
        <x-breadcrumb :items="[
            ['label' => $booking->concert->title, 'url' => route('concerts.show', $booking->concert)],
            ['label' => 'E-Ticket ' . $booking->booking_code, 'url' => null]
        ]" />
    </div>

    <!-- Print Header Actions (Hidden on Print) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden rounded-2xl bg-white p-5 border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </span>
            <div>
                <h1 class="text-base font-extrabold text-slate-900">E-Ticket Konser Resmi Aktif</h1>
                <p class="text-xs text-slate-600">Tunjukkan QR Code ini pada petugas gate di venue untuk penukaran gelang wristband.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('concerts.index') }}"
                class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition"
            >
                Jelajah Konser Lain
            </a>
            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-md hover:bg-indigo-700 transition"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak / Unduh PDF</span>
            </button>
        </div>
    </div>

    <!-- Ticket Pass Card (Ready for Print & Screen) -->
    <div class="relative overflow-hidden rounded-3xl bg-white border border-slate-200 shadow-xl print:shadow-none print:border-slate-800">

        <!-- Top Header Strip -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 sm:p-8 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-600 text-xs font-extrabold">SP</span>
                    <span class="text-sm font-extrabold uppercase tracking-widest text-indigo-300">SoundPass Official E-Pass</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white pt-1">
                    {{ $booking->concert->title }}
                </h2>
                <p class="text-xs text-slate-300">Headliner: <strong class="text-white">{{ $booking->concert->headliner }}</strong> &bull; Genre: {{ $booking->concert->genre }}</p>
            </div>

            <div class="text-left sm:text-right flex-shrink-0">
                <span class="inline-block rounded-lg px-3 py-1 text-xs font-bold border {{ $booking->status->badgeClasses() }}">
                    {{ $booking->status->label() }}
                </span>
                <p class="text-xs font-mono tracking-widest text-indigo-300 mt-2">KODE BOOKING</p>
                <p class="text-xl font-black font-mono tracking-widest text-white">{{ $booking->booking_code }}</p>
            </div>
        </div>

        <!-- Ticket Body -->
        <div class="p-6 sm:p-8 space-y-8">

            <!-- QR Code & Event Key Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center border-b border-slate-100 pb-8">
                <!-- QR Code Box -->
                <div class="md:col-span-5 flex flex-col items-center justify-center p-6 rounded-2xl bg-slate-50 border border-slate-200 text-center">
                    <div class="bg-white p-3 rounded-xl shadow-inner border border-slate-100">
                        {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(170)->generate($booking->booking_code) !!}
                    </div>
                    <p class="mt-3 font-mono font-extrabold text-sm tracking-widest text-slate-900">{{ $booking->booking_code }}</p>
                    <p class="text-[11px] text-slate-600 mt-0.5">Scan QR ini saat penukaran wristband di gate venue</p>
                </div>

                <!-- Event Details -->
                <div class="md:col-span-7 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Tanggal Acara</span>
                            <p class="text-sm font-extrabold text-slate-900">{{ $booking->concert->event_date->translatedFormat('d F Y') }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Open Gate</span>
                            <p class="text-sm font-extrabold text-slate-900">{{ $booking->concert->open_gate_time }}</p>
                        </div>
                    </div>

                    <div class="space-y-1 pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Lokasi Venue</span>
                        <p class="text-sm font-extrabold text-slate-900">{{ $booking->concert->venue_name }}</p>
                        <p class="text-xs text-slate-600">{{ $booking->concert->city }}, Indonesia</p>
                    </div>

                    <div class="space-y-1 pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Identitas Pemegang Tiket</span>
                        <p class="text-sm font-bold text-slate-900">{{ $booking->customer_name }}</p>
                        <p class="text-xs font-mono text-slate-600">NIK: {{ $booking->masked_identity_number }}</p>
                        <p class="text-xs text-slate-600">Email: {{ $booking->customer_email }} &bull; HP: {{ $booking->customer_phone }}</p>
                    </div>
                </div>
            </div>

            <!-- Ticket Items Table -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600">Rincian Kategori Tiket</h3>
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="p-3.5">Kategori Tiket</th>
                                <th class="p-3.5 text-center">Jumlah</th>
                                <th class="p-3.5 text-right">Harga Satuan</th>
                                <th class="p-3.5 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($booking->details as $detail)
                                <tr>
                                    <td class="p-3.5 font-bold text-slate-900">
                                        {{ $detail->ticketTier->name }}
                                        <p class="text-[11px] font-normal text-slate-600">{{ $detail->ticketTier->description }}</p>
                                    </td>
                                    <td class="p-3.5 text-center font-bold text-slate-900">{{ $detail->quantity }} Lembar</td>
                                    <td class="p-3.5 text-right font-medium text-slate-700">@rupiah($detail->unit_price)</td>
                                    <td class="p-3.5 text-right font-extrabold text-slate-900">@rupiah($detail->subtotal)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Breakdown -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6 pt-4 border-t border-slate-100">
                <div class="space-y-1 text-xs text-slate-600 max-w-sm">
                    <p class="font-bold text-slate-800">Catatan Penting:</p>
                    <p>Bawa KTP fisik asli dengan NIK {{ $booking->masked_identity_number }} saat penukaran gelang konser. E-Ticket ini sah dan diterbitkan resmi oleh SoundPass.</p>
                </div>

                <div class="w-full sm:w-64 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal Tiket:</span>
                        <span class="font-bold text-slate-900">@rupiah($booking->subtotal_amount)</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Pajak Daerah (10%):</span>
                        <span class="font-bold text-slate-900">@rupiah($booking->tax_amount)</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Platform:</span>
                        <span class="font-bold text-slate-900">@rupiah($booking->admin_fee)</span>
                    </div>
                    <div class="border-t border-slate-200 pt-2 flex justify-between text-sm font-extrabold text-slate-900">
                        <span>Total Bayar:</span>
                        <span class="text-indigo-600 font-black">@rupiah($booking->total_amount)</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

@push('styles')
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            background-color: #ffffff !important;
            color: #0f172a !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        header, footer, nav, .print\:hidden {
            display: none !important;
        }
        main {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        .shadow-xl, .shadow-md, .shadow-sm {
            box-shadow: none !important;
        }
    }
</style>
@endpush
@endsection
