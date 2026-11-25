<?php

namespace App\Services;

use App\Models\AhpComparison;
use App\Models\Alternatif;
use App\Models\Kriteria;
use App\Models\Penilaian;

class SpkAhpSawService
{
    // Tabel Random Index (RI) Saaty untuk ukuran matriks n = 1..10
    private array $randomIndex = [
        1 => 0.00, 2 => 0.00, 3 => 0.58, 4 => 0.90, 5 => 1.12,
        6 => 1.24, 7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49,
    ];

    /**
     * Menghitung Bobot Kriteria & Uji Konsistensi AHP secara bertahap
     */
    public function hitungAhp(bool $simpanBobot = true): array
    {
        $kriterias = Kriteria::orderBy('id')->get();
        $n = $kriterias->count();
        if ($n === 0) {
            return [];
        }

        $comparisons = AhpComparison::all()->keyBy(fn($item) => $item->kriteria_1_id . '-' . $item->kriteria_2_id);

        // 1. Susun Matriks Perbandingan Berpasangan (n x n)
        $matriks = [];
        $jumlahKolom = array_fill(0, $n, 0.0);

        foreach ($kriterias as $i => $k1) {
            foreach ($kriterias as $j => $k2) {
                if ($i === $j) {
                    $val = 1.0;
                } elseif ($comparisons->has($k1->id . '-' . $k2->id)) {
                    $val = (float) $comparisons[$k1->id . '-' . $k2->id]->nilai;
                } elseif ($comparisons->has($k2->id . '-' . $k1->id)) {
                    $inv = (float) $comparisons[$k2->id . '-' . $k1->id]->nilai;
                    $val = $inv > 0 ? 1.0 / $inv : 1.0;
                } else {
                    $val = 1.0;
                }

                $matriks[$i][$j] = $val;
                $jumlahKolom[$j] += $val;
            }
        }

        // 2. Normalisasi Matriks & Hitung Priority Vector (Bobot)
        $normalisasi = [];
        $bobot = [];

        foreach ($kriterias as $i => $k1) {
            $sumRow = 0.0;
            foreach ($kriterias as $j => $k2) {
                $norm = $jumlahKolom[$j] > 0 ? $matriks[$i][$j] / $jumlahKolom[$j] : 0;
                $normalisasi[$i][$j] = $norm;
                $sumRow += $norm;
            }
            $bobot[$i] = $sumRow / $n;

            if ($simpanBobot) {
                $k1->update(['bobot' => round($bobot[$i], 6)]);
            }
        }

        // 3. Hitung Eigen Value Maksimum (Lambda Max), CI, dan CR
        $aw = [];
        $lambdaVector = [];
        foreach ($kriterias as $i => $k1) {
            $rowVal = 0.0;
            foreach ($kriterias as $j => $k2) {
                $rowVal += $matriks[$i][$j] * $bobot[$j];
            }
            $aw[$i] = $rowVal;
            $lambdaVector[$i] = $bobot[$i] > 0 ? $rowVal / $bobot[$i] : 0;
        }

        $lambdaMax = array_sum($lambdaVector) / $n;
        $ci = $n > 1 ? ($lambdaMax - $n) / ($n - 1) : 0.0;
        $ri = $this->randomIndex[$n] ?? 1.12;
        $cr = $ri > 0 ? $ci / $ri : 0.0;

        return [
            'kriterias'    => $kriterias,
            'matriks'      => $matriks,
            'jumlah_kolom' => $jumlahKolom,
            'normalisasi'  => $normalisasi,
            'bobot'        => $bobot,
            'aw'           => $aw,
            'lambda_max'   => $lambdaMax,
            'ci'           => $ci,
            'ri'           => $ri,
            'cr'           => $cr,
            'konsisten'    => $cr <= 0.10,
        ];
    }

    /**
     * Menghitung Tahapan SAW (Matriks X -> Normalisasi R -> Terbobot -> Peringkat V)
     * Mendukung $overrideWeights [kriteria_id => bobot] untuk Analisis Sensitivitas
     */
    public function hitungSaw(?array $overrideWeights = null): array
    {
        $kriterias = Kriteria::orderBy('id')->get();
        $alternatifs = Alternatif::orderBy('id')->get();
        $penilaians = Penilaian::all()->keyBy(fn($p) => $p->alternatif_id . '-' . $p->kriteria_id);

        // Siapkan bobot aktif (dari DB atau hasil simulasi sensitivitas)
        $bobotAktif = [];
        foreach ($kriterias as $k) {
            $bobotAktif[$k->id] = $overrideWeights[$k->id] ?? (float) $k->bobot;
        }

        // 1. Matriks Keputusan (X) & Nilai Max/Min tiap Kriteria
        $matriksX = [];
        $maxKriteria = [];
        $minKriteria = [];

        foreach ($kriterias as $k) {
            $kolomNilai = [];
            foreach ($alternatifs as $alt) {
                $val = (float) ($penilaians[$alt->id . '-' . $k->id]->nilai ?? 0);
                $matriksX[$alt->id][$k->id] = $val;
                $kolomNilai[] = $val;
            }
            $maxKriteria[$k->id] = !empty($kolomNilai) ? max($kolomNilai) : 0;
            $minKriteria[$k->id] = !empty($kolomNilai) ? min($kolomNilai) : 0;
        }

        // 2. Matriks Ternormalisasi (R) & Perhitungan Preferensi (V)
        $matriksR = [];
        $matriksTerbobot = [];
        $hasil = [];

        foreach ($alternatifs as $alt) {
            $totalV = 0.0;
            foreach ($kriterias as $k) {
                $x = $matriksX[$alt->id][$k->id];
                if ($k->atribut === 'benefit') {
                    $r = $maxKriteria[$k->id] > 0 ? $x / $maxKriteria[$k->id] : 0;
                } else {
                    $r = $x > 0 ? $minKriteria[$k->id] / $x : 0;
                }

                $w = $bobotAktif[$k->id];
                $wr = $r * $w;

                $matriksR[$alt->id][$k->id] = $r;
                $matriksTerbobot[$alt->id][$k->id] = $wr;
                $totalV += $wr;
            }

            $hasil[] = [
                'alternatif' => $alt,
                'nilai_v'    => $totalV,
            ];
        }

        // 3. Urutkan dari nilai V tertinggi ke terendah untuk Peringkat
        usort($hasil, fn($a, $b) => $b['nilai_v'] <=> $a['nilai_v']);
        foreach ($hasil as $idx => &$row) {
            $row['peringkat'] = $idx + 1;
        }

        return [
            'kriterias'        => $kriterias,
            'alternatifs'      => $alternatifs,
            'bobot_aktif'      => $bobotAktif,
            'matriks_x'        => $matriksX,
            'max_kriteria'     => $maxKriteria,
            'min_kriteria'     => $minKriteria,
            'matriks_r'        => $matriksR,
            'matriks_terbobot' => $matriksTerbobot,
            'peringkat'        => $hasil,
        ];
    }
}