<?php

namespace Database\Seeders;

use App\Models\AhpComparison;
use App\Models\Alternatif;
use App\Models\Kriteria;
use App\Models\Penilaian;
use App\Services\SpkAhpSawService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed 5 Kriteria SNBP SMKN 8 Surabaya
        $dataKriteria = [
            ['kode' => 'C1', 'nama' => 'Rapor Akademik S1 - S5', 'atribut' => 'benefit'],
            ['kode' => 'C2', 'nama' => 'Nilai Kejuruan',         'atribut' => 'benefit'],
            ['kode' => 'C3', 'nama' => 'Prestasi Akademik',      'atribut' => 'benefit'],
            ['kode' => 'C4', 'nama' => 'Prestasi Non-Akademik',  'atribut' => 'benefit'],
            ['kode' => 'C5', 'nama' => 'Rata-rata Nilai TKA',    'atribut' => 'benefit'],
        ];

        $kMap = [];
        foreach ($dataKriteria as $dk) {
            $kMap[$dk['kode']] = Kriteria::create($dk);
        }

        // 2. Seed Matriks Perbandingan Berpasangan Kuesioner Pakar
        $pairs = [
            ['C1', 'C2', 1/2], ['C1', 'C3', 1/3], ['C1', 'C4', 1/3], ['C1', 'C5', 9],
            ['C2', 'C3', 1/4], ['C2', 'C4', 1/4], ['C2', 'C5', 7],
            ['C3', 'C4', 1],   ['C3', 'C5', 5],
            ['C4', 'C5', 5],
        ];

        foreach ($pairs as [$cA, $cB, $val]) {
            AhpComparison::create([
                'kriteria_1_id' => $kMap[$cA]->id,
                'kriteria_2_id' => $kMap[$cB]->id,
                'nilai'         => $val,
            ]);
        }

        // Hitung & simpan bobot awal AHP ke tabel kriterias
        app(SpkAhpSawService::class)->hitungAhp(true);

        // 3. Contoh Import Data Siswa Terverifikasi (C3 & C4 dari data asli, C1/C2/C5 diisi lewat menu Penilaian)
        $siswaSample = [
            ['A1',  'Dzakiyah Putri Sholeha',              'XII Kuliner 4',    0, 0],
            ['A2',  'DIRGANTARA PAMUNGKAS PUTRA POERNOMO', 'XII DKV 1',        4, 1],
            ['A5',  'Nabila Syakira',                      'XII DKV 2',        0, 4],
            ['A12', 'Aisyatul mutaalifah',                 'XII Perhotelan 2', 3, 0],
            ['A15', 'Aisyah Febriani Dewi',                'XII Busana 2',     0, 5],
            ['A20', 'kayla mahdaviqia azalea',             'XII Perhotelan 1', 5, 5],
            ['A23', 'RIKAH MAULIDYYAH HANUM',              'XII Kecantikan 3', 4, 3],
            ['A30', 'Ardianti Litasetyana',                'XII Busana 1',     5, 0],
            ['A51', 'FELY RACHMA CHARIR',                  'XII Busana 3',     5, 3],
            ['A53', 'DWI APRILIA',                         'XII Busana 1',     4, 3],
        ];

        foreach ($siswaSample as [$kode, $nama, $kelas, $c3, $c4]) {
            $alt = Alternatif::create([
                'kode'  => $kode,
                'nama'  => $nama,
                'kelas' => $kelas,
            ]);

            Penilaian::create(['alternatif_id' => $alt->id, 'kriteria_id' => $kMap['C1']->id, 'nilai' => 0]);
            Penilaian::create(['alternatif_id' => $alt->id, 'kriteria_id' => $kMap['C2']->id, 'nilai' => 0]);
            Penilaian::create(['alternatif_id' => $alt->id, 'kriteria_id' => $kMap['C3']->id, 'nilai' => $c3]);
            Penilaian::create(['alternatif_id' => $alt->id, 'kriteria_id' => $kMap['C4']->id, 'nilai' => $c4]);
            Penilaian::create(['alternatif_id' => $alt->id, 'kriteria_id' => $kMap['C5']->id, 'nilai' => 0]);
        }
    }
}