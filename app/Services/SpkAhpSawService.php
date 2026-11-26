<?php

namespace App\Services;

use App\Models\AhpComparison;
use App\Models\Alternatif;
use App\Models\Kriteria;

class SpkAhpSawService
{
    private const RI_TABLE = [
        1 => 0.00,
        2 => 0.00,
        3 => 0.58,
        4 => 0.90,
        5 => 1.12,
        6 => 1.24,
        7 => 1.32,
        8 => 1.41,
        9 => 1.45,
        10 => 1.49,
    ];

    public function hitungAhp(bool $simpanBobot = false): array
    {
        $kriterias = Kriteria::orderBy('id')->get();
        $n = $kriterias->count();

        if ($n === 0) {
            return [
                'kriterias'  => $kriterias,
                'matrix'     => [],
                'normMatrix' => [],
                'colSums'    => [],
                'weights'    => [],
                'lambdaMax'  => 0,
                'ci'         => 0,
                'ri'         => 0,
                'cr'         => 0,
                'konsisten'  => false,
            ];
        }

        $comparisons = AhpComparison::all()->keyBy(fn($c) => $c->kriteria_1_id . '-' . $c->kriteria_2_id);

        // 1. Susun matriks perbandingan berpasangan (4 desimal)
        $matrix = [];
        foreach ($kriterias as $i => $k1) {
            foreach ($kriterias as $j => $k2) {
                if ($i === $j) {
                    $matrix[$k1->id][$k2->id] = 1.0;
                } elseif (isset($comparisons[$k1->id . '-' . $k2->id])) {
                    $matrix[$k1->id][$k2->id] = round((float) $comparisons[$k1->id . '-' . $k2->id]->nilai, 4);
                } elseif (isset($comparisons[$k2->id . '-' . $k1->id]) && (float) $comparisons[$k2->id . '-' . $k1->id]->nilai > 0) {
                    $matrix[$k1->id][$k2->id] = round(1 / (float) $comparisons[$k2->id . '-' . $k1->id]->nilai, 4);
                } else {
                    $matrix[$k1->id][$k2->id] = 1.0;
                }
            }
        }

        // 2. Hitung jumlah tiap kolom (4 desimal)
        $colSums = [];
        foreach ($kriterias as $k2) {
            $sum = 0.0;
            foreach ($kriterias as $k1) {
                $sum += $matrix[$k1->id][$k2->id];
            }
            $colSums[$k2->id] = round($sum, 4);
        }

        // 3. Normalisasi matriks & rata-rata baris -> Bobot Prioritas (4 desimal)
        $normMatrix = [];
        $weights = [];
        foreach ($kriterias as $k1) {
            $rowSum = 0.0;
            foreach ($kriterias as $k2) {
                $normVal = $colSums[$k2->id] > 0 ? round($matrix[$k1->id][$k2->id] / $colSums[$k2->id], 4) : 0.0;
                $normMatrix[$k1->id][$k2->id] = $normVal;
                $rowSum += $normVal;
            }
            $bobot = round($rowSum / $n, 4);
            $weights[$k1->id] = $bobot;

            if ($simpanBobot) {
                $k1->update(['bobot' => $bobot]);
            }
        }

        // 4. Uji Konsistensi (Weighted Sum -> Consistency Vector -> Lambda Max -> CI -> CR)
        $lambdaSum = 0.0;
        foreach ($kriterias as $k1) {
            $ws = 0.0;
            foreach ($kriterias as $k2) {
                $ws += $matrix[$k1->id][$k2->id] * $weights[$k2->id];
            }
            $lambdaSum += $weights[$k1->id] > 0 ? ($ws / $weights[$k1->id]) : 0.0;
        }

        $lambdaMax = round($lambdaSum / $n, 4);
        $ci = $n > 1 ? round(($lambdaMax - $n) / ($n - 1), 4) : 0.0;
        $ri = self::RI_TABLE[$n] ?? 1.12;
        $cr = $ri > 0 ? round($ci / $ri, 4) : 0.0;

        return [
            'kriterias'  => $kriterias,
            'matrix'     => $matrix,
            'normMatrix' => $normMatrix,
            'colSums'    => $colSums,
            'weights'    => $weights,
            'lambdaMax'  => $lambdaMax,
            'ci'         => $ci,
            'ri'         => $ri,
            'cr'         => $cr,
            'konsisten'  => $cr <= 0.10,
        ];
    }

    public function hitungSaw(): array
    {
        $kriterias = Kriteria::orderBy('id')->get();
        $alternatifs = Alternatif::with('penilaians')->orderBy('id')->get();

        // Pembagi Max (Benefit) atau Min (Cost) dibulatkan 4 desimal
        $pembagi = [];
        foreach ($kriterias as $k) {
            $vals = $alternatifs->map(function ($alt) use ($k) {
                return (float) ($alt->penilaians->firstWhere('kriteria_id', $k->id)->nilai ?? 0);
            });

            if ($k->atribut === 'cost') {
                $positiveVals = $vals->filter(fn($v) => $v > 0);
                $pembagi[$k->id] = $positiveVals->isNotEmpty() ? round((float) $positiveVals->min(), 4) : 0.0;
            } else {
                $pembagi[$k->id] = round((float) $vals->max(), 4);
            }
        }

        // Normalisasi R_ij (4 desimal) & Nilai Preferensi V_i (4 desimal)
        $hasil = $alternatifs->map(function ($alt) use ($kriterias, $pembagi) {
            $pMap = $alt->penilaians->keyBy('kriteria_id');
            $raw = [];
            $norm = [];
            $skorTotal = 0.0;

            foreach ($kriterias as $k) {
                $x = round((float) ($pMap[$k->id]->nilai ?? 0), 4);
                $div = $pembagi[$k->id];

                if ($div <= 0) {
                    $r = 0.0;
                } elseif ($k->atribut === 'cost') {
                    $r = $x > 0 ? round($div / $x, 4) : 0.0;
                } else {
                    $r = round($x / $div, 4);
                }

                $w = round((float) $k->bobot, 4);
                $raw[$k->id] = $x;
                $norm[$k->id] = $r;
                $skorTotal += round($r * $w, 4);
            }

            return [
                'id'    => $alt->id,
                'kode'  => $alt->kode,
                'nama'  => $alt->nama,
                'kelas' => $alt->kelas,
                'raw'   => $raw,
                'norm'  => $norm,
                'skor'  => round($skorTotal, 4),
            ];
        })->sortByDesc('skor')->values()->map(function ($item, $idx) {
            $item['rank'] = $idx + 1;
            return $item;
        });

        return [
            'kriterias' => $kriterias,
            'pembagi'   => $pembagi,
            'hasil'     => $hasil,
        ];
    }
}