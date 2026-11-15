# PRODUCT REQUIREMENT DOCUMENT (PRD)
**Proyek:** SoundPass — Sistem Pemesanan Tiket Konser & Festival Musik Berbasis Web  
**Domain:** Event Musik / Konser (*Concert Ticketing System*)  
**Tech Stack:** Laravel 11.x, PHP 8.3+, MySQL 8.0+, Blade Templates, Tailwind CSS 3.x, Alpine.js 3.x  

---

## 1. Overview & Scope Produk
* **Deskripsi Produk:** SoundPass adalah aplikasi web pemesanan tiket konser dan festival musik yang memungkinkan penggemar musik menelusuri jadwal konser, melihat daftar penampil (*lineup*), memilih kategori tiket/zona menonton (*Festival Standing*, *VIP Seated*, *VVIP*), mengisi data identitas pemegang tiket (termasuk NIK/Nomor Identitas untuk mencegah calo), dan menerbitkan *e-ticket* ber-QR code untuk penukaran *wristband* di *venue*.
* **Target Audiens:**
  * *Penonton Konser (Guest & Registered Users):* Pengunjung yang membeli tiket konser untuk individu maupun rombongan (maksimal 4 tiket per transaksi/NIK).
  * *Petugas Gate / Penukaran Wristband (Validator):* Memverifikasi kode *booking* dan identitas pemesan pada *e-ticket*.
* **Batas Fungsionalitas (In-Scope & Out-of-Scope):**
  * **In-Scope:** Katalog konser & festival, filter berdasarkan genre musik/kota/bulan, rincian konser (*lineup*, *venue map*, syarat & ketentuan), pemilihan kuota per kategori tiket (*Ticket Tiers*), penguncian kuota atomik (*pessimistic locking*), penerbitan kode *booking* unik, dan tampilan *e-ticket* siap cetak/unduh PDF.
  * **Out-of-Scope:** Integrasi *payment gateway* pihak ketiga secara *live* (disimulasikan dengan status konfirmasi instan/sandbox), sistem *waiting room* antrean virtual berskala jutaan *concurrent users*, serta penjualan *merchandise* resmi.

---

## 2. Spesifikasi 4 Halaman Utama Terintegrasi

### Halaman 1: Katalog Konser (`GET /` atau `GET /concerts`)
* **Daftar Event Konser:** Menampilkan kartu konser (poster resmi, judul konser/tur, nama *headliner*/artis utama, tanggal & jam *open gate*, nama *venue* & kota, serta harga tiket termurah/"Mulai dari").
* **Filter & Kategori:**
  * Filter kategori genre musik (`Pop`, `Rock`, `Indie`, `EDM`, `Jazz`, `K-Pop`).
  * Filter kota penyelenggaraan (`Jakarta`, `Bandung`, `Surabaya`, `Bali`, `Balikpapan`).
  * Filter rentang waktu (`Minggu Ini`, `Bulan Ini`, `Mendatang`).
* **Search Bar:** Pencarian berbasis *query string* (`?search=nama_artis_atau_konser`) yang memfilter judul konser, nama artis penampil, dan nama *venue*.
* **Pagination:** Menggunakan Laravel Length-Aware Pagination (`$concerts->withQueryString()->paginate(12)`) agar parameter filter tetap terjaga saat berpindah halaman.
* **Status Ketersediaan Tiket:** Indikator visual otomatis berdasarkan total sisa kuota dari seluruh kategori tiket konser tersebut:
  * `Tersedia` (Hijau): Sisa kuota > 20%.
  * `Segera Habis` (Kuning/Amber): Sisa kuota antara 1% hingga 20%.
  * `Sold Out` (Merah — tombol dinonaktifkan): Sisa kuota 0.

