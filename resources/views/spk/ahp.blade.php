@extends('layouts.skote')
@section('title', 'Milestone 02: Pembobotan AHP')

@section('content')
@php
    $comparisons = \App\Models\AhpComparison::all()->keyBy(fn($c) => $c->kriteria_1_id . '-' . $c->kriteria_2_id);
    $saatyOptions = [
        '9'      => '9 — Mutlak lebih penting',
        '7'      => '7 — Sangat jelas lebih penting',
        '5'      => '5 — Jelas lebih penting',
        '3'      => '3 — Sedikit lebih penting',
        '1'      => '1 — Sama penting',
        '0.3333' => '1/3 — Sedikit kurang penting',
        '0.2'    => '1/5 — Jelas kurang penting',
        '0.1429' => '1/7 — Sangat jelas kurang penting',
        '0.1111' => '1/9 — Mutlak kurang penting',
    ];
    $cr = $hasilAhp['cr'] ?? 1;
    $isKonsisten = $cr <= 0.10;
@endphp

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">Matriks Perbandingan Berpasangan (Skala Saaty 1–9)</h5>
                    <small class="text-muted">Ubah sel segitiga atas; nilai kebalikan ($1/x$) pada segitiga bawah diperbarui otomatis</small>
                </div>
                <span class="badge {{ $isKonsisten ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                    CR = {{ number_format($cr, 4) }} ({{ $isKonsisten ? 'KONSISTEN ≤ 0.10' : 'TIDAK KONSISTEN > 0.10' }})
                </span>
            </div>
            <div class="card-body">
                <form action="{{ route('spk.ahp.hitung') }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead>
                                <tr>
                                    <th class="text-start">Kriteria</th>
                                    @foreach($kriterias as $col)
                                        <th>{{ $col->kode }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kriterias as $i => $row)
                                    <tr>
                                        <th class="text-start bg-light">{{ $row->kode }} — {{ $row->nama }}</th>
                                        @foreach($kriterias as $j => $col)
                                            @if($i === $j)
                                                <td class="bg-light fw-bold">1</td>
                                            @elseif($i < $j)
                                                @php
                                                    $savedVal = (float) ($comparisons[$row->id . '-' . $col->id]->nilai ?? 1);
                                                @endphp
                                                <td>
                                                    <select name="matrix[{{ $row->id }}][{{ $col->id }}]"
                                                            class="form-select form-select-sm ahp-select"
                                                            data-row="{{ $row->id }}" data-col="{{ $col->id }}">
                                                        @foreach($saatyOptions as $val => $label)
                                                            <option value="{{ $val }}" {{ abs($savedVal - (float)$val) < 0.01 ? 'selected' : '' }}>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            @else
                                                @php
                                                    $upperVal = (float) ($comparisons[$col->id . '-' . $row->id]->nilai ?? 1);
                                                    $invVal = $upperVal > 0 ? 1 / $upperVal : 1;
                                                @endphp
                                                <td class="text-muted bg-light" id="inv-{{ $row->id }}-{{ $col->id }}">
                                                    {{ number_format($invVal, 4) }}
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bx bx-calculator me-1"></i> Hitung Ulang &amp; Validasi Konsistensi AHP
                        </button>

                        @if($isKonsisten)
                            <a href="{{ route('spk.alternatif') }}" class="btn btn-camel px-4">
                                Lanjut ke Milestone 03 (Data Siswa) <i class="bx bx-right-arrow-alt ms-1"></i>
                            </a>
                        @else
                            <button type="button" class="btn btn-secondary" disabled>
                                <i class="bx bx-lock-alt me-1"></i> Milestone 03 Terkunci (CR &gt; 0.10)
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Hasil Vektor Bobot &amp; Uji Konsistensi</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-bordered mb-3">
                    <thead>
                        <tr>
                            <th>Kriteria</th>
                            <th class="text-end">Bobot ($w_j$)</th>
                            <th class="text-end">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kriterias as $k)
                            @php $b = $hasilAhp['weights'][$k->id] ?? $k->bobot ?? 0; @endphp
                            <tr>
                                <td class="fw-bold">{{ $k->kode }}</td>
                                <td class="text-end">{{ number_format($b, 4) }}</td>
                                <td class="text-end fw-bold" style="color: var(--spk-camel);">{{ number_format($b * 100, 2) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-3 rounded border" style="background: var(--spk-canvas);">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Eigen Value Maksimum ($\lambda_{\max}$):</span>
                        <strong>{{ number_format($hasilAhp['lambda_max'] ?? 0, 4) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Consistency Index (CI):</span>
                        <strong>{{ number_format($hasilAhp['ci'] ?? 0, 4) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Random Index (RI, n={{ $kriterias->count() }}):</span>
                        <strong>{{ number_format($hasilAhp['ri'] ?? 1.12, 2) }}</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Consistency Ratio (CR):</span>
                        <span class="fw-bold {{ $isKonsisten ? 'text-success' : 'text-danger' }}">
                            {{ number_format($cr, 4) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.ahp-select').forEach(select => {
    select.addEventListener('change', function () {
        const r = this.dataset.row;
        const c = this.dataset.col;
        const val = parseFloat(this.value);
        const invCell = document.getElementById(`inv-${c}-${r}`);
        if (invCell && val > 0) {
            invCell.textContent = (1 / val).toFixed(4);
        }
    });
});
</script>
@endpush