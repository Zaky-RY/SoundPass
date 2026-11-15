<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $booking_code
 * @property int|null $user_id
 * @property int $concert_id
 * @property string $customer_name
 * @property string $identity_number
 * @property string $customer_email
 * @property string $customer_phone
 * @property int $subtotal_amount
 * @property int $tax_amount
 * @property int $admin_fee
 * @property int $total_amount
 * @property BookingStatus $status
 * @property-read string $masked_identity_number
 * @property-read int $total_tickets
 */
final class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'user_id',
        'concert_id',
        'customer_name',
        'identity_number',
        'customer_email',
        'customer_phone',
        'subtotal_amount',
        'tax_amount',
        'admin_fee',
        'total_amount',
        'status',
    ];

    public function getRouteKeyName(): string
    {
        return 'booking_code';
    }

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'subtotal_amount' => 'integer',
            'tax_amount' => 'integer',
            'admin_fee' => 'integer',
            'total_amount' => 'integer',
        ];
    }

    public function concert(): BelongsTo
    {
        return $this->belongsTo(Concert::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }

    /**
     * Sensor sebagian NIK untuk privasi (contoh: 327301******0004)
     */
    protected function maskedIdentityNumber(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if (strlen($this->identity_number) < 12) {
                    return $this->identity_number;
                }

                $prefix = substr($this->identity_number, 0, 6);
                $suffix = substr($this->identity_number, -4);
                $maskLength = max(0, strlen($this->identity_number) - 10);

                return $prefix . str_repeat('*', $maskLength) . $suffix;
            }
        );
    }

    protected function totalTickets(): Attribute
    {
        return Attribute::make(
            get: fn (): int => (int) $this->details->sum('quantity')
        );
    }
}
