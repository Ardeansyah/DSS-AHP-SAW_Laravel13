@extends('layouts.app')
@section('content')
<h3 class="fw-bold mb-3">5. Tahapan Perhitungan & Hasil Perangkingan SAW</h3>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">Tahap 1: Matriks Keputusan Awal (X)</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-sm text-center align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode</th><th class="text-start">Nama Siswa</th>
                    @foreach($saw['kriterias'] as $k) <th>{{ $k->kode }} ({{ $k->atribut }})</th> @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($saw['alternatifs'] as $alt)
                <tr>
                    <td>{{ $alt->kode }}</td><td class="text-start">{{ $alt->nama }}</td>
                    @foreach($saw['kriterias'] as $k)
                        <td>{{ number_format($saw['matriks_x'][$alt->id][$k->id], 2) }}</td>
                    @endforeach
                </tr>
                @endforeach
                <tr class="table-warning fw-bold">
                    <td colspan="2" class="text-end">Nilai Max / Min Kriteria</td>
                    @foreach($saw['kriterias'] as $k)
                        <td>{{ number_format($k->atribut === 'benefit' ? $saw['max_kriteria'][$k->id] : $saw['min_kriteria'][$k->id], 2) }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">Tahap 2: Matriks Ternormalisasi (R)</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-sm text-center align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode</th><th class="text-start">Nama Siswa</th>
                    @foreach($saw['kriterias'] as $k) <th>{{ $k->kode }}</th> @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($saw['alternatifs'] as $alt)
                <tr>
                    <td>{{ $alt->kode }}</td><td class="text-start">{{ $alt->nama }}</td>
                    @foreach($saw['kriterias'] as $k)
                        <td>{{ number_format($saw['matriks_r'][$alt->id][$k->id], 4) }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold">Tahap 3: Matriks Terbobot (W x R) & Peringkat Akhir (V)</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-sm text-center align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Peringkat</th><th>Kode</th><th class="text-start">Nama Siswa</th><th>Kelas</th>
                    @foreach($saw['kriterias'] as $k) <th>{{ $k->kode }} (W={{ number_format($saw['bobot_aktif'][$k->id], 3) }})</th> @endforeach
                    <th class="bg-primary text-white">Nilai Preferensi (V)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saw['peringkat'] as $row)
                @php $alt = $row['alternatif']; @endphp
                <tr>
                    <td class="fw-bold">#{{ $row['peringkat'] }}</td>
                    <td>{{ $alt->kode }}</td>
                    <td class="text-start">{{ $alt->nama }}</td>
                    <td>{{ $alt->kelas }}</td>
                    @foreach($saw['kriterias'] as $k)
                        <td>{{ number_format($saw['matriks_terbobot'][$alt->id][$k->id], 4) }}</td>
                    @endforeach
                    <td class="fw-bold bg-light">{{ number_format($row['nilai_v'], 6) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection