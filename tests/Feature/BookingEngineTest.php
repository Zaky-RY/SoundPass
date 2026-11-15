<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\CreateBookingAction;
use App\Enums\BookingStatus;
use App\Exceptions\QuotaExceededException;
use App\Models\Booking;
use App\Models\Concert;
use App\Models\TicketTier;
use Tests\TestCase;

class BookingEngineTest extends TestCase
{
    public function test_booking_create_page_loads_for_available_concert(): void
    {
        $concert = Concert::where('slug', 'raisa-live-in-concert-heart-to-heart')->firstOrFail();

        $response = $this->get('/concerts/' . $concert->slug . '/book');

        $response->assertStatus(200);
        $response->assertSee('Pilih Kategori Tiket');
        $response->assertSee('Data Identitas Pemesan');
        $response->assertSee('Ringkasan Pemesanan');
    }

    public function test_booking_create_guards_against_sold_out_concert(): void
    {
        $soldOutConcert = Concert::where('slug', 'aespa-live-tour-2026-synk-hyper-line-jakarta')->firstOrFail();

        $response = $this->get('/concerts/' . $soldOutConcert->slug . '/book');

        $response->assertRedirect(route('concerts.show', $soldOutConcert));
        $response->assertSessionHas('error', 'Tiket untuk konser ini sudah habis atau penjualan telah ditutup.');
    }

    public function test_booking_form_request_validates_nik_and_max_tickets(): void
    {
        $concert = Concert::where('slug', 'raisa-live-in-concert-heart-to-heart')->firstOrFail();
        $tier = $concert->ticketTiers()->firstOrFail();

        // 1. Validasi NIK kurang dari 16 digit
        $responseInvalidNik = $this->post('/bookings', [
            'concert_id' => $concert->id,
            'items' => [
                ['ticket_tier_id' => $tier->id, 'quantity' => 2],
            ],
            'customer_name' => 'Budi Santoso',
            'identity_number' => '12345', // < 16 digit
            'customer_email' => 'budi@example.com',
            'customer_phone' => '081234567890',
        ]);
        $responseInvalidNik->assertSessionHasErrors(['identity_number']);

        // 2. Validasi jumlah tiket > 4
        $responseExceedsMax = $this->post('/bookings', [
            'concert_id' => $concert->id,
            'items' => [
                ['ticket_tier_id' => $tier->id, 'quantity' => 5], // > 4
            ],
            'customer_name' => 'Budi Santoso',
            'identity_number' => '3273012345670001',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '081234567890',
        ]);
        $responseExceedsMax->assertSessionHasErrors(['items.0.quantity']);
    }

    public function test_atomic_booking_process_decrements_quota_and_redirects(): void
    {
        $concert = Concert::where('slug', 'raisa-live-in-concert-heart-to-heart')->firstOrFail();
        $tier = $concert->ticketTiers()->where('remaining_quota', '>=', 3)->firstOrFail();
        $initialQuota = $tier->remaining_quota;

        $response = $this->post('/bookings', [
            'concert_id' => $concert->id,
            'items' => [
                ['ticket_tier_id' => $tier->id, 'quantity' => 2],
            ],
            'customer_name' => 'Citra Lestari',
            'identity_number' => '3273018809980002',
            'customer_email' => 'citra@example.com',
            'customer_phone' => '081298765432',
        ]);

        $tier->refresh();
        $this->assertSame($initialQuota - 2, $tier->remaining_quota);

        /** @var Booking $booking */
        $booking = Booking::where('identity_number', '3273018809980002')->latest('id')->firstOrFail();
        $this->assertStringStartsWith('SND-', $booking->booking_code);
        $this->assertSame(BookingStatus::CONFIRMED, $booking->status);

        // Subtotal, Tax 10%, Admin Fee Rp 10.000 * 2 = 20.000
        $expectedSubtotal = $tier->price * 2;
        $expectedTax = (int) round($expectedSubtotal * 0.10);
        $expectedAdminFee = 20000;
        $this->assertSame($expectedSubtotal, $booking->subtotal_amount);
        $this->assertSame($expectedTax, $booking->tax_amount);
        $this->assertSame($expectedAdminFee, $booking->admin_fee);
        $this->assertSame($expectedSubtotal + $expectedTax + $expectedAdminFee, $booking->total_amount);

        // Session authorization & redirect
        $response->assertRedirect(route('bookings.show', $booking));
        $response->assertSessionHas('authorized_bookings', [$booking->booking_code]);
    }

    public function test_overbooking_protection_throws_and_preserves_quota(): void
    {
        $concert = Concert::where('slug', 'sheila-on-7-tunggu-aku-di-bandung')->firstOrFail();
        $tier = $concert->ticketTiers()->firstOrFail();

        // Paksa kuota tersisa = 1
        $tier->update(['remaining_quota' => 1]);

        $response = $this->post('/bookings', [
            'concert_id' => $concert->id,
            'items' => [
                ['ticket_tier_id' => $tier->id, 'quantity' => 2], // Minta 2 padahal sisa 1
            ],
            'customer_name' => 'Doni Kusuma',
            'identity_number' => '3273011102900003',
            'customer_email' => 'doni@example.com',
            'customer_phone' => '081233445566',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Kuota tidak boleh berkurang
        $tier->refresh();
        $this->assertSame(1, $tier->remaining_quota);
    }

    public function test_e_ticket_page_is_protected_by_session_guard(): void
    {
        $booking = Booking::where('booking_code', 'SND-DEMO01')->firstOrFail();

        // 1. Akses tanpa otorisasi di session -> 403 Forbidden
        $unauthorizedResponse = $this->get('/bookings/' . $booking->booking_code);
        $unauthorizedResponse->assertStatus(403);

        // 2. Akses dengan session yang valid -> 200 OK
        $authorizedResponse = $this->withSession([
            'authorized_bookings' => [$booking->booking_code],
        ])->get('/bookings/' . $booking->booking_code);

        $authorizedResponse->assertStatus(200);
        $authorizedResponse->assertSee($booking->booking_code);
        $authorizedResponse->assertSee($booking->customer_name);
    }

    public function test_check_booking_form_loads_successfully(): void
    {
        $response = $this->get('/check-booking');

        $response->assertStatus(200);
        $response->assertSee('Cek & Buka E-Ticket', false);
        $response->assertSee('Kode Booking');
    }

    public function test_check_booking_verification_restores_session_with_valid_nik(): void
    {
        $booking = Booking::where('booking_code', 'SND-DEMO01')->firstOrFail();

        $response = $this->post('/check-booking', [
            'booking_code' => $booking->booking_code,
            'identity' => $booking->identity_number,
        ]);

        $response->assertRedirect(route('bookings.show', $booking));
        $response->assertSessionHas('authorized_bookings', [$booking->booking_code]);
    }

    public function test_check_booking_verification_restores_session_with_valid_email(): void
    {
        $booking = Booking::where('booking_code', 'SND-DEMO01')->firstOrFail();

        $response = $this->post('/check-booking', [
            'booking_code' => $booking->booking_code,
            'identity' => $booking->customer_email,
        ]);

        $response->assertRedirect(route('bookings.show', $booking));
        $response->assertSessionHas('authorized_bookings', [$booking->booking_code]);
    }

    public function test_check_booking_fails_with_invalid_credentials(): void
    {
        $response = $this->post('/check-booking', [
            'booking_code' => 'SND-INVALID',
            'identity' => '3273000000000000',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
