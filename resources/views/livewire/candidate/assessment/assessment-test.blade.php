<div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12 mb-4">
                <h4 class="font-weight-bolder">Asesmen Anda</h4>
                <p class="text-secondary">Selesaikan semua asesmen untuk mendapatkan hasil yang komprehensif dan rekomendasi yang lebih akurat.</p>
            </div>
        </div>
        @if (session()->has('info'))
            <div class="alert alert-info text-white alert-dismissible fade show" role="alert">
                <span class="alert-icon"><i class="material-icons text-sm">info</i></span>
                <span class="alert-text"><strong>Info!</strong> {{ session('info') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger text-white alert-dismissible fade show" role="alert">
                <span class="alert-icon"><i class="material-icons text-sm">error_outline</i></span>
                <span class="alert-text"><strong>Error!</strong> {{ session('error') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
         @if (session()->has('success'))
            <div class="alert alert-success text-white alert-dismissible fade show" role="alert">
                <span class="alert-icon"><i class="material-icons text-sm">check_circle</i></span>
                <span class="alert-text"><strong>Sukses!</strong> {{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            @if (empty($test_statuses_list))
                <div class="col-12">
                    <p class="text-center text-secondary">Tidak ada tes yang tersedia saat ini.</p>
                </div>
            @else
                @foreach ($test_statuses_list as $item)
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card h-100 card-hoverable d-flex flex-column @if(!$item['can_start'] && $item['status'] === 'not_started') bg-light-subtle @else bg-white @endif">
                            <div class="card-header p-3 pt-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-lg icon-shape text-center border-radius-xl mt-n4 me-3
                                        @if ($item['status'] === 'completed') bg-gradient-success shadow-success
                                        @elseif ($item['status'] === 'in_progress') bg-gradient-warning shadow-warning
                                        @elseif (!$item['can_start'] && $item['status'] === 'not_started') bg-gradient-secondary shadow-secondary
                                        @else bg-gradient-info shadow-info
                                        @endif">
                                        
                                        @if ($item['test_id'] == 1)
                                            <i class="material-icons opacity-10">code</i>
                                        @elseif ($item['test_id'] == 2)
                                            <i class="material-icons opacity-10">psychology</i>
                                        @elseif ($item['test_id'] == 3)
                                            <i class="material-icons opacity-10">theater_comedy</i>
                                        @else
                                            <i class="material-icons opacity-10">assignment</i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 text-start">
                                        <p class="text-sm mb-0 text-capitalize text-muted">Asesmen #{{ $item['test_order'] }}</p>
                                        <h5 class="mb-0 font-weight-bolder @if(!$item['can_start'] && $item['status'] === 'not_started') text-muted @endif" style="line-height: 1.4;">
                                            {{ $item['test_name'] }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <hr class="dark horizontal my-0">
                            <div class="card-body pt-2 d-flex flex-column flex-grow-1">
                                <p class="text-sm text-secondary mb-2" style="min-height: 54px; /* Sesuaikan dengan 3 baris x 18px (contoh) */ max-height: 54px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; line-height: 1.3;">
                                    {{ Str::limit($item['description'], 120) }}
                                </p>
                                
                                <div class="mt-auto">
                                    @if ($item['time_limit_minutes'])
                                    <div class="d-flex align-items-center text-xs text-muted mb-2">
                                        <i class="material-icons text-sm me-1">timer</i>
                                        <span>Batas Waktu: {{ $item['time_limit_minutes'] }} menit</span>
                                    </div>
                                    @endif

                                    <div class="mb-3 mt-2">
                                        @if ($item['status'] === 'completed')
                                            <span class="badge badge-sm bg-gradient-success">
                                                <i class="material-icons text-xs me-1" style="vertical-align: text-bottom;">check_circle_outline</i> Selesai
                                            </span>
                                        @elseif ($item['status'] === 'in_progress')
                                             <span class="badge badge-sm bg-gradient-warning">
                                                <i class="material-icons text-xs me-1" style="vertical-align: text-bottom;">hourglass_top</i> Sedang Dikerjakan
                                            </span>
                                        @elseif ($item['can_start'])
                                            <span class="badge badge-sm bg-gradient-light text-dark">
                                                <i class="material-icons text-xs me-1" style="vertical-align: text-bottom;">event_available</i> Belum Dikerjakan
                                            </span>
                                        @else
                                            <span class="badge badge-sm bg-gradient-secondary">
                                                <i class="material-icons text-xs me-1" style="vertical-align: text-bottom;">lock</i> Terkunci
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer pt-0 p-3">
                                @if ($item['status'] === 'completed')
                                    <button class="btn btn-sm btn-outline-success w-100 mb-0" disabled>
                                        <i class="material-icons text-sm me-1">done_all</i>
                                        SELESAI
                                    </button>
                                @elseif ($item['status'] === 'in_progress' && $item['can_start'])
                                    <a href="{{ $item['route'] }}" class="btn btn-sm bg-gradient-warning text-white w-100 mb-0">
                                        Lanjutkan Tes <i class="material-icons text-sm ms-1">play_arrow</i>
                                    </a>
                                @elseif ($item['can_start'])
                                    <a href="{{ $item['route'] }}" class="btn btn-sm bg-gradient-info w-100 mb-0">
                                        Kerjakan Tes <i class="material-icons text-sm ms-1">arrow_forward</i>
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-secondary w-100 mb-0" disabled title="Selesaikan tes sebelumnya untuk membuka tes ini.">
                                        Kerjakan Tes
                                    </button>
                                    @php
                                        $previousTestRequired = null;
                                        if ($item['test_order'] > 1) {
                                            $prevOrder = $item['test_order'] - 1;
                                            foreach($test_statuses_list as $ts) {
                                                if ($ts['test_order'] == $prevOrder) {
                                                    $previousTestRequired = $ts['test_name'];
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    @if($previousTestRequired)
                                    <p class="text-xs text-danger mt-1 mb-0 text-center"><small>Selesaikan "{{ $previousTestRequired }}" dahulu.</small></p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="row mt-2">
            <div class="col-12">
                <div class="alert alert-light text-secondary" role="alert" style="font-size: 0.875rem;">
                    <span class="alert-icon me-2"><i class="material-icons text-sm">info_outline</i></span>
                    <span class="alert-text"><strong>Petunjuk:</strong> Asesmen harus dikerjakan secara berurutan. Tombol "Kerjakan Tes" akan aktif setelah asesmen sebelumnya diselesaikan.</span>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .card-hoverable {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }
        .card-hoverable:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 28px rgba(0,0,0,0.15), 0 7px 10px rgba(0,0,0,0.12) !important;
        }
        .bg-light-subtle {
            background-color: #f8f9fa !important;
        }
        .icon-shape i.material-icons {
            line-height: inherit;
        }
        .card-body .text-secondary {
            line-height: 1.5; 
        }
    </style>
    @endpush
</div>