@extends('layouts.app')
@section('content')
<h3 class="fw-bold mb-3">4. Input Penilaian Alternatif (Matriks Keputusan X)</h3>

<form action="{{ route('penilaian.store') }}" method="POST">
    @csrf
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-bold">Tabel Nilai Siswa per Kriteria</span>
            <button type="submit" class="btn btn-primary btn-sm">Simpan Semua Nilai</button>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Siswa</th>
                        @foreach($kriterias as $k)
                            <th>{{ $k->kode }} <br><small class="text-muted fw-normal">{{ $k->nama }}</small></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($alternatifs as $alt)
                    <tr>
                        <td class="fw-bold">{{ $alt->kode }}</td>
                        <td>{{ $alt->nama }}</td>
                        @foreach($kriterias as $k)
                            @php $val = $penilaians[$alt->id . '-' . $k->id]->nilai ?? 0; @endphp
                            <td>
                                <input type="number" step="0.01" min="0"
                                       name="nilai[{{ $alt->id }}][{{ $k->id }}]"
                                       value="{{ $val }}" class="form-control form-control-sm">
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</form>
@endsection