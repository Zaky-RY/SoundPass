<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Concert;
use App\Models\TicketTier;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class DomainModelTest extends TestCase
{
    public function test_booking_status_enum_methods(): void
    {
        $this->assertSame('Menunggu Pembayaran', BookingStatus::PENDING->label());
        $this->assertSame('Terkonfirmasi (E-Ticket Aktif)', BookingStatus::CONFIRMED->label());
        $this->assertSame('Dibatalkan', BookingStatus::CANCELLED->label());

        $this->assertStringContainsString('amber', BookingStatus::PENDING->badgeClasses());
        $this->assertStringContainsString('emerald', BookingStatus::CONFIRMED->badgeClasses());
        $this->assertStringContainsString('rose', BookingStatus::CANCELLED->badgeClasses());
    }

    public function test_concert_ticket_status_accessors(): void
    {
        // 1. Tersedia
        $raisa = Concert::where('slug', 'raisa-live-in-concert-heart-to-heart')->firstOrFail();
        $this->assertSame('Tersedia', $raisa->ticket_status);
        $this->assertStringContainsString('emerald', $raisa->ticket_status_badge_classes);
        $this->assertGreaterThan(0, $raisa->starting_price);
        $this->assertCount(4, $raisa->ticketTiers);

        // 2. Segera Habis
        $sheila = Concert::where('slug', 'sheila-on-7-tunggu-aku-di-bandung')->firstOrFail();
        $this->assertSame('Segera Habis', $sheila->ticket_status);
        $this->assertStringContainsString('amber', $sheila->ticket_status_badge_classes);

        // 3. Sold Out
        $aespa = Concert::where('slug', 'aespa-live-tour-2026-synk-hyper-line-jakarta')->firstOrFail();
        $this->assertSame('Sold Out', $aespa->ticket_status);
        $this->assertStringContainsString('rose', $aespa->ticket_status_badge_classes);
    }

    public function test_booking_relationships_and_masked_nik(): void
    {
        $booking = Booking::where('booking_code', 'SND-DEMO01')->firstOrFail();

        $this->assertInstanceOf(BookingStatus::class, $booking->status);
        $this->assertSame(BookingStatus::CONFIRMED, $booking->status);
        $this->assertNotNull($booking->concert);
        $this->assertNotNull($booking->user);
        $this->assertCount(1, $booking->details);
        $this->assertSame(2, $booking->total_tickets);

        // NIK: 3273015508990004 -> 327301******0004
        $this->assertSame('327301******0004', $booking->masked_identity_number);
    }

    public function test_check_constraint_rejects_negative_remaining_quota(): void
    {
        $this->expectException(QueryException::class);

        $concert = Concert::firstOrFail();
        TicketTier::create([
            'concert_id' => $concert->id,
            'name' => 'Illegal Tier',
            'price' => 100000,
            'total_quota' => 10,
            'remaining_quota' => -1, // Harus ditolak oleh constraint/trigger
        ]);
    }
}
