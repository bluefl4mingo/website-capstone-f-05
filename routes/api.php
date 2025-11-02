<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AudioController;
use App\Http\Controllers\Api\NfcMappingController;
use App\Http\Controllers\Admin\DownloadController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Existing audio routes with auth
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/audios', [AudioController::class, 'index'])->name('audios.index');
        Route::get('/audios/{audio}', [AudioController::class, 'show'])->name('audios.show');
        Route::get('/audios/{audio}/download', [AudioController::class, 'download'])->name('audios.download');
    });
    
    // Public exports
    Route::get('/nfc-mappings/csv', [NfcMappingController::class, 'exportCsv'])->name('nfc.mappings.csv');
    Route::get('/nfc-mappings', [NfcMappingController::class, 'getMappings'])->name('nfc.mappings.json');
    
    // Audio download routes (can be public or protected)
    Route::get('/audio/download-all', [DownloadController::class, 'downloadAllAudio'])->name('audio.download.all');
    Route::get('/audio/stats', [DownloadController::class, 'getDownloadStats'])->name('audio.stats');
});