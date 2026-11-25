@extends('layouts.skote')
@section('title', 'Milestone 01: Definisi Kriteria')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">Daftar Kriteria Seleksi Eligible SNBP</h5>
                    <small class="text-muted">Syarat Gatekeeper: Minimal 5 kriteria terdefinisi (Regulasi Kepmendikdasmen No. 102/M/2025)</small>
                </div>
                <span class="badge bg-dark px-3 py-2">{{ $kriterias->count() }} / 5 Kriteria</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="10%">Kode</th>
                                <th>Nama Kriteria</th>
                                <th width="15%">Atribut</th>
                                <th width="20%">Bobot AHP</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kriterias as $k)
                                <tr>
                                    <td class="fw-bold">{{ $k->kode }}</td>
                                    <td>{{ $k->nama }}</td>
                                    <td>
                                        <span class="badge {{ $k->atribut === 'benefit' ? 'bg-success' : 'bg-danger' }} text-uppercase">
                                            {{ $k->atribut }}
                                        </span>
                                    </td>
                                    <td>{{ $k->bobot ? number_format($k->bobot * 100, 2) . '%' : 'Belum dihitung' }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('spk.kriteria.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Hapus kriteria ini? Status milestone setelahnya akan dikunci ulang.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada data kriteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        @if($state->m1_kriteria_valid)
                            <span class="text-success fw-bold"><i class="bx bx-check-circle"></i> Syarat Milestone 01 terpenuhi.</span> Anda dapat melanjutkan ke Pembobotan AHP.
                        @else
                            <span class="text-danger fw-bold"><i class="bx bx-lock"></i> Tambahkan minimal {{ 5 - $kriterias->count() }} kriteria lagi</span> untuk membuka Milestone 02.
                        @endif
                    </div>

                    @if($state->m1_kriteria_valid)
                        <a href="{{ route('spk.ahp') }}" class="btn btn-primary px-4">
                            Lanjut ke Milestone 02 (Pembobotan AHP) <i class="bx bx-right-arrow-alt ms-1"></i>
                        </a>
                    @else
                        <button class="btn btn-secondary" disabled><i class="bx bx-lock-alt me-1"></i> Milestone 02 Terkunci</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tambah Kriteria Baru</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('spk.kriteria.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kode Kriteria</label>
                        <input type="text" name="kode" class="form-control" placeholder="Contoh: C6" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Kriteria</label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama indikator penilaian" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Jenis Atribut</label>
                        <select name="atribut" class="form-select" required>
                            <option value="benefit">Benefit (Semakin besar semakin baik)</option>
                            <option value="cost">Cost (Semakin kecil semakin baik)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Simpan Kriteria</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection