<?php

use App\Http\Controllers\SpkController;
use Illuminate\Support\Facades\Route;

// Redirect root & URL lama ke alur Milestone SPK (kompatibel dengan route:cache)
Route::redirect('/', '/spk/kriteria');
Route::redirect('/kriteria', '/spk/kriteria');
Route::redirect('/ahp', '/spk/ahp');
Route::redirect('/alternatif', '/spk/alternatif');
Route::redirect('/perhitungan', '/spk/perhitungan');
Route::redirect('/hasil', '/spk/hasil');

Route::prefix('spk')->name('spk.')->group(function () {
    // Reset seluruh sistem ke kondisi awal (Milestone 01)
    Route::post('/reset', [SpkController::class, 'resetSistem'])->name('reset');

    // Milestone 01: Kriteria (Selalu Terbuka)
    Route::get('/kriteria', [SpkController::class, 'kriteria'])->name('kriteria');
    Route::post('/kriteria', [SpkController::class, 'storeKriteria'])->name('kriteria.store');
    Route::delete('/kriteria/{kriteria}', [SpkController::class, 'destroyKriteria'])->name('kriteria.destroy');

    // Milestone 02: Pembobotan AHP
    Route::middleware('milestone:2')->group(function () {
        Route::get('/ahp', [SpkController::class, 'ahp'])->name('ahp');
        Route::post('/ahp/hitung', [SpkController::class, 'hitungAhp'])->name('ahp.hitung');
    });

    // Milestone 03: Data Alternatif & Matriks Penilaian
    Route::middleware('milestone:3')->group(function () {
        Route::get('/alternatif', [SpkController::class, 'alternatif'])->name('alternatif');
        Route::post('/alternatif/import', [SpkController::class, 'importExcel'])->name('alternatif.import');
        Route::get('/alternatif/template', [SpkController::class, 'downloadTemplate'])->name('alternatif.template');
        Route::post('/alternatif/validasi', [SpkController::class, 'validasiAlternatif'])->name('alternatif.validasi');
    });

    // Milestone 04: Proses Perhitungan & Normalisasi SAW
    Route::middleware('milestone:4')->group(function () {
        Route::get('/perhitungan', [SpkController::class, 'perhitungan'])->name('perhitungan');
        Route::post('/perhitungan/finalisasi', [SpkController::class, 'finalisasiSaw'])->name('perhitungan.finalisasi');
    });

    // Milestone 05: Hasil Perangkingan, Evaluasi & Analisis Sensitivitas
    Route::middleware('milestone:5')->group(function () {
        Route::get('/hasil', [SpkController::class, 'hasil'])->name('hasil');
        Route::get('/hasil/export', [SpkController::class, 'exportExcel'])->name('hasil.export');
    });
});