@extends('layouts.app')
@section('content')
<h3 class="fw-bold mb-3">Dashboard Sistem Pendukung Keputusan (AHP - SAW)</h3>
<p class="text-muted">Studi Kasus: Pemilihan Siswa Eligible Jalur SNBP di SMKN 8 Surabaya</p>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3">
            <small class="text-muted">Total Kriteria</small>
            <h2 class="fw-bold mb-0">{{ $totalKriteria }}</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3">
            <small class="text-muted">Total Alternatif (Siswa)</small>
            <h2 class="fw-bold mb-0">{{ $totalAlternatif }}</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3">
            <small class="text-muted">Rasio Konsistensi AHP (CR)</small>
            <h2 class="fw-bold mb-0 {{ ($ahp['konsisten'] ?? false) ? 'text-success' : 'text-danger' }}">
                {{ isset($ahp['cr']) ? number_format($ahp['cr'], 4) : '-' }}
            </h2>
            <small>{{ ($ahp['konsisten'] ?? false) ? 'Konsisten (CR <= 0.10)' : 'Belum Konsisten (CR > 0.10)' }}</small>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold">Top 5 Peringkat Sementara Siswa Eligible SNBP</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr><th>Peringkat</th><th>Kode</th><th>Nama Siswa</th><th>Kelas</th><th>Nilai Preferensi (V)</th></tr>
            </thead>
            <tbody>
                @forelse($top5 as $row)
                <tr>
                    <td><span class="badge bg-primary">#{{ $row['peringkat'] }}</span></td>
                    <td>{{ $row['alternatif']->kode }}</td>
                    <td>{{ $row['alternatif']->nama }}</td>
                    <td>{{ $row['alternatif']->kelas }}</td>
                    <td class="fw-bold">{{ number_format($row['nilai_v'], 6) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">Belum ada data penilaian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection