<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PasswordController;

Route::get('/', fn () => view('welcome'))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))->name('dashboard');
    
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    // Admin area
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

        Route::view('/items', 'admin.items.index')->name('items.index');
        // Route::get('/items', [ItemController::class, 'index'])->name('items.index');
        // Route::post('/items', [ItemController::class, 'store'])->name('items.store');

        Route::view('/audio', 'admin.audio.index')->name('audio.index');
        Route::view('/nfc-tags', 'admin.nfc.index')->name('nfc.index');
        Route::view('/devices', 'admin.devices.index')->name('devices.index');
        Route::view('/activity-logs', 'admin.logs.index')->name('logs.index');
        Route::view('/users', 'admin.users.index')->name('users.index');
    });
});

require __DIR__.'/auth.php';
