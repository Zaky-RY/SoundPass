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
            'customer_phone.regex' => 'Format nomor telepon harus menggunakan nomor seluler Indonesia yang valid (contoh: 081234567890).',
        ];
    }
}
