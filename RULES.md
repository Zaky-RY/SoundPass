# ENGINE & CODE RULES (STRICT: ANTI-AI SLOP & DRY)
**Proyek:** SoundPass — Sistem Pemesanan Tiket Konser & Festival Musik Berbasis Web  
**Standar:** Laravel 11.x, PHP 8.3+ Strict Types  

---

## 1. Anti-AI Slop Standards
1. **No Phantom Helper / Wrapper Functions:** Jangan membuat wrapper class atau utility function yang tidak perlu jika Laravel atau PHP sudah menyediakannya secara native (gunakan `Illuminate\Support\Number::currency()` atau custom Blade directive `@rupiah`).
2. **Concrete Implementation Only:** Dilarang keras menggunakan komentar placeholder seperti `// implement controller logic here`, `// tambahkan kode view di sini`, atau stub kosong. Seluruh kode wajib ditulis lengkap, valid secara sintaksis, dan siap dieksekusi.
3. **No Fluff & No Redundant Comments:** Dilarang menulis komentar yang hanya mengulang baris kode (contoh dilarang: `// save booking to database: $booking->save();`). Komentar hanya diperbolehkan untuk menjelaskan logika bisnis yang kompleks (seperti database transaction lock).
4. **Strict Laravel Idiomatic Standards:**
   * Gunakan Form Request khusus (`StoreBookingRequest`) untuk validasi, jangan menumpuk aturan validasi di dalam controller.
   * Gunakan Eloquent Relationship (`hasMany`, `belongsTo`) dan Eager Loading (`with()`) untuk mencegah masalah N+1 Query.

---

## 2. DRY (Do Not Repeat Yourself) Guidelines
1. **Single Source of Truth (SSOT):**
   * Status pemesanan (`PENDING`, `CONFIRMED`, `CANCELLED`) wajib dikelola melalui PHP 8.1+ Backed Enum (`App\Enums\BookingStatus`), bukan string bebas.
   * Skema layout Blade menggunakan template inheritance (`layouts/app.blade.php`) dan reusable Blade components.
2. **Atomic & Reusable Services/Actions:**
   * Logika pengurangan kuota kategori tiket dan kalkulasi pajak/biaya admin wajib dibungkus dalam `DB::transaction()` terpusat di dalam Action Class (`CreateBookingAction`) untuk mencegah duplikasi logika.
3. **Route Management:**
   * Gunakan Route Model Binding eksplisit (`{concert:slug}`, `{booking:booking_code}`) dan penamaan rute yang konsisten (`route('concerts.show')`, `route('bookings.show')`).

---

## 3. Referensi Implementasi Kode Standar Produksi

### A. Enum Status Pemesanan (`app/Enums/BookingStatus.php`)
```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::CONFIRMED => 'Terkonfirmasi (E-Ticket Aktif)',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-100 text-amber-800 border-amber-200',
            self::CONFIRMED => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::CANCELLED => 'bg-rose-100 text-rose-800 border-rose-200',
        };
    }
}
```

### B. Form Request Validation (`app/Http/Requests/StoreBookingRequest.php`)
```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $concertId = $this->input('concert_id');

        return [
            'concert_id' => ['required', 'integer', 'exists:concerts,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ticket_tier_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('ticket_tiers', 'id')->where('concert_id', $concertId),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:4'],
            'customer_name' => ['required', 'string', 'min:3', 'max:100'],
            'identity_number' => ['required', 'string', 'digits:16'],
            'customer_email' => ['required', 'email:rfc', 'max:150'],
            'customer_phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,10}$/'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $totalTickets = collect($this->input('items', []))->sum('quantity');

                if ($totalTickets > 4) {
                    $validator->errors()->add(
                        'items',
                        'Maksimal pembelian adalah 4 tiket dalam satu transaksi untuk setiap NIK.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Anda wajib memilih minimal 1 kategori tiket konser.',
            'items.*.ticket_tier_id.exists' => 'Kategori tiket tidak valid untuk konser ini.',
            'identity_number.digits' => 'NIK wajib terdiri dari tepat 16 digit angka sesuai KTP.',
            'customer_phone.regex' => 'Format nomor telepon harus menggunakan nomor seluler Indonesia yang valid.',
        ];
    }
}
```

### C. Atomic Booking Action (`app/Actions/CreateBookingAction.php`)
```php
<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Exceptions\QuotaExceededException;
use App\Models\Booking;
use App\Models\BookingDetail;
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
            // remaining_quota berjalan secara atomik tanpa race condition.
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
```

### D. Controller & Session Guard (`app/Http/Controllers/BookingController.php`)
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateBookingAction;
use App\Exceptions\QuotaExceededException;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Concert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class BookingController extends Controller
{
    public function create(Concert $concert): View|RedirectResponse
    {
        $concert->load('ticketTiers');

        if ($concert->event_date->isPast() || $concert->ticketTiers->sum('remaining_quota') <= 0) {
            return redirect()
                ->route('concerts.show', $concert)
                ->with('error', 'Tiket untuk konser ini sudah habis atau penjualan telah ditutup.');
        }

        return view('bookings.create', [
            'concert' => $concert,
        ]);
    }

    public function store(StoreBookingRequest $request, CreateBookingAction $createBooking): RedirectResponse
    {
        try {
            $booking = $createBooking->execute([
                ...$request->validated(),
                'user_id' => Auth::id(),
            ]);

            $authorized = $request->session()->get('authorized_bookings', []);
            $request->session()->put('authorized_bookings', [...$authorized, $booking->booking_code]);

            return redirect()
                ->route('bookings.show', $booking)
                ->with('success', 'E-Ticket konser berhasil diterbitkan. Tunjukkan kode booking saat penukaran wristband.');
        } catch (QuotaExceededException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    public function show(Request $request, Booking $booking): View
    {
        $authorizedCodes = $request->session()->get('authorized_bookings', []);
        $isOwner = Auth::check() && $booking->user_id === Auth::id();
        $isSessionAuthorized = in_array($booking->booking_code, $authorizedCodes, true);

        abort_unless($isOwner || $isSessionAuthorized, 403, 'Anda tidak memiliki akses ke e-ticket ini.');

        $booking->load(['concert', 'details.ticketTier']);

        return view('bookings.show', [
            'booking' => $booking,
        ]);
    }
}
```

### E. Definisi Routes (`routes/web.php`)
```php
<?php

declare(strict_types=1);

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ConcertController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ConcertController::class, 'index'])->name('concerts.index');
Route::get('/concerts/{concert:slug}', [ConcertController::class, 'show'])->name('concerts.show');

Route::get('/concerts/{concert:slug}/book', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/bookings/{booking:booking_code}', [BookingController::class, 'show'])->name('bookings.show');
```