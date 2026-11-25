@extends('layouts.skote')
@section('title', 'Milestone 03: Data & Penilaian Alternatif')

@section('content')
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card mb-0">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">Total Siswa Terdaftar</div>
                <div class="fs-3 fw-bold mt-1">{{ $alternatifs->count() }} <span class="fs-6 fw-normal text-muted">Siswa (Min. 30)</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card mb-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <div class="fw-bold">Import &amp; Lengkapi Nilai Rapor (C1), Kejuruan (C2), &amp; TKA (C5)</div>
                    <small class="text-muted">Unduh template yang sudah memuat 132 nama siswa + skor terverifikasi C3 &amp; C4, isi nilai rapor, lalu unggah kembali.</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('spk.alternatif.template') }}" class="btn btn-outline-editorial btn-sm">
                        <i class="bx bx-download me-1"></i> Download Template (.xlsx)
                    </a>
                    <form action="{{ route('spk.alternatif.import') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2">
                        @csrf
                        <input type="file" name="file_excel" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                        <button type="submit" class="btn btn-primary btn-sm text-nowrap">
                            <i class="bx bx-upload me-1"></i> Import Excel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Matriks Data Alternatif Siswa SMKN 8 Surabaya</h5>
            <small class="text-muted">Pastikan seluruh kolom C1, C2, dan C5 telah terisi sebelum menekan tombol Validasi</small>
        </div>
        <form action="{{ route('spk.alternatif.validasi') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-camel px-4">
                <i class="bx bx-check-shield me-1"></i> Validasi Data &amp; Buka Milestone 04
            </button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="max-height: 540px;">
            <table class="table table-bordered table-sm align-middle">
                <thead class="sticky-top">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        @foreach($kriterias as $k)
                            <th class="text-center">{{ $k->kode }}</th>
                        @endforeach
                        <th>Keterangan Sertifikat C3 &amp; C4 Terverifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alternatifs as $alt)
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
                            <td class="small text-muted">
                                @if($alt->ket_c3 !== '-') <div><strong>Akademik:</strong> {{ $alt->ket_c3 }}</div> @endif
                                @if($alt->ket_c4 !== '-') <div><strong>Non-Akademik:</strong> {{ $alt->ket_c4 }}</div> @endif
                                @if($alt->ket_c3 === '-' && $alt->ket_c4 === '-') - @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection