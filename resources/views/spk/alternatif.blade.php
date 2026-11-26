@extends('layouts.skote')
@section('title', 'Milestone 03: Data & Penilaian Alternatif')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-4">
        <div class="card h-100 mb-0">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="text-muted small text-uppercase fw-bold">Total Alternatif</div>
                <div class="fs-3 fw-bold mt-1">
                    {{ $alternatifs->count() }}
                    <span class="fs-6 fw-normal text-muted">/ min. 30</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-9 col-md-8">
        <div class="card h-100 mb-0">
            <div class="card-body d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                <div>
                    <div class="fw-bold">Import Data Alternatif &amp; Nilai Kriteria</div>
                    <small class="text-muted">Unduh contoh format Excel (.xlsx), isi seluruh data alternatif &amp; nilai kriteria, lalu unggah kembali.</small>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('spk.alternatif.template') }}" class="btn btn-outline-editorial btn-sm text-nowrap">
                        <i class="bx bx-download me-1"></i> Contoh Template (.xlsx)
                    </a>

                    <form action="{{ route('spk.alternatif.import') }}" method="POST" enctype="multipart/form-data" class="m-0">
                        @csrf
                        <div class="input-group input-group-sm" style="min-width: 310px;">
                            <input type="file" name="file_excel" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <button type="submit" class="btn btn-primary text-nowrap">
                                <i class="bx bx-upload me-1"></i> Import Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="card-title mb-0">Matriks Data Alternatif &amp; Penilaian</h5>
            <small class="text-muted">Pastikan seluruh nilai kriteria (C1–C5) terisi lengkap sebelum melanjutkan ke perhitungan SAW</small>
        </div>
        <form action="{{ route('spk.alternatif.validasi') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-camel px-4">
                <i class="bx bx-check-shield me-1"></i> Validasi Data &amp; Buka Milestone 04
            </button>
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive" style="max-height: 540px;">
            <table class="table table-bordered table-hover table-sm align-middle mb-0">
                <thead class="sticky-top">
                    <tr>
                        <th width="9%">Kode</th>
                        <th>Nama Alternatif</th>
                        <th width="18%">Kelas / Kategori</th>
                        @foreach($kriterias as $k)
                            <th width="10%" class="text-center">{{ $k->kode }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($alternatifs as $alt)
                        @php $pMap = $alt->penilaians->keyBy('kriteria_id'); @endphp
                        <tr>
                            <td class="fw-bold">{{ $alt->kode }}</td>
                            <td>{{ $alt->nama }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $alt->kelas }}</span></td>
                            @foreach($kriterias as $k)
                                @php $val = (float) ($pMap[$k->id]->nilai ?? 0); @endphp
                                <td class="text-center {{ in_array($k->kode, ['C1','C2','C5']) && $val <= 0 ? 'table-warning fw-bold' : '' }}">
                                    {{ number_format($val, in_array($k->kode, ['C3','C4']) ? 0 : 2) }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + $kriterias->count() }}" class="text-center text-muted py-4">
                                Belum ada data alternatif. Silakan unggah file Excel menggunakan tombol Import di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection