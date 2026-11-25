@extends('layouts.app')
@section('content')
<h3 class="fw-bold mb-3">1. Data Kriteria</h3>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">Tambah Kriteria Baru</div>
    <div class="card-body">
        <form action="{{ route('kriteria.store') }}" method="POST" class="row g-2">
            @csrf
            <div class="col-md-2"><input type="text" name="kode" class="form-control" placeholder="Kode (C6)" required></div>
            <div class="col-md-5"><input type="text" name="nama" class="form-control" placeholder="Nama Kriteria" required></div>
            <div class="col-md-3">
                <select name="atribut" class="form-select">
                    <option value="benefit">Benefit</option>
                    <option value="cost">Cost</option>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr><th>Kode</th><th>Nama Kriteria</th><th>Atribut</th><th>Bobot AHP (W)</th><th>Persentase</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @foreach($kriterias as $k)
                <tr>
                    <td class="fw-bold">{{ $k->kode }}</td>
                    <td>{{ $k->nama }}</td>
                    <td><span class="badge bg-info text-dark text-uppercase">{{ $k->atribut }}</span></td>
                    <td>{{ number_format($k->bobot, 6) }}</td>
                    <td>{{ number_format($k->bobot * 100, 2) }}%</td>
                    <td>
                        <form action="{{ route('kriteria.destroy', $k) }}" method="POST" onsubmit="return confirm('Hapus kriteria ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="3" class="text-end">Total Bobot</td>
                    <td>{{ number_format($kriterias->sum('bobot'), 6) }}</td>
                    <td>{{ number_format($kriterias->sum('bobot') * 100, 2) }}%</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection