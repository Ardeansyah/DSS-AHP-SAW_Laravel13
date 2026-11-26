<?php

namespace App\Imports;

use App\Models\Alternatif;
use App\Models\Kriteria;
use App\Models\Penilaian;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaPenilaianImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows): void
    {
        $kriterias = Kriteria::all()->keyBy(fn($k) => strtolower(trim($k->kode)));

        // Saring hanya baris yang memiliki kode atau nama valid
        $validRows = $rows->filter(function ($row) {
            $kode = trim((string) ($row['kode'] ?? ''));
            $nama = trim((string) ($row['nama'] ?? ''));
            return $kode !== '' || $nama !== '';
        });

        if ($validRows->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($validRows, $kriterias) {
            // Reset bersih data penilaian & alternatif lama sebelum memasukkan dataset baru
            Penilaian::query()->delete();
            Alternatif::query()->delete();

            $now = now();
            $penilaianBatch = [];

            foreach ($validRows as $index => $row) {
                $kodeRaw  = trim((string) ($row['kode'] ?? ''));
                $namaRaw  = trim((string) ($row['nama'] ?? ''));
                $kelasRaw = trim((string) ($row['kelas'] ?? '-'));

                $kode  = mb_strtoupper($kodeRaw !== '' ? $kodeRaw : 'A' . ($index + 1), 'UTF-8');
                $nama  = mb_strtoupper(preg_replace('/\s+/', ' ', $namaRaw !== '' ? $namaRaw : $kode), 'UTF-8');
                $kelas = mb_strtoupper(preg_replace('/\s+/', ' ', $kelasRaw !== '' ? $kelasRaw : '-'), 'UTF-8');

                $alternatif = Alternatif::create([
                    'kode'   => $kode,
                    'nama'   => $nama,
                    'kelas'  => $kelas,
                    'ket_c3' => '-',
                    'ket_c4' => '-',
                ]);

                foreach ($kriterias as $kodeLower => $kriteria) {
                    $rawVal = $row[$kodeLower] ?? 0;
                    $nilai = ($rawVal !== '' && $rawVal !== null)
                        ? (float) str_replace(',', '.', (string) $rawVal)
                        : 0.0;

                    $penilaianBatch[] = [
                        'alternatif_id' => $alternatif->id,
                        'kriteria_id'   => $kriteria->id,
                        'nilai'         => $nilai,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }
            }

            // Insert massal agar proses impor ratusan baris selesai dalam hitungan milidetik
            foreach (array_chunk($penilaianBatch, 500) as $chunk) {
                Penilaian::insert($chunk);
            }
        });
    }
}