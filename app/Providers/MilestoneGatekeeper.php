<?php

namespace App\Services;

use App\Models\Alternatif;
use App\Models\Kriteria;
use App\Models\Penilaian;
use Illuminate\Support\Facades\DB;

class MilestoneGatekeeper
{
    public function getState(): object
    {
        $state = DB::table('spk_states')->first();

        if (!$state) {
            DB::table('spk_states')->insert([
                'm1_kriteria_valid'         => false,
                'm2_ahp_konsisten'          => false,
                'm3_alternatif_tervalidasi' => false,
                'm4_saw_terhitung'          => false,
                'created_at'                => now(),
                'updated_at'                => now(),
            ]);
            $state = DB::table('spk_states')->first();
        }

        // Sinkronisasi otomatis Milestone 1 (Minimal 5 kriteria sesuai ketentuan FP SPK)
        $jumlahKriteria = Kriteria::count();
        $m1Valid = $jumlahKriteria >= 5;

        if ((bool) $state->m1_kriteria_valid !== $m1Valid) {
            DB::table('spk_states')->where('id', $state->id)->update([
                'm1_kriteria_valid'         => $m1Valid,
                // Jika kriteria berubah/kurang dari 5, kunci ulang tahap setelahnya
                'm2_ahp_konsisten'          => $m1Valid ? $state->m2_ahp_konsisten : false,
                'm3_alternatif_tervalidasi' => $m1Valid ? $state->m3_alternatif_tervalidasi : false,
                'm4_saw_terhitung'          => $m1Valid ? $state->m4_saw_terhitung : false,
                'updated_at'                => now(),
            ]);
            $state = DB::table('spk_states')->first();
        }

        return $state;
    }

    /**
     * Dipanggil saat Milestone 02 (AHP) disimpan.
     * Syarat: CR <= 0.10
     */
    public function updateAhpStatus(float $cr): void
    {
        $state = $this->getState();
        $isConsistent = $state->m1_kriteria_valid && ($cr <= 0.10);

        DB::table('spk_states')->where('id', $state->id)->update([
            'm2_ahp_konsisten'          => $isConsistent,
            'last_cr'                   => $cr,
            // Jika matriks AHP diubah ulang, wajib validasi ulang SAW di M4
            'm4_saw_terhitung'          => false,
            'updated_at'                => now(),
        ]);
    }

    /**
     * Dipanggil saat user menekan tombol "Validasi & Kunci Data Alternatif" di Milestone 03.
     * Syarat: Minimal 30 alternatif & semua sel matriks penilaian terisi lengkap.
     */
    public function validateAlternatifData(): array
    {
        $state = $this->getState();

        if (!$state->m2_ahp_konsisten) {
            return ['ok' => false, 'message' => 'Selesaikan dan pastikan konsistensi AHP (CR ≤ 0.10) terlebih dahulu.'];
        }

        $jumlahAlternatif = Alternatif::count();
        if ($jumlahAlternatif < 30) {
            return ['ok' => false, 'message' => "Jumlah alternatif saat ini {$jumlahAlternatif}. Minimal 30 alternatif wajib terpenuhi."];
        }

        $jumlahKriteria = Kriteria::count();
        $expectedCells = $jumlahAlternatif * $jumlahKriteria;
        $actualCells = Penilaian::whereNotNull('nilai')->count();

        if ($actualCells < $expectedCells) {
            return ['ok' => false, 'message' => 'Masih terdapat nilai kriteria siswa yang kosong. Lengkapi semua penilaian terlebih dahulu.'];
        }

        // Pastikan nilai rapor/kejuruan/TKA (C1, C2, C5) sudah diisi (tidak 0 semua)
        $belumDiisi = Penilaian::whereHas('kriteria', fn($q) => $q->whereIn('kode', ['C1', 'C2', 'C5']))
            ->where('nilai', '<=', 0)
            ->count();

        if ($belumDiisi > 0) {
            return ['ok' => false, 'message' => "Terdapat {$belumDiisi} sel nilai Rapor/Kejuruan/TKA yang masih bernilai 0. Silakan lengkapi via input atau import Excel."];
        }

        DB::table('spk_states')->where('id', $state->id)->update([
            'm3_alternatif_tervalidasi' => true,
            'm4_saw_terhitung'          => false,
            'updated_at'                => now(),
        ]);

        return ['ok' => true, 'message' => "Data {$jumlahAlternatif} alternatif berhasil divalidasi. Milestone 04 (Perhitungan SAW) kini terbuka."];
    }

