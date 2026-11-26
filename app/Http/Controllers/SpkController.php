<?php

namespace App\Http\Controllers;

use App\Exports\HasilSpkExport;
use App\Exports\TemplatePenilaianExport;
use App\Imports\SiswaPenilaianImport;
use App\Models\AhpComparison;
use App\Models\Alternatif;
use App\Models\Kriteria;
use App\Models\Penilaian;
use App\Services\MilestoneGatekeeper;
use App\Services\SpkAhpSawService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SpkController extends Controller
{
    public function __construct(
        protected SpkAhpSawService $spkService,
        protected MilestoneGatekeeper $gatekeeper
    ) {}

    // MILESTONE 01: Kriteria
    public function kriteria()
    {
        $kriterias = Kriteria::orderBy('id')->get();
        $state = $this->gatekeeper->getState();

        return view('spk.kriteria', compact('kriterias', 'state'));
    }

    public function storeKriteria(Request $request)
    {
        $data = $request->validate([
            'kode'    => 'required|string|max:10|unique:kriterias,kode',
            'nama'    => 'required|string|max:100',
            'atribut' => 'required|in:benefit,cost',
        ]);

        Kriteria::create($data);
        $this->gatekeeper->getState();

        return back()->with('success', 'Kriteria berhasil ditambahkan.');
    }

    public function destroyKriteria(Kriteria $kriteria)
    {
        $kriteria->delete();
        $this->gatekeeper->getState();

        return back()->with('success', 'Kriteria dihapus dan status milestone diperbarui.');
    }

    // MILESTONE 02: Pembobotan AHP & Uji Konsistensi
    public function ahp()
    {
        $kriterias = Kriteria::orderBy('id')->get();
        $hasilAhp = $this->spkService->hitungAhp(false);

        return view('spk.ahp', compact('kriterias', 'hasilAhp'));
    }

    public function hitungAhp(Request $request)
    {
        $matrix = $request->input('matrix', []);
        $kriterias = Kriteria::orderBy('id')->get();

        foreach ($kriterias as $i => $k1) {
            foreach ($kriterias as $j => $k2) {
                if ($i < $j && isset($matrix[$k1->id][$k2->id])) {
                    AhpComparison::updateOrCreate(
                        ['kriteria_1_id' => $k1->id, 'kriteria_2_id' => $k2->id],
                        ['nilai' => round((float) $matrix[$k1->id][$k2->id], 4)]
                    );
                }
            }
        }

        $hasilAhp = $this->spkService->hitungAhp(true);
        $this->gatekeeper->updateAhpStatus($hasilAhp['cr']);

        if ($hasilAhp['cr'] <= 0.10) {
            return redirect()->route('spk.alternatif')
                ->with('success', sprintf('Matriks AHP KONSISTEN (CR = %.4f ≤ 0.10). Milestone 03 kini terbuka!', $hasilAhp['cr']));
        }

        return back()->with('error', sprintf('Matriks AHP TIDAK KONSISTEN (CR = %.4f > 0.10). Sesuaikan nilai perbandingan agar Milestone 03 terbuka.', $hasilAhp['cr']));
    }

    // MILESTONE 03: Data Alternatif & Matriks Penilaian
    public function alternatif()
    {
        $kriterias = Kriteria::orderBy('id')->get();
        $alternatifs = Alternatif::with('penilaians')->orderBy('id')->get();
        $state = $this->gatekeeper->getState();

        return view('spk.alternatif', compact('kriterias', 'alternatifs', 'state'));
    }

    public function importExcel(Request $request)
    {
        $request->validate(['file_excel' => 'required|mimes:xlsx,xls,csv|max:5120']);

        Excel::import(new SiswaPenilaianImport, $request->file('file_excel'));
        $this->gatekeeper->markAlternatifDirty();

        $totalBaru = Alternatif::count();

        return back()->with('success', "Dataset alternatif berhasil di-reset dan diganti dengan {$totalBaru} data baru dari file Excel. Klik \"Validasi Data & Buka Milestone 04\" untuk melanjutkan.");
    }

    public function downloadTemplate()
    {
        return Excel::download(new TemplatePenilaianExport, 'Template_Penilaian_SNBP_SMKN8.xlsx');
    }

    public function validasiAlternatif()
    {
        $res = $this->gatekeeper->validateAlternatifData();

        if (!$res['ok']) {
            return back()->with('error', $res['message']);
        }

        return redirect()->route('spk.perhitungan')->with('success', $res['message']);
    }

    // MILESTONE 04: Proses Perhitungan & Normalisasi SAW
    public function perhitungan()
    {
        $hasilSaw = $this->spkService->hitungSaw();
        $state = $this->gatekeeper->getState();

        return view('spk.perhitungan', compact('hasilSaw', 'state'));
    }

    public function resetSistem()
    {
        DB::transaction(function () {
            Penilaian::query()->delete();
            Alternatif::query()->delete();
            AhpComparison::query()->delete();
            Kriteria::query()->delete();

            DB::table('spk_states')->update([
                'm1_kriteria_valid'         => false,
                'm2_ahp_konsisten'          => false,
                'm3_alternatif_tervalidasi' => false,
                'm4_saw_terhitung'          => false,
                'last_cr'                   => null,
                'm4_calculated_at'          => null,
                'updated_at'                => now(),
            ]);
        });

        return redirect()->route('spk.kriteria')
            ->with('success', 'Sistem berhasil di-reset ke kondisi awal. Silakan mulai dari Milestone 01.');
    }

    public function finalisasiSaw()
    {
        $this->gatekeeper->completeSawCalculation();

        return redirect()->route('spk.hasil')
            ->with('success', 'Perhitungan SAW berhasil difinalisasi. Milestone 05 kini terbuka!');
    }

    // MILESTONE 05: Hasil Akhir, Evaluasi & Analisis Sensitivitas
    public function hasil()
    {
        $hasilSaw = $this->spkService->hitungSaw();
        $kriterias = Kriteria::orderBy('id')->get();
        $daftarKelas = Alternatif::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        return view('spk.hasil', compact('hasilSaw', 'kriterias', 'daftarKelas'));
    }

    public function exportExcel()
    {
        return Excel::download(new HasilSpkExport, 'Hasil_SPK_SNBP_SMKN8_Surabaya.xlsx');
    }
}