### Halaman 2: Rincian Konser (`GET /concerts/{concert:slug}`)
* **Informasi Lengkap Konser:** Deskripsi acara, daftar artis penampil (*Lineup*), tanggal acara, jam *open gate* & mulai pertunjukan, alamat lengkap *venue*, serta gambar denah area panggung (*Stage & Seat Map Layout*).
* **Daftar Kategori Tiket & Kuota:** Tabel/daftar kategori tiket (misal: `Festival A - Standing`, `Tribune - Free Seating`, `VIP - Numbered Seat`) lengkap dengan harga, fasilitas masing-masing kategori, dan sisa kuota aktual (`remaining_quota`).
* **Syarat & Ketentuan:** Daftar regulasi konser (wajib membawa KTP/Paspor asli sesuai data *e-ticket* saat penukaran *wristband*, larangan membawa kamera profesional/senjata tajam, tiket yang sudah dibeli tidak dapat di-*refund*).
* **Call-to-Action (CTA):** Tombol utama **"Beli Tiket"** yang mengarahkan pengguna ke `route('bookings.create', $concert)` (nonaktif jika seluruh kategori tiket *Sold Out*).

### Halaman 3: Pemesanan Interaktif & Formulir (`GET /concerts/{concert:slug}/book` & `POST /bookings`)
* **Ticket Tier & Quantity Selector Interaktif (Alpine.js + Blade):**
  * Pengguna dapat memilih jumlah tiket (`0` hingga maksimal `4` tiket) pada kategori tiket yang tersedia menggunakan tombol stepper (`-` / `+`).
  * Jika kategori bertipe *Seated* bernomor, sistem otomatis mengalokasikan nomor kursi berurutan atau menampilkan pilihan blok kursi.
  * Kalkulasi total harga, pajak hiburan daerah (*Entertainment Tax* 10%), dan biaya layanan (*Admin Fee*) diperbarui secara instan di sisi klien setiap kali kuantitas berubah.
* **Formulir Identitas Pemesan:**
  * `customer_name` (Nama Lengkap sesuai KTP/Paspor, wajib).
  * `identity_number` (NIK 16 digit / Nomor Paspor untuk validasi *wristband*, wajib).
  * `customer_email` (Alamat email untuk pengiriman salinan *e-ticket*, wajib).
  * `customer_phone` (Nomor WhatsApp/telepon aktif, wajib).
* **Validasi Client-Side & Server-Side:**
  * *Client-side:* Alpine.js memblokir tombol *submit* jika total tiket = 0, melebihi batas maksimal 4 tiket per transaksi, atau format NIK/email/nomor HP tidak valid.
  * *Server-side:* Ditangani oleh `StoreBookingRequest` untuk memvalidasi eksistensi konser, validitas kategori tiket, batas kuota per transaksi, dan format NIK 16 digit.

### Halaman 4: Ringkasan & E-Ticket (`GET /bookings/{booking:booking_code}`)
* **Ringkasan Detail Pesanan:** Judul konser, *venue*, tanggal & waktu *open gate*, daftar kategori & jumlah tiket yang dibeli (beserta nomor kursi/akses gerbang), serta identitas pemesan (Nama & NIK tersensor sebagian untuk privasi, misal: `327301******0004`).
* **Rincian Biaya:**
  * Subtotal Harga Tiket (`jumlah_tiket × harga_kategori`).
  * Pajak Hiburan 10% (`10% × Subtotal`).
  * Biaya Layanan / *Platform Fee* (`Rp 10.000 × jumlah_tiket`).
  * Total Pembayaran (`Subtotal + Pajak + Biaya Layanan`).
* **Kode Unik Transaksi & QR Code:** Kode *booking* alfanumerik 10 karakter (contoh: `SND-92K8M4`) disertai SVG QR Code untuk pemindaian saat penukaran *wristband*.
* **Tombol Aksi:** Tombol **"Cetak / Simpan E-Ticket PDF"** yang memicu `window.print()` dengan stylesheet khusus `@media print`.

---

## 3. Data Flow & Database Blueprint

### Alur Data (Data Flow)
1. **Read Flow (Katalog & Detail):** `HTTP Request` $\rightarrow$ `ConcertController` (Eager Loading `ticketTiers` dengan `withSum` atau kalkulasi sisa kuota) $\rightarrow$ `Blade View`.
2. **Write Flow (Pemesanan):** `POST /bookings` $\rightarrow$ `StoreBookingRequest` $\rightarrow$ `CreateBookingAction` $\rightarrow$ `DB::transaction()` dengan `lockForUpdate()` pada baris `ticket_tiers` $\rightarrow$ Decrement `remaining_quota` $\rightarrow$ Insert ke `bookings` & `booking_details` $\rightarrow$ Simpan `booking_code` ke Session $\rightarrow$ Redirect ke `route('bookings.show', $booking)`.

