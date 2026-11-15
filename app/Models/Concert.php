<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $headliner
 * @property string $genre
 * @property string $city
 * @property string $venue_name
 * @property CarbonInterface $event_date
 * @property string $open_gate_time
 * @property string|null $poster_url
 * @property string|null $stage_map_url
 * @property string $description
 * @property string $ticket_status
 * @property string $ticket_status_badge_classes
 * @property int $starting_price
 * @property int $total_remaining_quota
 */
final class Concert extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'headliner',
        'genre',
        'city',
        'venue_name',
        'event_date',
        'open_gate_time',
        'poster_url',
        'stage_map_url',
        'description',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'event_date' => 'immutable_date',
        ];
    }

    public function ticketTiers(): HasMany
    {
        return $this->hasMany(TicketTier::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Menghitung status ketersediaan tiket:
     * - 'Sold Out': Sisa kuota = 0
     * - 'Segera Habis': Sisa kuota antara 1% hingga 20%
     * - 'Tersedia': Sisa kuota > 20%
     */
    protected function ticketStatus(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $totalQuota = (int) $this->ticketTiers->sum('total_quota');
                $remainingQuota = (int) $this->ticketTiers->sum('remaining_quota');

                if ($totalQuota === 0 || $remainingQuota <= 0) {
                    return 'Sold Out';
                }

                $percentage = ($remainingQuota / $totalQuota) * 100;

                if ($percentage <= 20) {
                    return 'Segera Habis';
                }

                return 'Tersedia';
            }
        );
    }

    protected function ticketStatusBadgeClasses(): Attribute
    {
        return Attribute::make(
            get: fn (): string => match ($this->ticket_status) {
                'Tersedia' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'Segera Habis' => 'bg-amber-100 text-amber-800 border-amber-200',
                default => 'bg-rose-100 text-rose-800 border-rose-200',
            }
        );
    }

    protected function startingPrice(): Attribute
    {
        return Attribute::make(
            get: fn (): int => (int) ($this->ticketTiers->min('price') ?? 0)
        );
    }

    protected function totalRemainingQuota(): Attribute
    {
        return Attribute::make(
            get: fn (): int => (int) $this->ticketTiers->sum('remaining_quota')
        );
    }
}
