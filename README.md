<p align="center">
  <a href="#">
    <img src="https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&w=1200&q=80" alt="SoundPass Banner" width="100%" style="border-radius: 16px; max-height: 320px; object-fit: cover;">
  </a>
</p>

<h1 align="center">🎟️ SoundPass — Sistem Pemesanan Tiket Konser & Festival Musik</h1>

<p align="center">
  <strong>Aplikasi web pemesanan tiket konser musik modern, aman, dan responsif berbasis Laravel 11.</strong><br>
  Dilengkapi proteksi <em>concurrency & race condition</em> (pessimistic database locking), validasi NIK anti-calo, kalkulasi pajak real-time, serta penerbitan E-Ticket resmi ber-QR Code siap cetak format A4.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11">
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Alpine.js-3.13-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white" alt="Alpine.js">
  <img src="https://img.shields.io/badge/SQLite-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite">
  <img src="https://img.shields.io/badge/Tests-23_Passed-success?style=for-the-badge&logo=phpunit&logoColor=white" alt="Tests Passed">
  <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="License MIT">
</p>

---

## 📌 Daftar Isi
1. [Tentang SoundPass](#-tentang-soundpass)
2. [Fitur Utama & Alur Sistem](#-fitur-utama--alur-sistem)
3. [Arsitektur Teknis & Keamanan](#-arsitektur-teknis--keamanan)
4. [Skema Basis Data (Database Blueprint)](#-skema-basis-data-database-blueprint)
5. [Daftar Rute Aplikasi (Endpoints)](#-daftar-rute-aplikasi-endpoints)
6. [Tech Stack & Pustaka](#-tech-stack--pustaka)
7. [Panduan Instalasi & Menjalankan](#-panduan-instalasi--menjalankan)
8. [Akun Demo & Data Uji Coba](#-akun-demo--data-uji-coba)
9. [Pengujian Otomatis (Testing)](#-pengujian-otomatis-testing)
10. [Struktur Direktori Proyek](#-struktur-direktori-proyek)
11. [Lisensi](#-lisensi)

---

## 💡 Tentang SoundPass

**SoundPass** dibangun untuk menjawab tantangan industri konser musik di era digital, di mana antrean pembelian tiket sering kali dihadapkan pada masalah *overbooking* (kelebihan kuota terjual akibat *race condition* saat *war tiket*) dan praktik calo (*scalper*). 

SoundPass mengatasi masalah ini dengan:
- **Proteksi Kuota Atomik:** Menggunakan *Pessimistic Locking* di tingkat basis data saat transaksi berjalan, sehingga kuota tiket tidak akan pernah bisa bernilai minus atau terjual melebihi batas.
- **Validasi Identitas Pemesan (Anti-Calo):** Setiap 1 Nomor Induk Kependudukan (NIK 16 digit) dibatasi maksimal membeli 4 tiket per transaksi dan wajib diverifikasi saat penukaran gelang *wristband*.
- **Transparansi Biaya:** Menghitung otomatis Subtotal, Pajak Hiburan Daerah (10%), dan Biaya Layanan Platform (Rp 10.000/tiket) secara instan di sisi pengguna.
- **Penerbitan E-Ticket Siap Pakai:** Dilengkapi SVG QR Code dinamis dan format cetak presisi 1 lembar A4.

---

## 🚀 Fitur Utama & Alur Sistem

### 1. Katalog Konser (`GET /` atau `GET /concerts`)
- **15 Event Konser Lengkap:** Menampilkan artis papan atas (Raisa, Sheila On 7, AESPA, Hindia, DWP Bali, Borneo Jazz, Tulus, Green Day, NewJeans, The Adams, Ultra Beach Bali, Fourtwnty, Avenged Sevenfold, .Feast).
- **Pencarian Multi-Field:** Mencari instan berdasarkan nama artis (*headliner*), judul konser, nama *venue*, dan kota.
- **Filter Fleksibel:** Filter berdasarkan genre (*Pop, Rock, Indie, EDM, Jazz, K-Pop*), kota (*Jakarta, Bandung, Surabaya, Bali, Balikpapan*), dan rentang waktu.
- **Status Tiket Dinamis:**
  - 🟢 **Tersedia:** Sisa kuota > 20%
  - 🟡 **Segera Habis:** Sisa kuota 1% – 20%
  - 🔴 **Sold Out:** Sisa kuota 0 (tombol pemesanan dinonaktifkan otomatis)
- **Pagination Ramah URL:** Mempertahankan seluruh parameter filter saat berpindah halaman (`withQueryString()`).

### 2. Rincian Konser & Denah Panggung (`GET /concerts/{slug}`)
- Informasi lengkap tanggal acara, waktu *open gate*, alamat venue, dan deskripsi acara.
- **Modal Lightbox Denah Panggung (*Stage & Seat Map*):** Denah interaktif untuk melihat letak panggung dan zona menonton.
- Daftar kategori tiket (*Tribune, Festival Standing, VIP, VVIP*) beserta fasilitas dan sisa kuota *real-time*.
- Syarat & regulasi konser resmi (ketentuan NIK fisik, larangan barang terlarang, aturan *non-refundable*).

### 3. Pemesanan Interaktif & Formulir (`GET /concerts/{slug}/book` & `POST /bookings`)
- **Stepper Kuantitas Tiket (`-` / `+`):** Kontrol reaktif berbasis **Alpine.js** berukuran sentuh nyaman (`w-10 h-10`, min. 40×40px).
- **Batas Pembelian:** Sistem membatasi otomatis maksimal 4 tiket dalam satu transaksi.
- **Kalkulasi Harga Instan:**
  $$\text{Subtotal} = \sum (\text{Harga Tier} \times \text{Qty})$$
  $$\text{Pajak Hiburan} = 10\% \times \text{Subtotal}$$
  $$\text{Biaya Platform} = \text{Rp } 10.000 \times \text{Total Tiket}$$
  $$\text{Total Bayar} = \text{Subtotal} + \text{Pajak Hiburan} + \text{Biaya Platform}$$
- **Validasi Server-Side (`StoreBookingRequest`):** Memverifikasi format NIK tepat 16 digit, nomor telepon seluler Indonesia (`08...` / `+62...`), dan keabsahan kategori tiket.
- **Mobile Sticky Bottom Bar:** Panel ringkasan tiket dan tombol bayar melayang di bawah layar pada perangkat seluler.

### 4. E-Ticket Resmi & Cetak PDF (`GET /bookings/{booking_code}`)
- **Kode Booking Unik:** Kode alfanumerik acak 10 karakter (contoh: `SND-DEMO01`, `SND-8KP2L1`).
- **SVG QR Code Dinamis:** Dihasilkan secara langsung untuk dipindai oleh petugas *gate* venue.
- **Sensor NIK (Privasi):** Menyamarkan nomor identitas di antarmuka (contoh: `327301******0004`).
- **Mode Cetak Siap Pakai (`window.print()`):** Dilengkapi aturan CSS `@media print` presisi 1 lembar A4 tanpa elemen navbar, footer, atau tombol antarmuka.

### 5. Fitur Cek E-Ticket & Pemulihan Sesi (`GET /check-booking`)
- Memungkinkan penonton membuka kembali E-Ticket dari browser atau perangkat lain yang berbeda.
- Cukup memasukkan **Kode Booking** dan **NIK 16 Digit** atau **Email Pemesan**.
- Jika data cocok, sistem otomatis memulihkan sesi akses dan mengarahkan ke halaman e-ticket.

---

## 🛡️ Arsitektur Teknis & Keamanan

```
                                  [ Pengunjung / Browser ]
                                              │
                              ┌───────────────┴───────────────┐
                              ▼                               ▼
                     [ Katalog & Detail ]          [ Pemesanan Tiket ]
                              │                               │
                              │                    (StoreBookingRequest)
                              │                               ▼
                              │                    [ CreateBookingAction ]
                              │                               │
                              │                    DB::transaction()
                              │                    TicketTier::lockForUpdate()
                              │                    remaining_quota >= qty?
                              │                    (Kurangi kuota atomik)
                              │                               │
                              │                    Simpan Kode ke Session
                              │                               ▼
                              └───────────────► [ Halaman E-Ticket ]
                                                       │
                                            (Session Guard: Cek Akses)
                                             ├─ Valid ──► Tampilkan E-Ticket
                                             └─ Ilegal ─► Abort 403 Forbidden
```

- **Pessimistic Locking (`lockForUpdate()`):**
  Logika pengurangan sisa kuota dieksekusi di dalam `DB::transaction()` dengan mengunci baris `ticket_tiers` terkait. Jika dua pengguna memesan tiket terakhir di milidetik yang sama, salah satunya akan ditahan hingga transaksi pertama selesai; jika kuota habis, transaksi kedua melempar `QuotaExceededException` dan membatalkan (*rollback*) proses secara aman.
- **Database Integrity Constraint:**
  Tabel `ticket_tiers` memiliki pengaman `remaining_quota >= 0` baik di tingkat MySQL (native check constraint) maupun SQLite (database trigger).
- **Session Security Guard:**
  URL e-ticket (`/bookings/{code}`) tidak dapat ditebak secara bebas. Akses dibatasi ketat hanya untuk sesi browser pemesan aktif (`session('authorized_bookings')`) atau akun login pemilik booking (`abort_unless(..., 403)`).
- **Safe Session Timeout Handling:**
  Exception `TokenMismatchException` (419 Page Expired) ditangani secara elegan di `bootstrap/app.php` dengan redirect kembali disertai data input sebelumnya (`withInput()`) dan flash alert informatif.

---

## 🗄️ Skema Basis Data (Database Blueprint)

```
┌──────────────────┐       ┌────────────────────────┐       ┌──────────────────────┐
│     concerts     │       │        bookings        │       │        users         │
├──────────────────┤       ├────────────────────────┤       ├──────────────────────┤
│ id (PK)          │1     *│ id (PK)                │*     1│ id (PK)              │
│ title            ├───────┤ booking_code (UQ)      ├───────┤ name                 │
│ slug (UQ)        │       │ user_id (FK, Nullable) │       │ email (UQ)           │
│ headliner        │       │ concert_id (FK)        │       │ password             │
│ genre            │       │ customer_name          │       └──────────────────────┘
│ city             │       │ identity_number (NIK)  │
│ venue_name       │       │ customer_email         │
│ event_date       │       │ customer_phone         │
│ open_gate_time   │       │ subtotal_amount        │
│ poster_url       │       │ tax_amount             │
│ stage_map_url    │       │ admin_fee              │
│ description      │       │ total_amount           │
└────────┬─────────┘       │ status (ENUM)          │
         │ 1               └───────────┬────────────┘
         │                             │ 1
         │ *                           │ *
┌────────┴─────────┐       ┌───────────┴────────────┐
│   ticket_tiers   │       │    booking_details     │
├──────────────────┤       ├────────────────────────┤
│ id (PK)          │1     *│ id (PK)                │
│ concert_id (FK)  ├───────┤ booking_id (FK)        │
│ name             │       │ ticket_tier_id (FK)    │
│ description      │       │ quantity               │
│ price            │       │ unit_price             │
│ total_quota      │       │ subtotal               │
│ remaining_quota  │       └────────────────────────┘
└──────────────────┘
*CHECK(remaining_quota >= 0)
```

---

## 🌐 Daftar Rute Aplikasi (Endpoints)

| Method | URI Path | Nama Rute | Controller Action | Keterangan |
|:---:|:---|:---|:---|:---|
| `GET` | `/` | `concerts.index` | `ConcertController@index` | Katalog beranda & pencarian konser |
| `GET` | `/concerts` | `concerts.catalog` | `ConcertController@index` | Katalog konser utama |
| `GET` | `/concerts/{concert:slug}` | `concerts.show` | `ConcertController@show` | Halaman rincian konser & denah panggung |
| `GET` | `/concerts/{concert:slug}/book` | `bookings.create` | `BookingController@create` | Formulir pemesanan & pemilihan tiket |
| `POST` | `/bookings` | `bookings.store` | `BookingController@store` | Eksekusi transaksi pemesanan atomik |
| `GET` | `/check-booking` | `bookings.check` | `BookingController@checkForm` | Halaman verifikasi & pemulihan sesi e-ticket |
| `POST` | `/check-booking` | `bookings.verify` | `BookingController@verifyCheck` | Validasi kode booking & NIK/Email pemesan |
| `GET` | `/bookings/{booking:booking_code}` | `bookings.show` | `BookingController@show` | Halaman ringkasan E-Ticket resmi & cetak |

---

## 🛠️ Tech Stack & Pustaka

- **Core Framework:** [Laravel 11.x](https://laravel.com/)
- **Bahasa Pemrograman:** PHP 8.3+ (Strict Types `declare(strict_types=1);`)
- **Basis Data:** SQLite (bawaan lokal) / MySQL 8.0+
- **CSS Framework:** [Tailwind CSS 3.4](https://tailwindcss.com/)
- **JavaScript Engine:** [Alpine.js 3.13](https://alpinejs.dev/)
- **Bundler Aset:** [Vite 5.4](https://vitejs.dev/)
- **Generator QR Code:** [SimpleSoftwareIO QrCode 4.2](https://www.simplesoftware.io/docs/simple-qrcode)
- **Framework Testing:** [PHPUnit 10.5](https://phpunit.de/)

---

## 💻 Panduan Instalasi & Menjalankan

### 1. Prasyarat Sistem
Pastikan komputer Anda telah terpasang:
- **PHP 8.2 atau 8.3** dengan modul aktif: `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `curl`, `fileinfo`, `zip`, `intl`
- **Composer** (manajer paket PHP)
- **Node.js (v18+) & NPM**

### 2. Kloning Repositori
```bash
git clone https://github.com/USERNAME-ANDA/soundpass-ticket-konser.git
cd soundpass-ticket-konser
```

### 3. Install Dependensi Proyek
```bash
# Install paket backend Laravel
composer install

# Install paket frontend (Tailwind CSS, Alpine.js, Vite)
npm install
```

### 4. Konfigurasi Environment & Basis Data
Salin berkas konfigurasi lingkungan:
```bash
# Windows PowerShell / CMD
copy .env.example .env

# Linux / macOS
cp .env.example .env
```

Buat kunci enkripsi aplikasi:
```bash
php artisan key:generate
```

Jalankan migrasi tabel basis data dan isi dengan 15 data konser demo:
```bash
php artisan migrate:fresh --seed
```

### 5. Kompilasi Aset Frontend
```bash
npm run build
```
*(Atau gunakan `npm run dev` jika ingin mode pengembangan hot-reload).*

### 6. Jalankan Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui browser Anda di: **`http://127.0.0.1:8000`**

---

## 🎟️ Akun Demo & Data Uji Coba

Untuk mencoba fitur tanpa perlu memesan dari awal:

| Data | Keterangan / Nilai |
|:---|:---|
| **Akun Demo User** | `penonton@soundpass.id` *(Password: `password123`)* |
| **Kode Booking Demo** | `SND-DEMO01` |
| **NIK Demo** | `3273015508990004` |
| **Akses Langsung E-Ticket** | Buka menu **Cek E-Ticket** di navbar (`/check-booking`), masukkan kode booking dan NIK di atas untuk langsung membuka e-ticket terbitan SoundPass. |

---

## 🧪 Pengujian Otomatis (Testing)

SoundPass dilengkapi dengan suite pengujian otomatis menyeluruh:
- **Unit Tests:** Validasi enum status, model relationship, sensor NIK, Blade directive `@rupiah`, dan trigger constraint basis data.
- **Feature Tests:** Pengujian navigasi katalog, filter pencarian, validasi Form Request NIK & batas 4 tiket, atomisitas kuota, pencegahan overbooking, session guard 403, dan penanganan CSRF expired.

Jalankan pengujian via terminal:
```bash
php artisan test
```

Hasil verifikasi:
```text
   PASS  Tests\Unit\AppServiceProviderTest
  ✓ rupiah blade directive formats amount correctly

   PASS  Tests\Unit\DomainModelTest
  ✓ booking status enum methods
  ✓ concert ticket status accessors
  ✓ booking relationships and masked nik
  ✓ check constraint rejects negative remaining quota

   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\BookingEngineTest
  ✓ booking create page loads for available concert
  ✓ booking create guards against sold out concert
  ✓ booking form request validates nik and max tickets
  ✓ atomic booking process decrements quota and redirects
  ✓ overbooking protection throws and preserves quota
  ✓ e ticket page is protected by session guard
  ✓ check booking form loads successfully
  ✓ check booking verification restores session with valid nik
  ✓ check booking verification restores session with valid email
  ✓ check booking fails with invalid credentials

   PASS  Tests\Feature\ConcertCatalogTest
  ✓ catalog page loads successfully with seeded concerts
  ✓ catalog search filters by title or headliner
  ✓ catalog genre and city filtering
  ✓ concert detail page loads with tiers and facilities
  ✓ sold out concert shows disabled cta

   PASS  Tests\Feature\CsrfMismatchTest
  ✓ token mismatch exception redirects back with input and warning flash

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response

  Tests:    23 passed (97 assertions)
  Duration: 1.85s
```

---

## 📂 Struktur Direktori Proyek

```text
soundpass-ticket-konser/
├── app/
│   ├── Actions/
│   │   └── CreateBookingAction.php     # Engine pemesanan atomik & DB lock
│   ├── Enums/
│   │   └── BookingStatus.php           # Backed Enum status pemesanan
│   ├── Exceptions/
│   │   └── QuotaExceededException.php  # Exception kuota habis/tidak cukup
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── BookingController.php   # Controller booking, e-ticket & guard
│   │   │   └── ConcertController.php   # Controller katalog & detail konser
│   │   └── Requests/
│   │       └── StoreBookingRequest.php # Validasi input form & batas 4 tiket
│   └── Models/
│       ├── Booking.php                 # Model pesanan & sensor NIK
│       ├── BookingDetail.php           # Model rincian item tiket
│       ├── Concert.php                 # Model konser & kalkulasi status kuota
│       └── TicketTier.php              # Model kategori tier tiket
├── database/
│   ├── database.sqlite                 # File database lokal SQLite
│   ├── migrations/                     # Skema tabel & database triggers
│   └── seeders/
│       └── DatabaseSeeder.php          # Seeder 15 konser & 46 kategori tiket
├── resources/
│   ├── css/app.css                     # Konfigurasi Tailwind CSS
│   ├── js/app.js                       # Inisialisasi Alpine.js & script
│   └── views/
│       ├── bookings/
│       │   ├── check.blade.php         # Form cek e-ticket & pemulihan sesi
│       │   ├── create.blade.php        # Stepper tiket & form identitas
│       │   └── show.blade.php          # E-Ticket resmi & cetak PDF A4
│       ├── components/                 # Reusable Blade UI components
│       ├── concerts/
│       │   ├── index.blade.php         # Halaman katalog 15 konser
│       │   └── show.blade.php          # Halaman detail & denah panggung
│       └── layouts/
│           └── app.blade.php           # Template layout induk aplikasi
├── routes/
│   └── web.php                         # Definisi rute web aplikasi
└── tests/                              # Suite pengujian unit & fitur
```

---

## 📜 Lisensi

Aplikasi ini bersifat *open-source* di bawah lisensi [MIT License](LICENSE).
Bebas dimodifikasi, dikembangkan, dan digunakan untuk keperluan pembelajaran maupun portofolio profesional.
