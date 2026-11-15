# DESIGN & USER EXPERIENCE (UX) SPECIFICATION
**Proyek:** SoundPass — Sistem Pemesanan Tiket Konser & Festival Musik Berbasis Web  
**Framework UI:** Blade Templates, Tailwind CSS 3.x, Alpine.js 3.x  

---

## 1. Navigasi & Tata Kelola Alur (Flow Architecture)

### Diagram Alur Navigasi & State Guard

```
[Halaman 1: Katalog Konser]
       │ (Klik Event Konser / Filter Genre & Kota)
       ▼
[Halaman 2: Rincian Konser] ──(Semua Tier Sold Out)──► [Tombol Disabled: "Sold Out"]
       │ (Klik "Beli Tiket")
       ▼
[Halaman 3: Pilih Kategori & Form] ◄──(Error Validasi / Kuota Habis)──┐
       │ (Submit Valid & Atomic DB Lock Sukses)                       │
       ▼                                                              │
[Proses: CreateBookingAction] ───(Gagal / Race Condition)─────────────┘
       │ (Sukses: Simpan Kode Booking ke Session)
       ▼
[Halaman 4: Ringkasan & E-Ticket] ◄──(Guard: Cek Session / Auth Owner)
                                           └─(Akses Ilegal)──► [Abort 403 / Redirect]
```

### Mekanisme Navigasi & Keamanan State
* **Breadcrumbs:** Ditempatkan di bawah Navbar pada Halaman 2, 3, dan 4 menggunakan struktur hirarkis:  
  `Beranda > [Judul Konser] > Pilih Tiket > E-Ticket`
* **Flash Messages (Alert System):** Komponen global di atas area konten utama yang merespons `session('success')`, `session('error')`, dan `session('warning')` dengan tombol tutup (*dismissible*) menggunakan Alpine.js (`x-data="{ show: true }" x-show="show"`).
* **State Redirection Guard:**
  * Jika pengguna mengakses URL Halaman 3 (`/concerts/{slug}/book`) untuk konser yang sudah selesai (`event_date < today()`) atau seluruh kuota tiketnya sudah habis (`remaining_quota === 0`), sistem otomatis mengarahkan ke Halaman 2 dengan flash error.
  * Halaman 4 (`/bookings/{booking_code}`) dilindungi oleh pemeriksaan akses: hanya dapat diakses jika `booking_code` tersimpan di `session('authorized_bookings', [])` milik sesi browser pemesan saat ini, atau dimiliki oleh `auth()->id()` yang sedang login. Akses langsung tanpa otorisasi akan mengembalikan `abort(403)`.

---

## 2. Komponen Antarmuka (UI Component Hierarchy)

### Standar Layout Utama (`resources/views/layouts/app.blade.php`)
* **Navbar (`<header>`):** Fixed/sticky top bar berisi logo brand **SoundPass** (kiri), tautan navigasi utama `Jelajah Konser` dan `Cek E-Ticket` (tengah), serta status sesi pengguna (kanan).
* **Content Area (`<main>`):** Container terpusat (`max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8`) sebagai slot utama halaman (`@yield('content')`).
* **Footer (`<footer>`):** Informasi hak cipta, panduan penukaran *wristband*, dan layanan pelanggan (disembunyikan saat mode cetak menggunakan kelas `print:hidden`).
* **Modal / Offcanvas (Venue Map Preview & Mobile Sticky Bar):**
  * Modal *lightbox* untuk memperbesar gambar *Stage & Seat Map* pada Halaman 2 dan Halaman 3.
  * Pada layar mobile (`< 768px`), ringkasan jumlah tiket dan total harga menempel di bagian bawah layar (*sticky bottom bar*) agar tombol konfirmasi mudah dijangkau ibu jari.

### Indikator Status Kategori Tiket Interaktif

| Status Tier Tiket | Visual Warna (Tailwind CSS) | Interaktivitas Stepper (`-` / `+`) | Atribut Aksesibilitas |
| :--- | :--- | :--- | :--- |
| **Available** | `bg-white border-slate-200 hover:border-indigo-500` | Tombol `-` dan `+` aktif sesuai batas kuota (maks 4) | `aria-live="polite"` pada counter jumlah |
| **Selected (Qty > 0)** | `bg-indigo-50/50 border-indigo-600 ring-1 ring-indigo-600` | Tombol `+` nonaktif jika mencapai batas maksimal | `aria-label="Tambah jumlah tiket [Nama Tier]"` |
| **Sold Out (Quota = 0)** | `bg-slate-100 border-slate-200 opacity-60` | Stepper disembunyikan, diganti lencana `SOLD OUT` | `aria-disabled="true"` |

### Mobile-First Responsiveness & Aksesibilitas
* **Touch-Friendly Controls:** Tombol stepper kuantitas (`-` dan `+`) dirancang dengan ukuran minimal `40px × 40px` (`w-10 h-10`) agar nyaman ditekan pada perangkat layar sentuh tanpa salah tekan.
* **Semantic HTML & Keyboard Focus:**
  * Kartu kategori tiket menggunakan struktur `<fieldset>` dan kontrol `<button type="button">` untuk pengubah kuantitas dengan input tersembunyi yang tersinkronisasi.
  * Fokus keyboard diperjelas dengan kelas `focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1`.
  * Kontras warna teks memenuhi rasio minimal WCAG AA (`4.5:1`).