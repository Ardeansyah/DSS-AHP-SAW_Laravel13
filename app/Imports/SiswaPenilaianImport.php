<?php

namespace App\Exports;

use App\Models\Alternatif;
use App\Models\Kriteria;
use App\Models\Penilaian;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplatePenilaianExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'Template_Siswa_SNBP';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 38,
            'C' => 20,
            'D' => 12,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 12,
        ];
    }

    public function headings(): array
    {
        return ['kode', 'nama', 'kelas', 'c1', 'c2', 'c3', 'c4', 'c5'];
    }

    public function array(): array
    {
        $kriterias = Kriteria::orderBy('id')->get()->keyBy('kode');
        $alternatifs = Alternatif::orderBy('id')->get();
        $penilaians = Penilaian::all()->keyBy(fn($p) => $p->alternatif_id . '-' . $p->kriteria_id);

        if ($alternatifs->isEmpty()) {
            return [
                ['A1', 'Dzakiyah Putri Sholeha', 'XII Kuliner 4', 88.50, 89.00, 0, 0, 85.00],
                ['A2', 'DIRGANTARA PAMUNGKAS PUTRA POERNOMO', 'XII DKV 1', 90.20, 91.50, 4, 1, 88.40],
            ];
        }

        $rows = [];
        foreach ($alternatifs as $alt) {
            $getVal = fn(string $kode) => $kriterias->has($kode)
                ? (float) ($penilaians[$alt->id . '-' . $kriterias[$kode]->id]->nilai ?? 0)
                : 0;

            $rows[] = [
                $alt->kode,
                $alt->nama,
                $alt->kelas,
                $getVal('C1'),
                $getVal('C2'),
                $getVal('C3'),
                $getVal('C4'),
                $getVal('C5'),
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