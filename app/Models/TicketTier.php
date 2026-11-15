<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $concert_id
 * @property string $name
 * @property string|null $description
 * @property int $price
 * @property int $total_quota
 * @property int $remaining_quota
 * @property-read bool $is_sold_out
 * @property-read bool $is_available
 */
final class TicketTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'concert_id',
        'name',
        'description',
        'price',
        'total_quota',
        'remaining_quota',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'total_quota' => 'integer',
            'remaining_quota' => 'integer',
        ];
    }

    public function concert(): BelongsTo
    {
        return $this->belongsTo(Concert::class);
    }

    public function bookingDetails(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }

    protected function isSoldOut(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->remaining_quota <= 0
        );
    }

    protected function isAvailable(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->remaining_quota > 0
        );
    }
}
