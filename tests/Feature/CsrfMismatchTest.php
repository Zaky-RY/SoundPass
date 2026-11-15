<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CsrfMismatchTest extends TestCase
{
    public function test_token_mismatch_exception_redirects_back_with_input_and_warning_flash(): void
    {
        Route::post('/test-csrf-exception', function () {
            throw new TokenMismatchException('CSRF token mismatch.');
        });

        $response = $this->from('/previous-page')->post('/test-csrf-exception', [
            '_token' => 'invalid-token',
            'concert_id' => 1,
            'customer_name' => 'John Doe',
        ]);

        $response->assertRedirect('/previous-page');
        $response->assertSessionHas('warning', 'Sesi pemesanan Anda telah kedaluwarsa. Silakan periksa kembali pilihan kategori tiket Anda dan kirim ulang.');
        $response->assertSessionHasInput('concert_id', 1);
        $response->assertSessionHasInput('customer_name', 'John Doe');
        $this->assertFalse(session()->hasOldInput('_token'));
    }
}
