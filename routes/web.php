<?php

declare(strict_types=1);

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ConcertController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ConcertController::class, 'index'])->name('concerts.index');
Route::get('/concerts', [ConcertController::class, 'index'])->name('concerts.catalog');
Route::get('/concerts/{concert:slug}', [ConcertController::class, 'show'])->name('concerts.show');

Route::get('/concerts/{concert:slug}/book', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/check-booking', [BookingController::class, 'checkForm'])->name('bookings.check');
Route::post('/check-booking', [BookingController::class, 'verifyCheck'])->name('bookings.verify');
Route::get('/bookings/{booking:booking_code}', [BookingController::class, 'show'])->name('bookings.show');
