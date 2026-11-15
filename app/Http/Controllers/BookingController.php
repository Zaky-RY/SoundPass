<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateBookingAction;
use App\Exceptions\QuotaExceededException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Concert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class BookingController extends Controller
{
    /**
     * Halaman 3: Formulir Pemesanan & Pemilihan Kategori Tiket
     */
    public function create(Concert $concert): View|RedirectResponse
    {
        $concert->load('ticketTiers');

        if ($concert->event_date->isPast() || $concert->ticketTiers->sum('remaining_quota') <= 0) {
            return redirect()
                ->route('concerts.show', $concert)
                ->with('error', 'Tiket untuk konser ini sudah habis atau penjualan telah ditutup.');
        }

        return view('bookings.create', [
            'concert' => $concert,
        ]);
    }

    /**
     * Engine Pemrosesan Transaksi Tiket Atomik
     */
    public function store(StoreBookingRequest $request, CreateBookingAction $createBooking): RedirectResponse
    {
        try {
            $booking = $createBooking->execute([
                ...$request->validated(),
                'user_id' => Auth::id(),
            ]);

            $authorized = $request->session()->get('authorized_bookings', []);
            $request->session()->put('authorized_bookings', [...$authorized, $booking->booking_code]);

            return redirect()
                ->route('bookings.show', $booking)
                ->with('success', 'E-Ticket konser berhasil diterbitkan. Tunjukkan kode booking saat penukaran wristband.');
        } catch (QuotaExceededException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    /**
     * Halaman Fitur Tambahan: Formulir Pemulihan Sesi E-Ticket
     */
    public function checkForm(): View
    {
        return view('bookings.check');
    }

    /**
     * Verifikasi Kode Booking dan Identitas Pemesan (NIK / Email) untuk Memulihkan Sesi Akses
     */
    public function verifyCheck(Request $request): RedirectResponse
    {
        $request->validate([
            'booking_code' => ['required', 'string', 'max:20'],
            'identity' => ['required', 'string', 'max:150'],
        ], [
            'booking_code.required' => 'Kode booking wajib diisi.',
            'identity.required' => 'NIK (16 digit) atau email pemesan wajib diisi.',
        ]);

        $bookingCode = strtoupper(trim((string) $request->input('booking_code')));
        $identity = trim((string) $request->input('identity'));

        $booking = Booking::where('booking_code', $bookingCode)
            ->where(function ($query) use ($identity): void {
                $query->where('identity_number', $identity)
                    ->orWhere('customer_email', strtolower($identity));
            })
            ->first();

        if (! $booking) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Kombinasi kode booking dan NIK/Email tidak ditemukan. Pastikan data yang dimasukkan sesuai.');
        }

        $authorized = $request->session()->get('authorized_bookings', []);
        if (! in_array($booking->booking_code, $authorized, true)) {
            $request->session()->put('authorized_bookings', [...$authorized, $booking->booking_code]);
        }

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Sesi e-ticket berhasil dipulihkan. Selamat menikmati konser!');
    }

    /**
     * Halaman 4: Ringkasan & E-Ticket (Protected by Session Guard)
     */
    public function show(Request $request, Booking $booking): View
    {
        $authorizedCodes = $request->session()->get('authorized_bookings', []);
        $isOwner = Auth::check() && $booking->user_id === Auth::id();
        $isSessionAuthorized = in_array($booking->booking_code, $authorizedCodes, true);

        abort_unless($isOwner || $isSessionAuthorized, 403, 'Anda tidak memiliki akses ke e-ticket ini.');

        $booking->load(['concert', 'details.ticketTier']);

        return view('bookings.show', [
            'booking' => $booking,
        ]);
    }
}
