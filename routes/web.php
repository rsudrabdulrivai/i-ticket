<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Berikan dua nama sekaligus (dashboard dan home) agar Laravel tidak bingung
    Route::view('/', 'dashboard')->name('dashboard');

    // Tambahkan ini sebagai alias jika sistem masih mencari nama 'home'
    Route::get('/home', function () {
        return redirect('/');
    })->name('home');

    Route::view('/it-monitor', 'it-monitor')
        ->middleware('can:access-it-monitor')
        ->name('it-monitor');

    Route::view('/it-staff', 'it-staff')
        ->middleware('can:access-it-staff')
        ->name('it-staff');

    Route::view('/inventory', 'inventory')
        ->middleware('can:access-inventory')
        ->name('inventory');
});

require __DIR__ . '/settings.php';
