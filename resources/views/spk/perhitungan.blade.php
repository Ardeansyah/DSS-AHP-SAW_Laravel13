@extends('layouts.skote')
@section('title', 'Milestone 04: Normalisasi & Perhitungan SAW')

@section('content')
@php
    $kriterias = \App\Models\Kriteria::orderBy('id')->get();
    $alternatifs = \App\Models\Alternatif::with('penilaians')->orderBy('id')->get();

    // Hitung pembagi Max/Min tiap kriteria untuk transparansi tahap SAW
    $pembagi = [];
    foreach ($kriterias as $k) {
        $vals = $alternatifs->map(fn($a) => (float) ($a->penilaians->firstWhere('kriteria_id', $k->id)->nilai ?? 0));
        $pembagi[$k->id] = $k->atribut === 'benefit' ? max($vals->max(), 0.0001) : max($vals->min(), 0.0001);
    }
@endphp

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
    <div>
    </div>
    <form action="{{ route('spk.perhitungan.finalisasi') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary px-4">
            <i class="bx bx-check-double me-1"></i> Finalisasi Perhitungan &amp; Buka Milestone 05
        </button>
    </form>
</div>
<div class="card-body">
    <div class="alert border" style="background: var(--spk-canvas);">
        <strong>Nilai Pembagi Maksimum (max <i>X<sub>j</sub></i>) Tiap Kriteria Benefit:</strong>
        <div class="d-flex flex-wrap gap-4 mt-2 small">
            @foreach($kriterias as $k)
                <div>
                    <span class="badge bg-dark">{{ $k->kode }}</span>
                    Max = <strong>{{ number_format($pembagi[$k->id], 2) }}</strong> |
                    Bobot (<i>w</i>) = <strong>{{ number_format($k->bobot, 4) }}</strong>
                </div>
            @endforeach
        </div>
    </div>
    <div class="card-body">
        <div class="alert border" style="background: var(--spk-canvas);">
            <strong>Nilai Pembagi Maksimum ($\max X_j$) Tiap Kriteria Benefit:</strong>
            <div class="d-flex flex-wrap gap-4 mt-2 small">
                @foreach($kriterias as $k)
                    <div>
                        <span class="badge bg-dark">{{ $k->kode }}</span>
                        Max = <strong>{{ number_format($pembagi[$k->id], 2) }}</strong> |
                        Bobot ($w$) = <strong>{{ number_format($k->bobot, 4) }}</strong>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="table-responsive" style="max-height: 560px;">
            <table class="table table-bordered table-sm align-middle">
                <thead class="sticky-top">
                    <tr>
                        <th rowspan="2">Kode</th>
                        <th rowspan="2">Nama Siswa</th>
                        <th colspan="{{ $kriterias->count() }}" class="text-center">Matriks Normalisasi</th>
                        <th rowspan="2" class="text-end">Skor Preferensi</th>
                    </tr>
                    <tr>
                        @foreach($kriterias as $k)
                            <th class="text-center">{{ $k->kode }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($alternatifs as $alt)
                        @php
                            $pMap = $alt->penilaians->keyBy('kriteria_id');
                            $totalV = 0;
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $alt->kode }}</td>
                            <td>{{ $alt->nama }}</td>
                            @foreach($kriterias as $k)
                                @php
                                    $x = (float) ($pMap[$k->id]->nilai ?? 0);
                                    $r = $k->atribut === 'benefit' ? ($x / $pembagi[$k->id]) : ($pembagi[$k->id] / max($x, 0.0001));
                                    $totalV += $r * (float) $k->bobot;
                                @endphp
                                <td class="text-center">{{ number_format($r, 4) }}</td>
                            @endforeach
                            <td class="text-end fw-bold" style="color: var(--spk-obsidian);">{{ number_format($totalV, 4) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection