@extends('layouts.skote')
@section('title', 'Milestone 05: Hasil Perangkingan & Sensitivitas')

@section('content')
@php
    $alternatifs = \App\Models\Alternatif::with('penilaians')->get();
    $pembagi = [];
    foreach ($kriterias as $k) {
        $vals = $alternatifs->map(fn($a) => (float) ($a->penilaians->firstWhere('kriteria_id', $k->id)->nilai ?? 0));
        $pembagi[$k->id] = max($vals->max(), 0.0001);
    }

    $ranking = $alternatifs->map(function ($alt) use ($kriterias, $pembagi) {
        $pMap = $alt->penilaians->keyBy('kriteria_id');
        $norm = [];
        $skor = 0;
        foreach ($kriterias as $k) {
            $x = (float) ($pMap[$k->id]->nilai ?? 0);
            $r = $x / $pembagi[$k->id];
            $norm[$k->kode] = $r;
            $skor += $r * (float) $k->bobot;
        }
        return [
            'kode'  => $alt->kode,
            'nama'  => $alt->nama,
            'kelas' => $alt->kelas,
            'norm'  => $norm,
            'skor'  => $skor,
        ];
    })->sortByDesc('skor')->values();
@endphp

<!-- Top 3 Kandidat Terbaik -->
<div class="row mb-3">
    @foreach($ranking->take(3) as $idx => $top)
        <div class="col-md-4">
            <div class="card mb-0" style="{{ $idx === 0 ? 'border: 1.5px solid var(--spk-camel) !important;' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge {{ $idx === 0 ? 'bg-dark' : 'bg-light text-dark border' }}">
                            Peringkat #{{ $idx + 1 }}
                        </span>
                        <span class="fw-bold" style="color: var(--spk-camel);">{{ number_format($top['skor'], 4) }}</span>
                    </div>
                    <h6 class="fw-bold mb-1">{{ $top['nama'] }}</h6>
                    <div class="small text-muted">{{ $top['kode'] }} • {{ $top['kelas'] }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Simulator Analisis Sensitivitas Bobot (Ketentuan Evaluasi FP SPK) -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Simulator Analisis Sensitivitas Bobot Kriteria</h5>
            <small class="text-muted">Ubah salah satu bobot untuk mengamati pergeseran peringkat (total bobot otomatis dinormalisasi agar tetap = 1.0000)</small>
        </div>
        <button type="button" class="btn btn-sm btn-outline-editorial" id="btnResetSens">Reset Bobot AHP Asli</button>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($kriterias as $k)
                <div class="col-md">
                    <label class="form-label small fw-bold">{{ $k->kode }} — {{ $k->nama }}</label>
                    <input type="number" step="0.01" min="0.01" max="0.99"
                           class="form-control form-control-sm sens-input"
                           data-kode="{{ $k->kode }}"
                           data-default="{{ number_format($k->bobot, 4, '.', '') }}"
                           value="{{ number_format($k->bobot, 4, '.', '') }}">
                </div>
            @endforeach
        </div>
        <div class="mt-2 small text-muted" id="sensInfo">
            Bobot Aktif Ternormalisasi: <strong>1.0000</strong>
        </div>
    </div>
</div>

<!-- Tabel Perangkingan Lengkap & Export Excel -->
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="card-title mb-0">Hasil Pemeringkatan Akhir Siswa Eligible SNBP (132 Siswa)</h5>
            <small class="text-muted">Urutan berdasarkan nilai preferensi akhir ($V_i$) metode Hybrid AHP–SAW</small>
        </div>
        <div class="d-flex gap-2">
            <select id="filterKelas" class="form-select form-select-sm" style="width: 200px;">
                <option value="">Semua Kelas / Jurusan</option>
                @foreach($daftarKelas as $kelas)
                    <option value="{{ $kelas }}">{{ $kelas }}</option>
                @endforeach
            </select>
            <a href="{{ route('spk.hasil.export') }}" class="btn btn-primary btn-sm text-nowrap">
                <i class="bx bxs-file-export me-1"></i> Export Laporan Excel (4 Sheet)
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle" id="tabelHasil">
                <thead>
                    <tr>
                        <th width="8%" class="text-center">Rank Asli</th>
                        <th width="10%" class="text-center">Rank Simulasi</th>
                        <th width="9%">Kode</th>
                        <th>Nama Siswa</th>
                        <th width="15%">Kelas</th>
                        <th width="13%" class="text-end">Skor Asli ($V_i$)</th>
                        <th width="13%" class="text-end">Skor Simulasi</th>
                    </tr>
                </thead>
                <tbody id="bodyHasil"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const rawRanking = @json($ranking);

function renderRanking() {
    const inputs = document.querySelectorAll('.sens-input');
    let rawWeights = {};
    let sumW = 0;

    inputs.forEach(inp => {
        const val = Math.max(parseFloat(inp.value) || 0, 0.0001);
        rawWeights[inp.dataset.kode] = val;
        sumW += val;
    });

    let normWeights = {};
    let labelParts = [];
    for (const [kode, val] of Object.entries(rawWeights)) {
        normWeights[kode] = val / sumW;
        labelParts.push(`${kode}: ${(normWeights[kode] * 100).toFixed(1)}%`);
    }

    document.getElementById('sensInfo').innerHTML =
        `Bobot Ternormalisasi (W = 1.0000$): <strong>${labelParts.join(' | ')}</strong>`;

    // Hitung skor simulasi
    const simulated = rawRanking.map((item, idx) => {
        let simScore = 0;
        for (const [kode, w] of Object.entries(normWeights)) {
            simScore += (item.norm[kode] || 0) * w;
        }
        return { ...item, origRank: idx + 1, simScore };
    });

    const sortedSim = [...simulated].sort((a, b) => b.simScore - a.simScore);
    const simRankMap = {};
    sortedSim.forEach((item, idx) => { simRankMap[item.kode] = idx + 1; });

    const selectedKelas = document.getElementById('filterKelas').value;
    const tbody = document.getElementById('bodyHasil');
    tbody.innerHTML = '';

    simulated.forEach(item => {
        if (selectedKelas && item.kelas !== selectedKelas) return;
        const sRank = simRankMap[item.kode];
        const diff = item.origRank - sRank;
        const diffBadge = diff > 0
            ? `<span class="badge bg-success ms-1">▲${diff}</span>`
            : (diff < 0 ? `<span class="badge bg-danger ms-1">▼${Math.abs(diff)}</span>` : '');

        tbody.innerHTML += `
            <tr>
                <td class="text-center fw-bold">#${item.origRank}</td>
                <td class="text-center">#${sRank} ${diffBadge}</td>
                <td class="fw-bold">${item.kode}</td>
                <td>${item.nama}</td>
                <td><span class="badge bg-light text-dark border">${item.kelas}</span></td>
                <td class="text-end fw-bold">${item.skor.toFixed(4)}</td>
                <td class="text-end" style="color: var(--spk-camel); font-weight: 600;">${item.simScore.toFixed(4)}</td>
            </tr>
        `;
    });
}

document.querySelectorAll('.sens-input').forEach(el => el.addEventListener('input', renderRanking));
document.getElementById('filterKelas').addEventListener('change', renderRanking);
document.getElementById('btnResetSens').addEventListener('click', () => {
    document.querySelectorAll('.sens-input').forEach(el => { el.value = el.dataset.default; });
    renderRanking();
});

renderRanking();
</script>
@endpush