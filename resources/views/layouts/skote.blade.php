<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'SPK SNBP SMKN 8 Surabaya') — AHP &amp; SAW</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5, Boxicons & Editorial Theme -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/editorial-skote.css') }}?v=3" rel="stylesheet" type="text/css" />
    @stack('styles')
</head>

<body data-sidebar="light">
@php
    $milestones = app(\App\Services\MilestoneGatekeeper::class)->getMilestones(Route::currentRouteName() ?? '');
    $completedCount = collect($milestones)->where('completed', true)->count();
    $stepUrls = [
        1 => url('/spk/kriteria'),
        2 => url('/spk/ahp'),
        3 => url('/spk/alternatif'),
        4 => url('/spk/perhitungan'),
        5 => url('/spk/hasil'),
    ];
@endphp

<div id="layout-wrapper">
    <!-- Topbar -->
    <header id="page-topbar" class="sticky-top bg-white border-bottom px-4">
        <div class="w-100 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ url('/spk/kriteria') }}" class="text-decoration-none text-dark fw-bold fs-5">
                    SPK <span style="color: var(--spk-camel);">SMKN 8</span>
                </a>
                <span class="badge border text-dark bg-light fw-normal px-2 py-1 d-none d-md-inline-block">
                    Seleksi Siswa Eligible SNBP • Hybrid AHP–SAW
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Progres Tahapan:</span>
                <span class="badge bg-dark px-2 py-1">{{ $completedCount }} / 5 Milestone</span>
            </div>
             <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small d-none d-sm-inline">Progres Tahapan:</span>
                    <span class="badge bg-dark px-2 py-1">{{ $completedCount }} / 5 Milestone</span>
                </div>

                <form action="{{ route('spk.reset') }}" method="POST" class="m-0"
                      onsubmit="return confirm('Mulai dari awal? Seluruh data kriteria, bobot AHP, dan data alternatif saat ini akan dihapus.');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" title="Reset seluruh tahapan ke awal">
                        <i class="bx bx-reset"></i>
                        <span class="d-none d-md-inline">Mulai dari Awal</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Left Sidebar -->
            <nav class="col-lg-2 col-md-3 bg-white border-end min-vh-100 py-4 px-3">
                <div class="text-uppercase text-muted fw-bold mb-3 px-2" style="font-size: 11px; letter-spacing: 0.8px;">
                    Alur Keputusan (Milestone)
                </div>
                <ul class="nav flex-column gap-1">
                    @foreach($milestones as $step => $m)
                        <li class="nav-item">
                            @if($m['unlocked'])
                                <a href="{{ $stepUrls[$step] }}"
                                   class="nav-link rounded d-flex justify-content-between align-items-center py-2 px-2 {{ $m['active'] ? 'fw-bold text-dark' : 'text-secondary' }}"
                                   style="{{ $m['active'] ? 'background: var(--spk-camel-soft); border-left: 3px solid var(--spk-camel);' : '' }}">
                                    <span>
                                        <i class="bx {{ $m['completed'] ? 'bx-check-circle text-success' : 'bx-radio-circle-marked' }} me-1"></i>
                                        {{ $m['number'] }}. {{ $m['title'] }}
                                    </span>
                                    @if($m['completed'])
                                        <span class="badge rounded-pill" style="background: var(--spk-sage-soft); color: var(--spk-sage); font-size: 10px;">Selesai</span>
                                    @endif
                                </a>
                            @else
                                <a href="javascript:void(0);"
                                   class="nav-link d-flex justify-content-between align-items-center py-2 px-2 text-muted opacity-50"
                                   style="cursor: not-allowed;"
                                   title="{{ $m['lock_msg'] }}">
                                    <span>
                                        <i class="bx bx-lock-alt me-1"></i>
                                        {{ $m['number'] }}. {{ $m['title'] }}
                                    </span>
                                    <span class="badge bg-light text-muted border" style="font-size: 10px;">Terkunci</span>
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-lg-10 col-md-9 py-4 px-4">
                <!-- 5-Stage Horizontal Milestone Progress Stepper -->
                <div class="milestone-bar">
                    @foreach($milestones as $step => $m)
                        @php
                            $stateClass = !$m['unlocked'] ? 'locked' : ($m['active'] ? 'active' : ($m['completed'] ? 'completed' : ''));
                        @endphp
                        <a href="{{ $m['unlocked'] ? $stepUrls[$step] : '#' }}" class="milestone-step {{ $stateClass }}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="milestone-badge">Milestone {{ $m['number'] }}</span>
                                @if(!$m['unlocked'])
                                    <i class="bx bx-lock-alt fs-5 text-muted"></i>
                                @elseif($m['completed'])
                                    <i class="bx bxs-check-circle fs-5" style="color: var(--spk-sage);"></i>
                                @else
                                    <i class="bx bx-loader-circle fs-5" style="color: var(--spk-camel);"></i>
                                @endif
                            </div>
                            <div class="fw-bold fs-6">{{ $m['title'] }}</div>
                            <div class="small text-muted mt-1">{{ $m['subtitle'] }}</div>
                        </a>
                    @endforeach
                </div>

                <!-- Flash Alerts -->
                @if(session('success'))
                    <div class="alert border-0 d-flex align-items-center justify-content-between" style="background: var(--spk-sage-soft); color: var(--spk-sage);">
                        <div><i class="bx bx-check-circle me-2 fs-5 align-middle"></i> {{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert border-0 d-flex align-items-center justify-content-between" style="background: var(--spk-crimson-soft); color: var(--spk-crimson);">
                        <div><i class="bx bx-lock-alt me-2 fs-5 align-middle"></i> {{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')

                <footer class="mt-5 pt-3 border-top d-flex justify-content-between text-muted small">
                    <span>SPK Pemilihan Siswa Eligible SNBP — SMKN 8 Surabaya (Kepmendikdasmen No. 102/M/2025)</span>
                    <span>Metode Integrasi AHP &amp; SAW</span>
                </footer>
            </main>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>