@extends('layouts.app')
@section('content')
<h3 class="fw-bold mb-3">2. Pembobotan Kriteria (Analytical Hierarchy Process / AHP)</h3>

@if(!empty($ahp))
<form action="{{ route('ahp.store') }}" method="POST">
    @csrf
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Tahap 1: Matriks Perbandingan Berpasangan (Pairwise Comparison)</div>
        <div class="card-body table-responsive">
            <p class="small text-muted">Isi sel segitiga atas (desimal diperbolehkan, misal <code>0.5</code> untuk <code>1/2</code>, <code>0.3333</code> untuk <code>1/3</code>, <code>0.25</code> untuk <code>1/4</code>). Sel kebalikannya akan dihitung otomatis.</p>
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kriteria</th>
                        @foreach($ahp['kriterias'] as $k) <th>{{ $k->kode }} ({{ $k->nama }})</th> @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($ahp['kriterias'] as $i => $k1)
                    <tr>
                        <th class="table-light text-start">{{ $k1->kode }}</th>
                        @foreach($ahp['kriterias'] as $j => $k2)
                            <td>
                                @if($i === $j)
                                    <input type="text" class="form-control text-center bg-light" value="1" readonly>
                                @elseif($i < $j)
                                    <input type="number" step="0.0001" min="0.01" max="9"
                                           name="matrix[{{ $k1->id }}][{{ $k2->id }}]"
                                           value="{{ round($ahp['matriks'][$i][$j], 4) }}"
                                           class="form-control text-center border-primary">
                                @else
                                    <span class="text-muted">{{ number_format($ahp['matriks'][$i][$j], 4) }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                    <tr class="table-secondary fw-bold">
                        <td class="text-start">Jumlah Kolom</td>
                        @foreach($ahp['jumlah_kolom'] as $sum) <td>{{ number_format($sum, 4) }}</td> @endforeach
                    </tr>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Hitung Ulang & Simpan Bobot AHP</button>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">Tahap 2: Matriks Normalisasi & Priority Vector (Bobot Kriteria)</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th>Kriteria</th>
                    @foreach($ahp['kriterias'] as $k) <th>{{ $k->kode }}</th> @endforeach
                    <th class="bg-primary text-white">Bobot Prioritas (W)</th>
                    <th>A * W</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ahp['kriterias'] as $i => $k1)
                <tr>
                    <th class="text-start">{{ $k1->kode }} - {{ $k1->nama }}</th>
                    @foreach($ahp['kriterias'] as $j => $k2)
                        <td>{{ number_format($ahp['normalisasi'][$i][$j], 4) }}</td>
                    @endforeach
                    <td class="fw-bold bg-light">{{ number_format($ahp['bobot'][$i], 6) }}</td>
                    <td>{{ number_format($ahp['aw'][$i], 4) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold">Tahap 3: Pengujian Rasio Konsistensi (Consistency Ratio)</div>
    <div class="card-body">
        <ul class="mb-2">
            <li><strong>Eigen Value Maksimum ($\lambda_{\max}$):</strong> {{ number_format($ahp['lambda_max'], 4) }}</li>
            <li><strong>Consistency Index (CI):</strong> {{ number_format($ahp['ci'], 4) }}</li>
            <li><strong>Random Index (RI untuk n={{ $ahp['kriterias']->count() }}):</strong> {{ number_format($ahp['ri'], 2) }}</li>
            <li><strong>Consistency Ratio (CR = CI / RI):</strong>
                <span class="fw-bold {{ $ahp['konsisten'] ? 'text-success' : 'text-danger' }}">
                    {{ number_format($ahp['cr'], 4) }} ({{ $ahp['konsisten'] ? 'KONSISTEN <= 0.10' : 'TIDAK KONSISTEN > 0.10' }})
                </span>
            </li>
        </ul>
    </div>
</div>
@endif
@endsection