@extends('layouts.app')
@section('content')
<h3 class="fw-bold mb-3">6. Analisis Sensitivitas Bobot Kriteria</h3>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('sensitivitas.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Pilih Kriteria yang Diubah</label>
                <select name="kriteria_id" class="form-select" required>
                    @foreach($kriterias as $k)
                        <option value="{{ $k->id }}" {{ $targetId == $k->id ? 'selected' : '' }}>
                            {{ $k->kode }} - {{ $k->nama }} (Bobot Asli: {{ number_format($k->bobot, 4) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Perubahan Bobot (%)</label>
                <input type="number" step="1" name="persentase" value="{{ $persenUbah ?: 20 }}" class="form-control" placeholder="Misal: 20 atau -20" required>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100">Jalankan Simulasi</button>
            </div>
        </form>
    </div>
</div>

@if($sawSimulasi)
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold">Perbandingan Peringkat Sebelum vs Sesudah Perubahan Bobot (Total Bobot = 1.0000)</div>
    <div class="card-body table-responsive">
        @php
            $rankAsliMap = [];
            foreach ($sawAsli['peringkat'] as $r) {
                $rankAsliMap[$r['alternatif']->id] = ['rank' => $r['peringkat'], 'v' => $r['nilai_v']];
            }
        @endphp
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode</th><th>Nama Siswa</th>
                    <th>Skor V Asli</th><th>Peringkat Asli</th>
                    <th>Skor V Simulasi</th><th>Peringkat Simulasi</th><th>Status Perubahan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sawSimulasi['peringkat'] as $rSim)
                @php
                    $id = $rSim['alternatif']->id;
                    $awal = $rankAsliMap[$id]['rank'] ?? '-';
                    $selisih = is_numeric($awal) ? ($awal - $rSim['peringkat']) : 0;
                @endphp
                <tr>
                    <td>{{ $rSim['alternatif']->kode }}</td>
                    <td>{{ $rSim['alternatif']->nama }}</td>
                    <td>{{ number_format($rankAsliMap[$id]['v'] ?? 0, 6) }}</td>
                    <td>#{{ $awal }}</td>
                    <td class="fw-bold">{{ number_format($rSim['nilai_v'], 6) }}</td>
                    <td class="fw-bold">#{{ $rSim['peringkat'] }}</td>
                    <td>
                        @if($selisih > 0) <span class="badge bg-success">Naik {{ $selisih }}</span>
                        @elseif($selisih < 0) <span class="badge bg-danger">Turun {{ abs($selisih) }}</span>
                        @else <span class="badge bg-secondary">Tetap</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection