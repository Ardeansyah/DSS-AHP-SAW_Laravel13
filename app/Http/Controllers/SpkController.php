<?php

namespace App\Http\Controllers;

use App\Models\AhpComparison;
use App\Models\Alternatif;
use App\Models\Kriteria;
use App\Models\Penilaian;
use App\Services\SpkAhpSawService;
use Illuminate\Http\Request;

class SpkController extends Controller
{
    public function __construct(private SpkAhpSawService $spk) {}

    // 1. Dashboard Utama
    public function dashboard()
    {
        $ahp = $this->spk->hitungAhp(false);
        $saw = $this->spk->hitungSaw();

        return view('spk.dashboard', [
            'totalKriteria'   => Kriteria::count(),
            'totalAlternatif' => Alternatif::count(),
            'ahp'             => $ahp,
            'top5'            => array_slice($saw['peringkat'] ?? [], 0, 5),
        ]);
    }

    // 2. Manajemen Data Kriteria
    public function kriteriaIndex()
    {
        $kriterias = Kriteria::orderBy('id')->get();
        return view('spk.kriteria', compact('kriterias'));
    }

    public function kriteriaStore(Request $request)
    {
        $data = $request->validate([
            'kode'    => 'required|string|max:10|unique:kriterias,kode',
            'nama'    => 'required|string|max:255',
            'atribut' => 'required|in:benefit,cost',
        ]);

        Kriteria::create($data);
        return back()->with('success', 'Kriteria berhasil ditambahkan.');
    }

    public function kriteriaUpdate(Request $request, Kriteria $kriteria)
    {
        $data = $request->validate([
            'kode'    => 'required|string|max:10|unique:kriterias,kode,' . $kriteria->id,
            'nama'    => 'required|string|max:255',
            'atribut' => 'required|in:benefit,cost',
        ]);

        $kriteria->update($data);
        return back()->with('success', 'Kriteria berhasil diperbarui.');
    }

    public function kriteriaDestroy(Kriteria $kriteria)
    {
        $kriteria->delete();
        $this->spk->hitungAhp(true);
        return back()->with('success', 'Kriteria berhasil dihapus.');
    }

    // 3. Pembobotan Kriteria (AHP)
    public function ahpIndex()
    {
        $ahp = $this->spk->hitungAhp(false);
        return view('spk.ahp', compact('ahp'));
    }

    public function ahpStore(Request $request)
    {
        $kriterias = Kriteria::orderBy('id')->get();
        $inputMatrix = $request->input('matrix', []);

        foreach ($kriterias as $i => $k1) {
            foreach ($kriterias as $j => $k2) {
                if ($i < $j && isset($inputMatrix[$k1->id][$k2->id])) {
                    $nilai = (float) $inputMatrix[$k1->id][$k2->id];
                    if ($nilai > 0) {
                        AhpComparison::updateOrCreate(
                            ['kriteria_1_id' => $k1->id, 'kriteria_2_id' => $k2->id],
                            ['nilai' => $nilai]
                        );
                    }
                }
            }
        }

        $ahp = $this->spk->hitungAhp(true);
        $pesan = $ahp['konsisten']
            ? 'Matriks AHP disimpan. Rasio Konsistensi (CR = ' . number_format($ahp['cr'], 4) . ') KONSISTEN.'
            : 'Matriks AHP disimpan, namun CR = ' . number_format($ahp['cr'], 4) . ' (> 0.10). Sesuaikan perbandingan agar konsisten.';

        return back()->with($ahp['konsisten'] ? 'success' : 'warning', $pesan);
    }

    // 4. Manajemen Data Alternatif (Siswa) + Import CSV
    public function alternatifIndex()
    {
        $alternatifs = Alternatif::orderBy('id')->get();
        return view('spk.alternatif', compact('alternatifs'));
    }

