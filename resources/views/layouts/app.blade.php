<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK AHP-SAW | Seleksi Siswa Eligible SNBP SMKN 8 Surabaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1e293b; color: #fff; }
        .sidebar a { color: #cbd5e1; text-decoration: none; display: block; padding: 10px 16px; border-radius: 6px; margin-bottom: 4px; }
        .sidebar a:hover, .sidebar a.active { background: #3b82f6; color: #fff; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block sidebar p-3">
            <h5 class="fw-bold mb-1">SPK AHP - SAW</h5>
            <small class="text-secondary d-block mb-4">SNBP SMKN 8 Surabaya</small>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('kriteria.index') }}" class="{{ request()->routeIs('kriteria.*') ? 'active' : '' }}">1. Data Kriteria</a>
            <a href="{{ route('ahp.index') }}" class="{{ request()->routeIs('ahp.*') ? 'active' : '' }}">2. Pembobotan AHP</a>
            <a href="{{ route('alternatif.index') }}" class="{{ request()->routeIs('alternatif.*') ? 'active' : '' }}">3. Data Alternatif</a>
            <a href="{{ route('penilaian.index') }}" class="{{ request()->routeIs('penilaian.*') ? 'active' : '' }}">4. Input Penilaian</a>
            <a href="{{ route('perhitungan.index') }}" class="{{ request()->routeIs('perhitungan.*') ? 'active' : '' }}">5. Perhitungan & Hasil SAW</a>
            <a href="{{ route('sensitivitas.index') }}" class="{{ request()->routeIs('sensitivitas.*') ? 'active' : '' }}">6. Analisis Sensitivitas</a>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>