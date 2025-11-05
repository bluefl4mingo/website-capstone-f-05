<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\AudioFileController;
use App\Http\Controllers\Admin\NfcTagController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DownloadController;
use App\Http\Controllers\Api\NfcMappingController;

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

        // Items management
        Route::get('/items', [ItemController::class, 'index'])->name('items.index');
        Route::post('/items', [ItemController::class, 'store'])->name('items.store');
        Route::patch('/items/{item}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');

        // Audio files management
        Route::get('/audio', [AudioFileController::class, 'index'])->name('audio.index');
        Route::post('/audio', [AudioFileController::class, 'store'])->name('audio.store');
        Route::get('/audio/{audioFile}/download', [AudioFileController::class, 'download'])->name('audio.download'); 
        Route::patch('/audio/{audioFile}', [AudioFileController::class, 'update'])->name('audio.update');
        Route::delete('/audio/{audioFile}', [AudioFileController::class, 'destroy'])->name('audio.destroy');

        // NFC tags management
        Route::get('/nfc', [NfcTagController::class, 'index'])->name('nfc.index');
        Route::post('/nfc', [NfcTagController::class, 'store'])->name('nfc.store');
        Route::patch('/nfc/{nfcTag}', [NfcTagController::class, 'update'])->name('nfc.update');
        Route::delete('/nfc/{nfcTag}', [NfcTagController::class, 'destroy'])->name('nfc.destroy');

        // Activity logs
        Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
        Route::delete('/logs/purge', [ActivityLogController::class, 'purge'])->name('logs.purge');

        // Users management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Devices
        Route::view('/devices', 'admin.devices.index')->name('devices.index');

        // Downloads & Exports
        Route::get('/export/nfc-mappings', [NfcMappingController::class, 'exportJson'])->name('export.nfc.mappings');
        Route::get('/export/audio-all', [DownloadController::class, 'downloadAllAudio'])->name('export.audio.all');
        Route::get('/export/audio-stats', [DownloadController::class, 'getDownloadStats'])->name('export.audio.stats');
    });
});

require __DIR__.'/auth.php';