### Skema Entitas & Relasi Inti (MySQL)

```
+---------------+       +-----------------+       +------------------+
|     users     | 1   * |    bookings     | *   1 |     concerts     |
+---------------+-------+-----------------+-------+------------------+
| id (PK)       |       | id (PK)         |       | id (PK)          |
| name          |       | booking_code(UQ)|       | title            |
| email         |       | user_id (FK,NUL)|       | slug (UNIQUE)    |
| password      |       | concert_id (FK) |       | headliner        |
+---------------+       | customer_name   |       | genre            |
                        | identity_number |       | city             |
                        | customer_email  |       | venue_name       |
                        | customer_phone  |       | event_date       |
                        | subtotal_amount |       | open_gate_time   |
                        | tax_amount      |       | poster_url       |
                        | admin_fee       |       | stage_map_url    |
                        | total_amount    |       | description      |
                        | status (ENUM)   |       +--------+---------+
                        | created_at      |                | 1
                        +--------+--------+                |
                                 | 1                       | *
                                 |                +--------+---------+
                                 | *              |   ticket_tiers   |
                        +--------v--------+       +------------------+
                        | booking_details | *   1 | id (PK)          |
                        +-----------------+-------+ concert_id (FK)  |
                        | id (PK)         |       | name (VIP/Fest)  |
                        | booking_id (FK) |       | description      |
                        | ticket_tier_id  |       | price            |
                        | quantity        |       | total_quota      |
                        | unit_price      |       | remaining_quota  |
                        | subtotal        |       +------------------+
                        +-----------------+       *CHECK(remaining_quota >= 0)
```

* **Kunci Integritas Database:** Kolom `remaining_quota` pada tabel `ticket_tiers` menggunakan tipe data `UNSIGNED INT` serta constraint `CHECK (remaining_quota >= 0)` di MySQL agar jumlah kuota tersisa tidak pernah bisa bernilai negatif dalam kondisi konkuren apa pun.

---

## 4. Validasi & Error Handling

1. **Form Request Validation (`StoreBookingRequest`):**
   * `concert_id`: `['required', 'integer', 'exists:concerts,id']`
   * `items`: `['required', 'array', 'min:1']`
   * `items.*.ticket_tier_id`: `['required', 'integer', 'distinct', 'exists:ticket_tiers,id']`
   * `items.*.quantity`: `['required', 'integer', 'min:1', 'max:4']`
   * `customer_name`: `['required', 'string', 'min:3', 'max:100']`
   * `identity_number`: `['required', 'string', 'digits:16']` (Validasi 16 digit NIK KTP)
   * `customer_email`: `['required', 'email:rfc,dns', 'max:150']`
   * `customer_phone`: `['required', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,10}$/']`
2. **Proteksi Overbooking / Overselling (Race Condition Handling):**
   * Saat dua pembeli memesan sisa 1 tiket terakhir kategori `VIP` pada waktu yang sama:
   * Sistem membungkus proses di dalam `DB::transaction()`.
   * Mengunci baris kategori tiket terkait menggunakan `TicketTier::whereIn('id', $tierIds)->lockForUpdate()->get()`.
   * Memverifikasi `remaining_quota >= $requestedQuantity` di dalam blok transaksi yang terkunci.
   * Jika kuota tidak mencukupi, sistem melempar `QuotaExceededException`, membatalkan transaksi (*rollback*), dan mengembalikan pengguna ke halaman pemilihan tiket dengan pesan error spesifik kategori mana yang kuotanya baru saja habis.
3. **Invalid CSRF Token Handling (`419 Page Expired`):**
   * Dikonfigurasi pada `bootstrap/app.php` untuk menangkap `TokenMismatchException`.
   * Mengarahkan pengguna kembali (`redirect()->back()->withInput($request->except('_token'))`) dengan flash message: *"Sesi pemesanan Anda telah kedaluwarsa. Silakan periksa kembali pilihan kategori tiket Anda dan kirim ulang."*