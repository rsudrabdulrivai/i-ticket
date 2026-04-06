<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::view('/it-monitor', 'it-monitor')
    ->middleware(['auth', 'can:access-it-monitor']) // Kita tambahkan "can:access-it-monitor"
    ->name('it-monitor');

require __DIR__ . '/settings.php';