    /**
     * Dipanggil saat data alternatif diedit/diimport ulang sehingga M4 perlu dihitung ulang.
     */
    public function markAlternatifDirty(): void
    {
        $state = $this->getState();
        DB::table('spk_states')->where('id', $state->id)->update([
            'm3_alternatif_tervalidasi' => false,
            'm4_saw_terhitung'          => false,
            'updated_at'                => now(),
        ]);
    }

    /**
     * Dipanggil saat user memfinalisasi perhitungan normalisasi SAW di Milestone 04.
     */
    public function completeSawCalculation(): void
    {
        $state = $this->getState();
        if ($state->m1_kriteria_valid && $state->m2_ahp_konsisten && $state->m3_alternatif_tervalidasi) {
            DB::table('spk_states')->where('id', $state->id)->update([
                'm4_saw_terhitung' => true,
                'm4_calculated_at' => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    /**
     * Mengembalikan daftar 5 milestone beserta status unlocked/completed untuk UI & Middleware.
     */
    public function getMilestones(string $activeRoute = ''): array
    {
        $s = $this->getState();

        $m1Unlocked = true;
        $m2Unlocked = (bool) $s->m1_kriteria_valid;
        $m3Unlocked = $m2Unlocked && (bool) $s->m2_ahp_konsisten;
        $m4Unlocked = $m3Unlocked && (bool) $s->m3_alternatif_tervalidasi;
        $m5Unlocked = $m4Unlocked && (bool) $s->m4_saw_terhitung;

        return [
            1 => [
                'number'    => '01',
                'title'     => 'Definisi Kriteria',
                'subtitle'  => 'Min. 5 Kriteria & Atribut',
                'route'     => 'spk.kriteria',
                'unlocked'  => $m1Unlocked,
                'completed' => (bool) $s->m1_kriteria_valid,
                'active'    => $activeRoute === 'spk.kriteria',
                'lock_msg'  => '',
            ],
            2 => [
                'number'    => '02',
                'title'     => 'Pembobotan AHP',
                'subtitle'  => 'Syarat Konsistensi CR ≤ 0.10',
                'route'     => 'spk.ahp',
                'unlocked'  => $m2Unlocked,
                'completed' => (bool) $s->m2_ahp_konsisten,
                'active'    => $activeRoute === 'spk.ahp',
                'lock_msg'  => 'Terkunci: Selesaikan minimal 5 kriteria pada Milestone 01 terlebih dahulu.',
            ],
            3 => [
                'number'    => '03',
                'title'     => 'Data & Penilaian',
                'subtitle'  => 'Min. 30 Siswa Tervalidasi',
                'route'     => 'spk.alternatif',
                'unlocked'  => $m3Unlocked,
                'completed' => (bool) $s->m3_alternatif_tervalidasi,
                'active'    => $activeRoute === 'spk.alternatif',
                'lock_msg'  => 'Terkunci: Nilai Consistency Ratio (CR) pada Milestone 02 harus ≤ 0.10.',
            ],
            4 => [
                'number'    => '04',
                'title'     => 'Normalisasi SAW',
                'subtitle'  => 'Matriks X, R & Terbobot',
                'route'     => 'spk.perhitungan',
                'unlocked'  => $m4Unlocked,
                'completed' => (bool) $s->m4_saw_terhitung,
                'active'    => $activeRoute === 'spk.perhitungan',
                'lock_msg'  => 'Terkunci: Validasi kelengkapan minimal 30 data alternatif pada Milestone 03.',
            ],
            5 => [
                'number'    => '05',
                'title'     => 'Hasil & Sensitivitas',
                'subtitle'  => 'Peringkat Akhir & Evaluasi',
                'route'     => 'spk.hasil',
                'unlocked'  => $m5Unlocked,
                'completed' => $m5Unlocked,
                'active'    => $activeRoute === 'spk.hasil',
                'lock_msg'  => 'Terkunci: Jalankan & finalisasi perhitungan SAW pada Milestone 04.',
            ],
        ];
    }
}