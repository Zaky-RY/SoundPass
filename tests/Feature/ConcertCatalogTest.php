<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Concert;
use Tests\TestCase;

class ConcertCatalogTest extends TestCase
{
    public function test_catalog_page_loads_successfully_with_seeded_concerts(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('SoundPass');
        $response->assertSee('Katalog Konser Musik');
        $response->assertSee('Raisa Live in Concert: Heart to Heart');
        $response->assertSee('Sheila On 7: Tunggu Aku di Bandung');
        $response->assertSee('AESPA LIVE TOUR 2026: SYNK Hyper Line');
    }

    public function test_catalog_search_filters_by_title_or_headliner(): void
    {
        $response = $this->get('/?search=Sheila');

        $response->assertStatus(200);
        $response->assertSee('Sheila On 7: Tunggu Aku di Bandung');
        $response->assertDontSee('AESPA LIVE TOUR 2026: SYNK Hyper Line');
    }

    public function test_catalog_genre_and_city_filtering(): void
    {
        $response = $this->get('/?genre=EDM&city=Bali');

        $response->assertStatus(200);
        $response->assertSee('Djakarta Warehouse Project (DWP) Tropical Edition 2026');
        $response->assertDontSee('Raisa Live in Concert');
    }

    public function test_concert_detail_page_loads_with_tiers_and_facilities(): void
    {
        $concert = Concert::where('slug', 'raisa-live-in-concert-heart-to-heart')->firstOrFail();

        $response = $this->get('/concerts/' . $concert->slug);

        $response->assertStatus(200);
        $response->assertSee($concert->title);
        $response->assertSee($concert->venue_name);
        $response->assertSee('Stadion Madya Gelora Bung Karno');
        $response->assertSee('Tribune - Free Seating');
        $response->assertSee('Festival - Standing');
        $response->assertSee('VIP - Numbered Seat');
        $response->assertSee('VVIP Royal Lounge');
        $response->assertSee('Syarat & Regulasi Konser', false);
        $response->assertSee('Beli Tiket Sekarang');
    }

    public function test_sold_out_concert_shows_disabled_cta(): void
    {
        $soldOutConcert = Concert::where('slug', 'aespa-live-tour-2026-synk-hyper-line-jakarta')->firstOrFail();

        $response = $this->get('/concerts/' . $soldOutConcert->slug);

        $response->assertStatus(200);
        $response->assertSee('Sold Out');
        $response->assertSee('Tiket Habis (Sold Out)');
    }
}
