<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Concert;
use App\Models\TicketTier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with concert catalog & ticket tiers.
     */
    public function run(): void
    {
        // Buat akun demo user
        $demoUser = User::firstOrCreate(
            ['email' => 'penonton@soundpass.id'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $concertsData = [
            [
                'title' => 'Raisa Live in Concert: Heart to Heart',
                'slug' => 'raisa-live-in-concert-heart-to-heart',
                'headliner' => 'Raisa',
                'genre' => 'Pop',
                'city' => 'Jakarta',
                'venue_name' => 'Stadion Madya Gelora Bung Karno',
                'event_date' => Carbon::now()->addDays(24)->toDateString(),
                'open_gate_time' => '16:00 WIB',
                'poster_url' => 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Konser solo megah Raisa menyambut akhir tahun dengan tata panggung panggung 360 derajat, visual imersif, dan aransemen orkestra spektakuler membawakan tembang-tembang hits terbaiknya.',
                'tiers' => [
                    [
                        'name' => 'Tribune - Free Seating',
                        'description' => 'Akses tribun atas dengan pemandangan panggung menyeluruh dan nomor kursi bebas.',
                        'price' => 450000,
                        'total_quota' => 600,
                        'remaining_quota' => 450,
                    ],
                    [
                        'name' => 'Festival - Standing',
                        'description' => 'Area berdiri bebas di depan panggung utama dengan atmosfer konser paling hidup.',
                        'price' => 850000,
                        'total_quota' => 1200,
                        'remaining_quota' => 920,
                    ],
                    [
                        'name' => 'VIP - Numbered Seat',
                        'description' => 'Kursi bernomor tepat di depan FOH dengan sudut pandang audio-visual terbaik, lanyard eksklusif, dan akses toilet khusus.',
                        'price' => 1750000,
                        'total_quota' => 300,
                        'remaining_quota' => 180,
                    ],
                    [
                        'name' => 'VVIP Royal Lounge',
                        'description' => 'Area sofa privat ber-AC, jamuan makan malam prasmanan, jalur masuk khusus tanpa antre, dan merchandise bertanda tangan.',
                        'price' => 3500000,
                        'total_quota' => 50,
                        'remaining_quota' => 32,
                    ],
                ],
            ],
            [
                'title' => 'Sheila On 7: Tunggu Aku di Bandung',
                'slug' => 'sheila-on-7-tunggu-aku-di-bandung',
                'headliner' => 'Sheila On 7',
                'genre' => 'Rock',
                'city' => 'Bandung',
                'venue_name' => 'Stadion Siliwangi Bandung',
                'event_date' => Carbon::now()->addDays(14)->toDateString(),
                'open_gate_time' => '15:00 WIB',
                'poster_url' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Nostalgia dan energi tanpa batas bersama Duta, Eross, dan Adam dalam tur konser stadion Sheila On 7 yang telah lama dinanti oleh Sheila Gank di Jawa Barat.',
                // Kategori ini disimulasikan HAMPIR HABIS (kuota total tersisa <= 20%)
                'tiers' => [
                    [
                        'name' => 'Tribune - Free Seating',
                        'description' => 'Area duduk tribun barat stadion.',
                        'price' => 375000,
                        'total_quota' => 500,
                        'remaining_quota' => 20, // 4% tersisa
                    ],
                    [
                        'name' => 'Festival - Standing',
                        'description' => 'Area berdiri lapangan utama stadion dekat panggung.',
                        'price' => 550000,
                        'total_quota' => 800,
                        'remaining_quota' => 35, // 4.3% tersisa
                    ],
                    [
                        'name' => 'VIP - Numbered Seat',
                        'description' => 'Tribun utama bernomor dengan kanopi teduh dan paket merchandise.',
                        'price' => 1250000,
                        'total_quota' => 200,
                        'remaining_quota' => 8, // 4% tersisa
                    ],
                ],
            ],
            [
                'title' => 'AESPA LIVE TOUR 2026: SYNK Hyper Line',
                'slug' => 'aespa-live-tour-2026-synk-hyper-line-jakarta',
                'headliner' => 'AESPA',
                'genre' => 'K-Pop',
                'city' => 'Jakarta',
                'venue_name' => 'Indonesia Convention Exhibition (ICE) BSD City Hall 5-6',
                'event_date' => Carbon::now()->addDays(6)->toDateString(),
                'open_gate_time' => '13:00 WIB',
                'poster_url' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Kuartet metaverse masa depan Karina, Giselle, Winter, dan Ningning hadir mengguncang Jakarta dengan tata visual futuristik nan masif.',
                // Kategori ini disimulasikan SOLD OUT (kuota total = 0)
                'tiers' => [
                    [
                        'name' => 'CAT 3 - Seated Tribune',
                        'description' => 'Area duduk bertingkat belakang FOH.',
                        'price' => 1450000,
                        'total_quota' => 600,
                        'remaining_quota' => 0, // SOLD OUT
                    ],
                    [
                        'name' => 'CAT 1 - Festival Standing',
                        'description' => 'Area berdiri mengelilingi runway panggung.',
                        'price' => 2350000,
                        'total_quota' => 1000,
                        'remaining_quota' => 0, // SOLD OUT
                    ],
                    [
                        'name' => 'VIP - Soundcheck Package',
                        'description' => 'Akses sesi soundcheck pre-show, jalur masuk prioritas, dan lanyard resmi tur.',
                        'price' => 3650000,
                        'total_quota' => 300,
                        'remaining_quota' => 0, // SOLD OUT
                    ],
                ],
            ],
            [
                'title' => 'Hindia: Lagipula Hidup Akan Berakhir di Surabaya',
                'slug' => 'hindia-lagipula-hidup-akan-berakhir-surabaya',
                'headliner' => 'Hindia',
                'genre' => 'Indie',
                'city' => 'Surabaya',
                'venue_name' => 'Grand City Convention Hall Surabaya',
                'event_date' => Carbon::now()->addDays(32)->toDateString(),
                'open_gate_time' => '17:00 WIB',
                'poster_url' => 'https://images.unsplash.com/photo-1465847899084-d164df4dedc6?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Perayaan katarsis emosional dan musikalitas personal Baskara Putra membawakan album ganda dalam format pertunjukan teatrikal penuh.',
                'tiers' => [
                    [
                        'name' => 'Early Bird - Festival Standing',
                        'description' => 'Tiket presale dengan kuota terbatas.',
                        'price' => 250000,
                        'total_quota' => 300,
                        'remaining_quota' => 120,
                    ],
                    [
                        'name' => 'Festival - Standing',
                        'description' => 'Akses area standing utama hall konvensi.',
                        'price' => 380000,
                        'total_quota' => 700,
                        'remaining_quota' => 490,
                    ],
                    [
                        'name' => 'VIP - Numbered Seat with Meet & Greet',
                        'description' => 'Kursi nomor terdepan disertai sesi temu sapa singkat dan photocard eksklusif.',
                        'price' => 950000,
                        'total_quota' => 100,
                        'remaining_quota' => 64,
                    ],
                ],
            ],
            [
                'title' => 'Djakarta Warehouse Project (DWP) Tropical Edition 2026',
                'slug' => 'dwp-tropical-edition-2026-bali',
                'headliner' => 'Martin Garrix & Peggy Gou',
                'genre' => 'EDM',
                'city' => 'Bali',
                'venue_name' => 'GWK Cultural Park Bali',
                'event_date' => Carbon::now()->addDays(50)->toDateString(),
                'open_gate_time' => '15:00 WITA',
                'poster_url' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Festival musik elektronik terbesar di Asia Tenggara kembali mengguncang tebing megah GWK Bali dengan rentetan DJ papan atas dunia dan pesta kembang api spektakuler.',
                'tiers' => [
                    [
                        'name' => 'GA - Early Entry (Before 18:00)',
                        'description' => 'Tiket masuk general admission dengan syarat masuk venue sebelum pukul 18:00 WITA.',
                        'price' => 990000,
                        'total_quota' => 800,
                        'remaining_quota' => 610,
                    ],
                    [
                        'name' => 'Festival - Standing (3-Day Pass)',
                        'description' => 'Akses festival standing bebas 3 hari berturut-turut untuk seluruh panggung.',
                        'price' => 1950000,
                        'total_quota' => 1500,
                        'remaining_quota' => 1100,
                    ],
                    [
                        'name' => 'VIP Gold - Private Deck',
                        'description' => 'Akses panggung dek bertingkat dengan bar pribadi, toilet eksekutif, dan antrean khusus.',
                        'price' => 4500000,
                        'total_quota' => 250,
                        'remaining_quota' => 170,
                    ],
                ],
            ],
            [
                'title' => 'Borneo Sunset Jazz Festival 2026',
                'slug' => 'borneo-sunset-jazz-festival-2026-balikpapan',
                'headliner' => "Maliq & D'Essentials feat. Indra Lesmana",
                'genre' => 'Jazz',
                'city' => 'Balikpapan',
                'venue_name' => 'Pantai BSB Balikpapan',
                'event_date' => Carbon::now()->addDays(38)->toDateString(),
                'open_gate_time' => '16:30 WITA',
                'poster_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Alunan jazz syahdu berlatar matahari terbenam Selat Makassar yang magis, menyajikan kolaborasi jazz modern nan elegan di tepi pantai.',
                'tiers' => [
                    [
                        'name' => 'Tribune - Free Seating (Sunset Pass)',
                        'description' => 'Area duduk santai berundak menghadap ke arah laut dan panggung.',
                        'price' => 350000,
                        'total_quota' => 400,
                        'remaining_quota' => 310,
                    ],
                    [
                        'name' => 'Festival - Standing',
                        'description' => 'Area pasir pantai depan panggung utama.',
                        'price' => 550000,
                        'total_quota' => 600,
                        'remaining_quota' => 480,
                    ],
                    [
                        'name' => 'VIP - Numbered Seat with Sunset Dinner',
                        'description' => 'Meja bernomor dengan sajian jamuan makan malam bernuansa pantai dan suvenir festival.',
                        'price' => 1500000,
                        'total_quota' => 120,
                        'remaining_quota' => 95,
                    ],
                ],
            ],
            [
                'title' => 'Tulus: Manusia Tur Jawa Barat',
                'slug' => 'tulus-manusia-tur-jawa-barat-bandung',
                'headliner' => 'Tulus',
                'genre' => 'Pop',
                'city' => 'Bandung',
                'venue_name' => 'Eldorado Dome Bandung',
                'event_date' => Carbon::now()->addDays(18)->toDateString(),
                'open_gate_time' => '16:00 WIB',
                'poster_url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Lantunan lirik puitis dan orkestrasi megah dalam tur album Manusia bersama Tulus, merayakan perjalanan rasa dan kemanusiaan di Kota Kembang.',
                'tiers' => [
                    [
                        'name' => 'Tribune - Free Seating',
                        'description' => 'Area tribun duduk dengan pemandangan panggung menyeluruh.',
                        'price' => 400000,
                        'total_quota' => 500,
                        'remaining_quota' => 380,
                    ],
                    [
                        'name' => 'Festival - Standing',
                        'description' => 'Area berdiri di depan panggung utama.',
                        'price' => 650000,
                        'total_quota' => 900,
                        'remaining_quota' => 710,
                    ],
                    [
                        'name' => 'VIP - Numbered Seat',
                        'description' => 'Kursi nomor terbaik dengan goodie bag eksklusif.',
                        'price' => 1350000,
                        'total_quota' => 200,
                        'remaining_quota' => 125,
                    ],
                ],
            ],
            [
                'title' => 'Green Day: The Saviors Tour Jakarta',
                'slug' => 'green-day-the-saviors-tour-jakarta',
                'headliner' => 'Green Day',
                'genre' => 'Rock',
                'city' => 'Jakarta',
                'venue_name' => 'Carnaval Ancol Circuit Jakarta',
                'event_date' => Carbon::now()->addDays(45)->toDateString(),
                'open_gate_time' => '15:30 WIB',
                'poster_url' => 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Legenda punk rock dunia Billie Joe Armstrong, Mike Dirnt, dan Tre Cool kembali menggebrak Jakarta merayakan 30 tahun Dookie dan 20 tahun American Idiot.',
                'tiers' => [
                    [
                        'name' => 'CAT 2 - Festival Standing',
                        'description' => 'Area standing festival umum tepi panggung.',
                        'price' => 1250000,
                        'total_quota' => 1500,
                        'remaining_quota' => 120,
                    ],
                    [
                        'name' => 'CAT 1 - Front Stage Moshpit',
                        'description' => 'Area festival paling depan menghadap panggung utama.',
                        'price' => 2100000,
                        'total_quota' => 1000,
                        'remaining_quota' => 80,
                    ],
                    [
                        'name' => 'VIP Rock Lounge',
                        'description' => 'Akses tenda VIP ber-AC, bar khusus, dan official tour merchandise.',
                        'price' => 3800000,
                        'total_quota' => 300,
                        'remaining_quota' => 15,
                    ],
                ],
            ],
            [
                'title' => 'NewJeans: Get Up World Tour Jakarta',
                'slug' => 'newjeans-get-up-world-tour-jakarta',
                'headliner' => 'NewJeans',
                'genre' => 'K-Pop',
                'city' => 'Jakarta',
                'venue_name' => 'Jakarta International Stadium (JIS)',
                'event_date' => Carbon::now()->addDays(28)->toDateString(),
                'open_gate_time' => '14:00 WIB',
                'poster_url' => 'https://images.unsplash.com/photo-1524368535928-5b5e00ddc76b?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Sensasi pop global Minji, Hanni, Danielle, Haerin, dan Hyein siap menyapa Bunnies Indonesia dalam konser stadion spektakuler pertama mereka.',
                'tiers' => [
                    [
                        'name' => 'CAT 3 - Upper Tribune',
                        'description' => 'Tribun atas stadion bernomor kursi.',
                        'price' => 1100000,
                        'total_quota' => 800,
                        'remaining_quota' => 620,
                    ],
                    [
                        'name' => 'CAT 2 - Lower Tribune',
                        'description' => 'Tribun bawah dengan sudut pandang panggung prima.',
                        'price' => 1950000,
                        'total_quota' => 1200,
                        'remaining_quota' => 950,
                    ],
                    [
                        'name' => 'Festival VIP - Soundcheck Pass',
                        'description' => 'Area berdiri terdepan dengan akses soundcheck sebelum konser dimulai.',
                        'price' => 3500000,
                        'total_quota' => 600,
                        'remaining_quota' => 410,
                    ],
                ],
            ],
            [
                'title' => 'The Adams & Barasuara: Gelora Musik Surabaya',
                'slug' => 'the-adams-barasuara-gelora-musik-surabaya',
                'headliner' => 'The Adams x Barasuara',
                'genre' => 'Rock',
                'city' => 'Surabaya',
                'venue_name' => 'Jatim Expo Convention Center Surabaya',
                'event_date' => Carbon::now()->addDays(12)->toDateString(),
                'open_gate_time' => '17:30 WIB',
                'poster_url' => 'https://images.unsplash.com/photo-1464375117522-1311d6a5b81f?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Kolaborasi dua raksasa rock Indonesia dalam satu panggung penuh energi menyanyikan lagu-lagu legendaris dari Masa Demi Masa hingga Taifun.',
                'tiers' => [
                    [
                        'name' => 'Early Bird - Festival Standing',
                        'description' => 'Tiket presale awal kuota terbatas.',
                        'price' => 200000,
                        'total_quota' => 300,
                        'remaining_quota' => 150,
                    ],
                    [
                        'name' => 'Festival - Standing Reguler',
                        'description' => 'Akses area standing hall festival.',
                        'price' => 320000,
                        'total_quota' => 800,
                        'remaining_quota' => 540,
                    ],
                    [
                        'name' => 'VIP Backstage Access',
                        'description' => 'Kursi nomor VIP dan akses temu musisi di belakang panggung.',
                        'price' => 750000,
                        'total_quota' => 100,
                        'remaining_quota' => 65,
                    ],
                ],
            ],
            [
                'title' => 'Ultra Beach Bali 2026',
                'slug' => 'ultra-beach-bali-2026',
                'headliner' => 'Skrillex & Zedd',
                'genre' => 'EDM',
                'city' => 'Bali',
                'venue_name' => 'Locca Sea House Jimbaran Bali',
                'event_date' => Carbon::now()->addDays(60)->toDateString(),
                'open_gate_time' => '14:00 WITA',
                'poster_url' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Dentuman bass di tepi pantai eksotis Jimbaran Bali bersama bintang DJ dunia dalam gelaran festival musik pantai paling bergengsi.',
                'tiers' => [
                    [
                        'name' => '2-Day Pass GA',
                        'description' => 'Akses masuk umum 2 hari berturut-turut.',
                        'price' => 1850000,
                        'total_quota' => 1200,
                        'remaining_quota' => 890,
                    ],
                    [
                        'name' => 'VIP Standing Deck',
                        'description' => 'Panggung bertingkat dengan pemandangan sunset dan bar khusus.',
                        'price' => 3200000,
                        'total_quota' => 400,
                        'remaining_quota' => 280,
                    ],
                    [
                        'name' => 'VVIP Private Cabana',
                        'description' => 'Sofa privat untuk grup hingga 8 orang dengan paket minuman premium.',
                        'price' => 8500000,
                        'total_quota' => 30,
                        'remaining_quota' => 18,
                    ],
                ],
            ],
            [
                'title' => 'Java Jazz on the Beach Bali 2026',
                'slug' => 'java-jazz-on-the-beach-bali-2026',
                'headliner' => 'Tompi & Teza Sumendra',
                'genre' => 'Jazz',
                'city' => 'Bali',
                'venue_name' => 'Nusa Dua Beach Resort Bali',
                'event_date' => Carbon::now()->addDays(35)->toDateString(),
                'open_gate_time' => '16:00 WITA',
                'poster_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Harmoni vokal jazz dan soul khas Indonesia berpadu dengan deburan ombak dan angin senja pantai Nusa Dua Bali.',
                'tiers' => [
                    [
                        'name' => 'Sunset Lawn Pass',
                        'description' => 'Area santai di atas rumput tepi pantai.',
                        'price' => 450000,
                        'total_quota' => 500,
                        'remaining_quota' => 360,
                    ],
                    [
                        'name' => 'Golden Seated',
                        'description' => 'Kursi nyaman depan panggung akustik.',
                        'price' => 950000,
                        'total_quota' => 300,
                        'remaining_quota' => 210,
                    ],
                    [
                        'name' => 'Diamond Lounge & Dinner',
                        'description' => 'Akses lounge tertutup dengan sajian makan malam buffet 5-star.',
                        'price' => 1800000,
                        'total_quota' => 100,
                        'remaining_quota' => 75,
                    ],
                ],
            ],
            [
                'title' => 'Fourtwnty: Nalar Tour Balikpapan',
                'slug' => 'fourtwnty-nalar-tour-balikpapan',
                'headliner' => 'Fourtwnty',
                'genre' => 'Indie',
                'city' => 'Balikpapan',
                'venue_name' => 'Balikpapan Sport and Convention Center (DOME)',
                'event_date' => Carbon::now()->addDays(22)->toDateString(),
                'open_gate_time' => '17:00 WITA',
                'poster_url' => 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Menenangkan pikiran dan merayakan kebersamaan lewat petikan akustik Ari Lesmana dan kawan-kawan dalam perhelatan tur album Nalar.',
                'tiers' => [
                    [
                        'name' => 'Tribune - Free Seating',
                        'description' => 'Duduk santai di tribun atas dome gedung.',
                        'price' => 180000,
                        'total_quota' => 400,
                        'remaining_quota' => 290,
                    ],
                    [
                        'name' => 'Festival - Zona Damai',
                        'description' => 'Area berdiri depan panggung utama.',
                        'price' => 280000,
                        'total_quota' => 800,
                        'remaining_quota' => 620,
                    ],
                    [
                        'name' => 'VIP - Acoustic & Meet',
                        'description' => 'Kursi depan plus sesi foto bersama personel.',
                        'price' => 550000,
                        'total_quota' => 150,
                        'remaining_quota' => 110,
                    ],
                ],
            ],
            [
                'title' => 'Avenged Sevenfold: Life Is But A Dream Indonesia',
                'slug' => 'avenged-sevenfold-life-is-but-a-dream-jakarta',
                'headliner' => 'Avenged Sevenfold',
                'genre' => 'Rock',
                'city' => 'Jakarta',
                'venue_name' => 'Stadion Madya Gelora Bung Karno Jakarta',
                'event_date' => Carbon::now()->addDays(15)->toDateString(),
                'open_gate_time' => '15:00 WIB',
                'poster_url' => 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Satu-satunya persinggahan tur Asia Tenggara A7X di Jakarta menampilkan panggung megah, solo gitar Synyster Gates, dan lagu-lagu legendaris.',
                'tiers' => [
                    [
                        'name' => 'CAT 3 - Upper Tribune',
                        'description' => 'Tribun atas stadion.',
                        'price' => 1350000,
                        'total_quota' => 1000,
                        'remaining_quota' => 0,
                    ],
                    [
                        'name' => 'CAT 1 - Festival Standing',
                        'description' => 'Area festival berdiri depan panggung.',
                        'price' => 2400000,
                        'total_quota' => 1500,
                        'remaining_quota' => 0,
                    ],
                    [
                        'name' => 'VIP Moshpit Experience',
                        'description' => 'Akses eksklusif pit depan panggung dan cinderamata resmi.',
                        'price' => 3900000,
                        'total_quota' => 400,
                        'remaining_quota' => 0,
                    ],
                ],
            ],
            [
                'title' => 'Feast & Reality Club: Indie Soundscape Bandung',
                'slug' => 'feast-reality-club-indie-soundscape-bandung',
                'headliner' => '.Feast x Reality Club',
                'genre' => 'Indie',
                'city' => 'Bandung',
                'venue_name' => 'Sasana Budaya Ganesha (Sabuga) ITB Bandung',
                'event_date' => Carbon::now()->addDays(40)->toDateString(),
                'open_gate_time' => '16:30 WIB',
                'poster_url' => 'https://images.unsplash.com/photo-1465847899084-d164df4dedc6?auto=format&fit=crop&w=1200&q=80',
                'stage_map_url' => 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Dua ikon musik independen masa kini berbagi panggung dalam konser audio-visual spektakuler di gedung bersejarah Sabuga Bandung.',
                'tiers' => [
                    [
                        'name' => 'Presale - Festival Standing',
                        'description' => 'Area festival presale kuota terbatas.',
                        'price' => 220000,
                        'total_quota' => 400,
                        'remaining_quota' => 270,
                    ],
                    [
                        'name' => 'Reguler - Festival Standing',
                        'description' => 'Akses festival standing umum.',
                        'price' => 350000,
                        'total_quota' => 800,
                        'remaining_quota' => 590,
                    ],
                    [
                        'name' => 'VIP Balcony Box',
                        'description' => 'Area balkon atas dengan tempat duduk terbaik dan merchandise.',
                        'price' => 800000,
                        'total_quota' => 150,
                        'remaining_quota' => 110,
                    ],
                ],
            ],
        ];

        foreach ($concertsData as $concertItem) {
            $tiers = $concertItem['tiers'];
            unset($concertItem['tiers']);

            /** @var Concert $concert */
            $concert = Concert::create($concertItem);

            foreach ($tiers as $tier) {
                $concert->ticketTiers()->create($tier);
            }
        }

        // Contoh 1 booking contoh terkonfirmasi untuk demo/testing
        $sampleConcert = Concert::where('slug', 'raisa-live-in-concert-heart-to-heart')->first();
        if ($sampleConcert) {
            $sampleTier = $sampleConcert->ticketTiers()->first();
            if ($sampleTier) {
                $subtotal = $sampleTier->price * 2;
                $taxAmount = (int) round($subtotal * 0.10);
                $adminFee = 10000 * 2;

                /** @var Booking $booking */
                $booking = Booking::create([
                    'booking_code' => 'SND-DEMO01',
                    'user_id' => $demoUser->id,
                    'concert_id' => $sampleConcert->id,
                    'customer_name' => 'Budi Santoso',
                    'identity_number' => '3273015508990004',
                    'customer_email' => 'penonton@soundpass.id',
                    'customer_phone' => '081234567890',
                    'subtotal_amount' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'admin_fee' => $adminFee,
                    'total_amount' => $subtotal + $taxAmount + $adminFee,
                    'status' => BookingStatus::CONFIRMED,
                ]);

                $booking->details()->create([
                    'ticket_tier_id' => $sampleTier->id,
                    'quantity' => 2,
                    'unit_price' => $sampleTier->price,
                    'subtotal' => $subtotal,
                ]);
            }
        }
    }
}
