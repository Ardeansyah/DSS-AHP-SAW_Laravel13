@extends('layouts.app')
@section('content')
<h3 class="fw-bold mb-3">3. Data Alternatif (Siswa SMKN 8 Surabaya)</h3>

<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold">Tambah Siswa Manual</div>
            <div class="card-body">
                <form action="{{ route('alternatif.store') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-3"><input type="text" name="kode" class="form-control" placeholder="Kode (A11)" required></div>
                    <div class="col-md-5"><input type="text" name="nama" class="form-control" placeholder="Nama Siswa" required></div>
                    <div class="col-md-4"><input type="text" name="kelas" class="form-control" placeholder="Kelas (XII DKV 1)"></div>
                    <div class="col-12 text-end"><button class="btn btn-primary btn-sm">Tambah Siswa</button></div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold">Import Massal via CSV</div>
            <div class="card-body">
                <form action="{{ route('alternatif.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file_csv" class="form-control form-control-sm mb-2" accept=".csv,.txt" required>
                    <small class="text-muted d-block mb-2">Urutan kolom CSV: <code>kode,nama,kelas,c1,c2,c3,c4,c5</code></small>
                    <button class="btn btn-success btn-sm">Upload & Import CSV</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>No</th><th>Kode</th><th>Nama Siswa</th><th>Kelas</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @foreach($alternatifs as $idx => $alt)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="fw-bold">{{ $alt->kode }}</td>
                    <td>{{ $alt->nama }}</td>
                    <td>{{ $alt->kelas }}</td>
                    <td>
                        <form action="{{ route('alternatif.destroy', $alt) }}" method="POST" onsubmit="return confirm('Hapus siswa ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection