<?php

use App\Http\Controllers\SpkController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SpkController::class, 'dashboard'])->name('dashboard');

Route::get('/kriteria', [SpkController::class, 'kriteriaIndex'])->name('kriteria.index');
Route::post('/kriteria', [SpkController::class, 'kriteriaStore'])->name('kriteria.store');
Route::put('/kriteria/{kriteria}', [SpkController::class, 'kriteriaUpdate'])->name('kriteria.update');
Route::delete('/kriteria/{kriteria}', [SpkController::class, 'kriteriaDestroy'])->name('kriteria.destroy');

Route::get('/ahp', [SpkController::class, 'ahpIndex'])->name('ahp.index');
Route::post('/ahp', [SpkController::class, 'ahpStore'])->name('ahp.store');

Route::get('/alternatif', [SpkController::class, 'alternatifIndex'])->name('alternatif.index');
Route::post('/alternatif', [SpkController::class, 'alternatifStore'])->name('alternatif.store');
Route::post('/alternatif/import', [SpkController::class, 'alternatifImportCsv'])->name('alternatif.import');
Route::delete('/alternatif/{alternatif}', [SpkController::class, 'alternatifDestroy'])->name('alternatif.destroy');

Route::get('/penilaian', [SpkController::class, 'penilaianIndex'])->name('penilaian.index');
Route::post('/penilaian', [SpkController::class, 'penilaianStore'])->name('penilaian.store');

Route::get('/perhitungan', [SpkController::class, 'perhitunganSaw'])->name('perhitungan.index');
Route::get('/sensitivitas', [SpkController::class, 'sensitivitas'])->name('sensitivitas.index');