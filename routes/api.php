<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AudioController;
use App\Http\Controllers\Api\NfcMappingController;

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
    
    Route::get('/nfc-mappings/csv', [NfcMappingController::class, 'exportCsv'])->name('nfc.mappings.csv');
    Route::get('/nfc-mappings', [NfcMappingController::class, 'getMappings'])->name('nfc.mappings.json');
});