    public function alternatifStore(Request $request)
    {
        $data = $request->validate([
            'kode'   => 'required|string|max:15|unique:alternatifs,kode',
            'nama'   => 'required|string|max:255',
            'kelas'  => 'nullable|string|max:50',
            'ket_c3' => 'nullable|string',
            'ket_c4' => 'nullable|string',
        ]);

        Alternatif::create($data);
        return back()->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function alternatifImportCsv(Request $request)
    {
        $request->validate(['file_csv' => 'required|file|mimes:csv,txt']);

        $file = fopen($request->file('file_csv')->getRealPath(), 'r');
        $header = fgetcsv($file); // Lewati baris header pertama
        $kriterias = Kriteria::orderBy('id')->get()->keyBy('kode');

        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 2 || empty(trim($row[0]))) {
                continue;
            }

            // Format kolom CSV: kode, nama, kelas, c1, c2, c3, c4, c5
            $alt = Alternatif::updateOrCreate(
                ['kode' => trim($row[0])],
                [
                    'nama'  => trim($row[1] ?? '-'),
                    'kelas' => trim($row[2] ?? '-'),
                ]
            );

            $mapNilai = [
                'C1' => (float) ($row[3] ?? 0),
                'C2' => (float) ($row[4] ?? 0),
                'C3' => (float) ($row[5] ?? 0),
                'C4' => (float) ($row[6] ?? 0),
                'C5' => (float) ($row[7] ?? 0),
            ];

            foreach ($mapNilai as $kodeK => $val) {
                if ($kriterias->has($kodeK)) {
                    Penilaian::updateOrCreate(
                        ['alternatif_id' => $alt->id, 'kriteria_id' => $kriterias[$kodeK]->id],
                        ['nilai' => $val]
                    );
                }
            }
            $count++;
        }
        fclose($file);

        return back()->with('success', "$count data siswa dan nilai kriterianya berhasil diimpor.");
    }

    public function alternatifDestroy(Alternatif $alternatif)
    {
        $alternatif->delete();
        return back()->with('success', 'Data siswa berhasil dihapus.');
    }

    // 5. Input Penilaian Matriks Keputusan (X)
    public function penilaianIndex()
    {
        $kriterias = Kriteria::orderBy('id')->get();
        $alternatifs = Alternatif::orderBy('id')->get();
        $penilaians = Penilaian::all()->keyBy(fn($p) => $p->alternatif_id . '-' . $p->kriteria_id);

        return view('spk.penilaian', compact('kriterias', 'alternatifs', 'penilaians'));
    }

    public function penilaianStore(Request $request)
    {
        $scores = $request->input('nilai', []);

        foreach ($scores as $altId => $kriteriaScores) {
            foreach ($kriteriaScores as $kriteriaId => $val) {
                Penilaian::updateOrCreate(
                    ['alternatif_id' => $altId, 'kriteria_id' => $kriteriaId],
                    ['nilai' => (float) $val]
                );
            }
        }

        return back()->with('success', 'Seluruh nilai matriks penilaian berhasil disimpan.');
    }

    // 6. Tahapan Perhitungan & Hasil Perangkingan SAW
    public function perhitunganSaw()
    {
        $saw = $this->spk->hitungSaw();
        return view('spk.perhitungan', compact('saw'));
    }

    // 7. Analisis Sensitivitas Bobot (Total Bobot Tetap = 1)
    public function sensitivitas(Request $request)
    {
        $kriterias = Kriteria::orderBy('id')->get();
        $sawAsli = $this->spk->hitungSaw();
        $sawSimulasi = null;
        $bobotBaru = [];
        $targetId = $request->input('kriteria_id');
        $persenUbah = (float) $request->input('persentase', 0);

        if ($request->filled('kriteria_id') && $kriterias->count() > 1) {
            $target = $kriterias->firstWhere('id', $targetId);
            $wLama = (float) $target->bobot;
            $wBaruTarget = max(0.01, min(0.95, $wLama * (1 + ($persenUbah / 100))));
            $sisaBobotLama = 1.0 - $wLama;
            $sisaBobotBaru = 1.0 - $wBaruTarget;

            foreach ($kriterias as $k) {
                if ($k->id == $targetId) {
                    $bobotBaru[$k->id] = $wBaruTarget;
                } else {
                    $proporsi = $sisaBobotLama > 0 ? ((float) $k->bobot / $sisaBobotLama) : (1 / ($kriterias->count() - 1));
                    $bobotBaru[$k->id] = $proporsi * $sisaBobotBaru;
                }
            }

            $sawSimulasi = $this->spk->hitungSaw($bobotBaru);
        }

        return view('spk.sensitivitas', compact('kriterias', 'sawAsli', 'sawSimulasi', 'bobotBaru', 'targetId', 'persenUbah'));
    }
}