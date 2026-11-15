<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Exceptions\QuotaExceededException;
use App\Models\Booking;
use App\Models\Concert;
use App\Models\TicketTier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class CreateBookingAction
{
    private const float ENTERTAINMENT_TAX_RATE = 0.10;
    private const int ADMIN_FEE_PER_TICKET = 10000;

    /**
     * @param array{
     *     concert_id: int,
     *     items: array<int, array{ticket_tier_id: int, quantity: int}>,
     *     customer_name: string,
     *     identity_number: string,
     *     customer_email: string,
     *     customer_phone: string,
     *     user_id?: int|null
     * } $data
     * @throws QuotaExceededException
     */
    public function execute(array $data): Booking
    {
        return DB::transaction(function () use ($data): Booking {
            $concert = Concert::findOrFail($data['concert_id']);

            if ($concert->event_date->isPast()) {
                throw new QuotaExceededException('Penjualan tiket ditutup karena konser telah berlangsung.');
            }

            $requestedItems = collect($data['items'])->keyBy('ticket_tier_id');
            $tierIds = $requestedItems->keys()->all();

            // Kunci baris kategori tiket dengan Pessimistic Locking agar pengurangan
            // remaining_quota berjalan secara atomik tanpa race condition / overbooking.
            $tiers = TicketTier::whereIn('id', $tierIds)
                ->where('concert_id', $concert->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($tiers->count() !== count($tierIds)) {
                throw new QuotaExceededException('Sebagian kategori tiket tidak ditemukan.');
            }

            $subtotal = 0;
            $totalQuantity = 0;
            $detailsPayload = [];

            foreach ($tiers as $tier) {
                $qty = (int) $requestedItems[$tier->id]['quantity'];

                if ($tier->remaining_quota < $qty) {
                    throw new QuotaExceededException(
                        "Kuota tiket kategori {$tier->name} tidak mencukupi (tersisa {$tier->remaining_quota} tiket)."
                    );
                }

                $tier->decrement('remaining_quota', $qty);

                $lineSubtotal = $tier->price * $qty;
                $subtotal += $lineSubtotal;
                $totalQuantity += $qty;

                $detailsPayload[] = [
                    'ticket_tier_id' => $tier->id,
                    'quantity' => $qty,
                    'unit_price' => $tier->price,
                    'subtotal' => $lineSubtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $taxAmount = (int) round($subtotal * self::ENTERTAINMENT_TAX_RATE);
            $adminFee = self::ADMIN_FEE_PER_TICKET * $totalQuantity;

            $booking = Booking::create([
                'booking_code' => $this->generateUniqueCode(),
                'user_id' => $data['user_id'] ?? null,
                'concert_id' => $concert->id,
                'customer_name' => $data['customer_name'],
                'identity_number' => $data['identity_number'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'subtotal_amount' => $subtotal,
                'tax_amount' => $taxAmount,
                'admin_fee' => $adminFee,
                'total_amount' => $subtotal + $taxAmount + $adminFee,
                'status' => BookingStatus::CONFIRMED,
            ]);

            $booking->details()->createMany($detailsPayload);

            return $booking->load(['concert', 'details.ticketTier']);
        });
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'SND-' . Str::upper(Str::random(6));
        } while (Booking::where('booking_code', $code)->exists());

        return $code;
    }
}
