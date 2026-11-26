<?php

namespace App\Exports;

use App\Models\Alternatif;
use App\Models\Kriteria;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HasilSpkExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'Hasil_Perangkingan_SAW';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 10,
            'C' => 36,
            'D' => 20,
            'E' => 14,
            'F' => 14,
            'G' => 14,
            'H' => 14,
            'I' => 14,
            'J' => 18,
        ];
    }

    public function headings(): array
    {
        return ['Peringkat', 'Kode', 'Nama Siswa', 'Kelas', 'R_C1', 'R_C2', 'R_C3', 'R_C4', 'R_C5', 'Skor Akhir (V)'];
    }

    public function array(): array
    {
        $kriterias = Kriteria::orderBy('id')->get();
        $alternatifs = Alternatif::with('penilaians')->get();

        $pembagi = [];
        foreach ($kriterias as $k) {
            $vals = $alternatifs->map(fn($a) => (float) ($a->penilaians->firstWhere('kriteria_id', $k->id)->nilai ?? 0));
            $pembagi[$k->id] = max($vals->max(), 0.0001);
        }

        $ranked = $alternatifs->map(function ($alt) use ($kriterias, $pembagi) {
            $pMap = $alt->penilaians->keyBy('kriteria_id');
            $r = [];
            $skor = 0;
            foreach ($kriterias as $k) {
                $x = (float) ($pMap[$k->id]->nilai ?? 0);
                $norm = $x / $pembagi[$k->id];
                $r[$k->kode] = round($norm, 4);
                $skor += $norm * (float) $k->bobot;
            }
            return [
                'kode'  => $alt->kode,
                'nama'  => $alt->nama,
                'kelas' => $alt->kelas,
                'r'     => $r,
                'skor'  => round($skor, 4),
            ];
        })->sortByDesc('skor')->values();

        $rows = [];
        foreach ($ranked as $idx => $item) {
            $rows[] = [
                $idx + 1,
                $item['kode'],
                $item['nama'],
                $item['kelas'],
                $item['r']['C1'] ?? 0,
                $item['r']['C2'] ?? 0,
                $item['r']['C3'] ?? 0,
                $item['r']['C4'] ?? 0,
                $item['r']['C5'] ?? 0,
                $item['skor'],
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '111111']],
            ],
        ];
    }
}