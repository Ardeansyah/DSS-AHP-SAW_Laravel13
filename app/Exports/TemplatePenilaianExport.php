<?php

namespace App\Exports;

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
        return 'Template_Input_Alternatif';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 32,
            'C' => 18,
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
        return [
            ['A1', 'Contoh Siswa Pertama', 'XII RPL 1', 88.50, 90.00, 4, 2, 85.40],
            ['A2', 'Contoh Siswa Kedua',   'XII TKJ 2', 86.20, 88.75, 0, 3, 82.10],
            ['A3', 'Contoh Siswa Ketiga',  'XII DKV 1', 91.00, 92.30, 5, 0, 89.00],
        ];
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