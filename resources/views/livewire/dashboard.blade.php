{{-- File: resources/views/livewire/dashboard.blade.php --}}
<div class="container-fluid py-4">

    {{-- Alert untuk Profil Belum Lengkap --}}
    @if (Auth::check() && !$isProfileComplete)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning text-white" role="alert">
                <h4 class="alert-heading mt-1 text-white">Perhatian! Profil Belum Lengkap</h4>
                <p>Untuk memaksimalkan peluang Anda dan dapat melanjutkan ke tahap asesmen, mohon pastikan semua informasi pada halaman profil Anda telah diisi dengan lengkap dan akurat.</p>
                <hr class="horizontal light">
                <p class="mb-0">
                    Silakan kunjungi halaman profil Anda untuk melengkapi informasi yang dibutuhkan.
                    <a href="{{ route('profile') }}" class="btn btn-sm btn-light mb-0 ms-2">Lengkapi Profil Sekarang</a>
                </p>
            </div>
        </div>
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-body p-3">
            <div class="row">
                <div class="col-8">
                    <div class="numbers">
                        <p class="text-sm mb-0 text-capitalize font-weight-bold">Selamat Datang, {{ $userName }}!</p>
                        <h5 class="font-weight-bolder mb-0">
                            Progres Asesmen Anda
                        </h5>
                        <p class="text-sm mt-1">{{ $progressMessage }}</p>
                    </div>
                </div>
                <div class="col-4 text-end">
                    <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                        <i class="material-icons text-lg opacity-10" aria-hidden="true">checklist</i>
                    </div>
                </div>
            </div>
            @if ($totalTestsCount > 0)
            <div class="progress-wrapper mt-3">
                <div class="progress-info">
                    <div class="progress-percentage">
                        <span class="text-xs font-weight-bold">{{ number_format($completionPercentage, 0) }}% Selesai</span>
                    </div>
                </div>
                <div class="progress">
                    <div class="progress-bar bg-gradient-info" role="progressbar" aria-valuenow="{{ $completionPercentage }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $completionPercentage }}%;"></div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="row">
        @if (!empty($testsList))
            <div class="col-12 mb-4">
                <h5 class="font-weight-bolder">Asesmen Anda:</h5>
            </div>

            @foreach ($testsList as $test)
            <div class="col-md-12 mb-4">
                {{-- Card background changes if test is locked (not startable and not completed) --}}
                <div class="card @if($test['display_status_text'] === 'Terkunci' || ($test['status_internal'] === 'not_started' && !$test['can_start'])) bg-light @endif">
                    <div class="card-body p-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            {{-- Kolom Kiri: Ikon, Nama Tes, Deskripsi, Waktu --}}
                            <div class="mb-3 mb-md-0">
                                <div class="d-flex align-items-center mb-1">
                                     <div class="icon icon-sm icon-shape text-center border-radius-md me-2 shadow-sm
                                        @if ($test['status_internal'] === 'completed') bg-gradient-success
                                        @elseif ($test['status_internal'] === 'in_progress') bg-gradient-warning
                                        @elseif ($test['status_internal'] === 'not_started' && $test['can_start']) bg-gradient-info
                                        @else bg-gradient-secondary @endif">
                                        <i class="material-icons opacity-10">{{ $test['icon'] }}</i>
                                    </div>
                                    {{-- Test name is muted if locked --}}
                                    <h6 class="font-weight-bolder mb-0 @if($test['display_status_text'] === 'Terkunci' || ($test['status_internal'] === 'not_started' && !$test['can_start'])) text-muted @endif">{{ $test['name'] }}</h6>
                                </div>
                                {{-- Test Description --}}
                                <p class="text-xs text-secondary mb-2 mt-4">{{ $test['description'] }}</p>
                                {{-- Test Time Limit --}}
                                <p class="text-xs text-muted mb-0"><i class="material-icons text-sm align-middle">timer</i> Batas Waktu: {{ $test['time_limit_minutes'] }} menit</p>
                            </div>

                            {{-- Kolom Kanan: Status Teks atau Tombol Aksi --}}
                            <div class="text-md-end">
                                @if ($test['status_internal'] === 'completed')
                                    <span class="badge badge-sm bg-gradient-success me-2">
                                        <i class="material-icons text-sm me-1 align-middle">check_circle</i>
                                        {{ $test['display_status_text'] }}
                                    </span>

                                @elseif ($test['status_internal'] === 'in_progress')
                                    @if ($test['can_start'])
                                        <span class="badge badge-sm bg-gradient-warning me-2">
                                            <i class="material-icons text-sm me-1 align-middle">hourglass_top</i>
                                            Sedang Dikerjakan
                                        </span>
                                        <a href="{{ route('candidate.assessment.test') }}"
                                           class="btn btn-sm mb-0 {{ $test['action_button_class'] }}">
                                            {{ $test['action_button_text'] }}
                                            <i class="material-icons text-sm ms-1">arrow_forward</i>
                                        </a>
                                    @else
                                        <span class="font-weight-bold text-sm text-danger">
                                            <i class="material-icons text-sm me-1 align-middle">error_outline</i>
                                            {{ $test['display_status_text'] }}
                                        </span>
                                    @endif

                                @elseif ($test['status_internal'] === 'not_started')
                                    @if ($test['can_start'])
                                        <a href="{{ route('candidate.assessment.test') }}"
                                           class="btn btn-sm mb-0 {{ $test['action_button_class'] }}">
                                            {{ $test['action_button_text'] }}
                                            <i class="material-icons text-sm ms-1">arrow_forward</i>
                                        </a>
                                    @else
                                        <span class="badge badge-sm bg-gradient-secondary me-2">
                                            <i class="material-icons text-sm me-1 align-middle">lock</i>
                                            {{ $test['display_status_text'] }}
                                        </span>
                                        @if($test['prerequisite_message'])
                                            <p class="text-xs text-danger mt-1 mb-0"><small>{{ $test['prerequisite_message'] }}</small></p>
                                        @endif
                                    @endif
                                @else
                                    <span class="font-weight-bold text-sm text-dark">
                                        <i class="material-icons text-sm me-1 align-middle">help_outline</i>
                                        {{ $test['display_status_text'] }}
                                    </span>
                                     @if($test['prerequisite_message'])
                                        <p class="text-xs text-danger mt-1 mb-0"><small>{{ $test['prerequisite_message'] }}</small></p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            @if($totalTestsCount === 0 && Auth::check())
            <div class="col-12">
                <p class="text-center text-secondary">{{ $progressMessage }}</p>
            </div>
            @elseif(!Auth::check())
             <div class="col-12">
                <p class="text-center text-secondary">{{ $progressMessage }}</p>
            </div>
            @endif
        @endif
    </div>
</div>