<div class="container-fluid py-4">
    {{-- BARIS 1: STATISTIK CARDS --}}
    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">group</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Total Kandidat</p>
                        <h4 class="mb-0">{{ $statistics['total_candidates'] ?? 0 }}</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0">
                        <span class="text-success text-sm font-weight-bolder">+{{ $statistics['new_candidates_this_week'] ?? 0 }}</span>
                        minggu ini
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">checklist_rtl</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Selesai Semua Tes</p>
                        <h4 class="mb-0">{{ $statistics['completed_all_tests'] ?? 0 }}</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0">
                        <span class="text-info text-sm font-weight-bolder">{{ $statistics['completion_rate'] ?? 0 }}%</span>
                        tingkat penyelesaian
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">analytics</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Analisis ANP</p>
                        <h4 class="mb-0">{{ $statistics['anp_analyses_count'] ?? 0 }}</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0">
                        <span class="text-warning text-sm font-weight-bolder">+{{ $statistics['anp_this_month'] ?? 0 }}</span>
                        bulan ini
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-warning shadow-warning text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">leaderboard</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Rata-rata Skor</p>
                        <h4 class="mb-0">{{ $statistics['average_programming_score'] ?? 'N/A' }}</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0 text-sm">Programming Asesmen</p>
                </div>
            </div>
        </div>
    </div>

    {{-- BARIS 2: GRAFIK INTERAKTIF --}}
    <div class="row mt-4">
        <div class="col-lg-6 col-md-6 mb-4">
            <div class="card z-index-2 h-100">
                <div class="card-header pb-0 pt-3 bg-transparent">
                    <h6 class="text-capitalize">Distribusi Tipe MBTI</h6>
                    <p class="text-sm mb-0">
                        <i class="fa fa-brain text-warning" aria-hidden="true"></i>
                        <span class="font-weight-bold ms-1">{{ count($mbtiData) }} tipe</span> kepribadian teridentifikasi
                    </p>
                </div>
                <div class="card-body p-3">
                    <div class="chart">
                        <canvas id="mbtiDistributionChart" class="chart-canvas" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRAFIK DISTRIBUSI RIASEC (BAR CHART) --}}
        <div class="col-lg-6 col-md-6 mb-4">
            <div class="card z-index-2 h-100">
                <div class="card-header pb-0 pt-3 bg-transparent">
                    <h6 class="text-capitalize">Distribusi Tipe RIASEC</h6>
                    <p class="text-sm mb-0">
                        <i class="fa fa-chart-bar text-info" aria-hidden="true"></i>
                        <span class="font-weight-bold ms-1">6 dimensi</span> minat pekerjaan
                    </p>
                </div>
                <div class="card-body p-3">
                    <div class="chart">
                        <canvas id="riasecDistributionChart" class="chart-canvas" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BARIS 3: TABEL DAN KARTU INFORMASI --}}
    <div class="row mt-4">
        {{-- TOP KANDIDAT (DENGAN SCROLLABLE TABLE) --}}
        <div class="col-lg-7 mb-lg-0 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6>Top 5 Kandidat Berdasarkan Skor</h6>
                    <p class="text-sm">Kandidat dengan performa terbaik di semua tes</p>
                </div>
                <div class="card-body px-0 pb-2">
                    <div style="max-height: 400px; overflow-y: auto;">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kandidat</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Posisi</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Skor Rata-rata</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ranking</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topCandidates as $index => $candidate)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div>
                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($candidate->name) }}&background=random" 
                                                         class="avatar avatar-sm me-3 border-radius-lg" alt="user">
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $candidate->name }}</h6>
                                                    <p class="text-xs text-secondary mb-0">{{ $candidate->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $candidate->jobPosition->name ?? 'N/A' }}</p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">{{ $candidate->average_score }}</span>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="badge badge-sm 
                                                @if($index == 0) bg-gradient-success 
                                                @elseif($index == 1) bg-gradient-info 
                                                @elseif($index == 2) bg-gradient-warning 
                                                @else bg-gradient-secondary @endif">
                                                #{{ $index + 1 }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3">
                                            <p class="text-muted mb-0">Belum ada kandidat yang menyelesaikan semua tes.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6>Analisis ANP Terbaru</h6>
                    <p class="text-sm">5 analisis terakhir yang dibuat</p>
                </div>
                <div class="card-body p-3">
                    <div style="max-height: 400px; overflow-y: auto;">
                        @forelse ($recentAnalyses as $analysis)
                            <div class="card mb-3 border">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 text-sm">{{ $analysis->name }}</h6>
                                            <p class="text-xs text-muted mb-2">
                                                <i class="material-icons text-sm align-middle">work</i> 
                                            </p>
                                            <div class="avatar-group">
                                                @foreach($analysis->candidates->take(3) as $candidate)
                                                    <a href="#" class="avatar avatar-xs rounded-circle" 
                                                       data-bs-toggle="tooltip" 
                                                       data-bs-placement="bottom" 
                                                       title="{{ $candidate->name }}">
                                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($candidate->name) }}&size=30" 
                                                             alt="{{ $candidate->name }}">
                                                    </a>
                                                @endforeach
                                                @if($analysis->candidates_count > 3)
                                                    <span class="avatar avatar-xs rounded-circle bg-gradient-dark text-white">
                                                        +{{ $analysis->candidates_count - 3 }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            {{-- BADGE STATUS DENGAN WARNA --}}
                                            @php
                                                $statusClass = 'bg-gradient-secondary';
                                                if ($analysis->status == 'completed') {
                                                    $statusClass = 'bg-gradient-success';
                                                } elseif ($analysis->status == 'calculating') {
                                                    $statusClass = 'bg-gradient-info';
                                                } elseif (str_contains($analysis->status, 'pending')) {
                                                    $statusClass = 'bg-gradient-warning';
                                                } elseif ($analysis->status == 'error') {
                                                    $statusClass = 'bg-gradient-danger';
                                                }
                                            @endphp
                                            <span class="badge badge-sm {{ $statusClass }} mb-2">
                                                {{ ucfirst(str_replace('_', ' ', $analysis->status)) }}
                                            </span>
                                            <p class="text-xs text-secondary mb-0">
                                                {{ $analysis->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        @if($analysis->status == 'completed')
                                            <a href="{{ route('HR.anp.analysis.show', $analysis->id) }}" 
                                               class="btn btn-sm bg-gradient-primary mb-0">
                                                <i class="material-icons text-sm">visibility</i> Lihat Hasil
                                            </a>
                                        @elseif($analysis->status !== 'calculating')
                                            <a href="{{ route('HR.anp.analysis.network.define', $analysis->id) }}" 
                                               class="btn btn-sm bg-gradient-warning mb-0">
                                                <i class="material-icons text-sm">play_arrow</i> Lanjutkan Proses
                                            </a>
                                        @else
                                            <button class="btn btn-sm bg-gradient-info mb-0" disabled>
                                                <i class="material-icons text-sm">hourglass_empty</i> Sedang Diproses...
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="material-icons text-5xl text-secondary opacity-5">analytics</i>
                                <p class="text-muted mt-2">Belum ada analisis yang dilakukan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="{{ asset('assets') }}/js/plugins/chartjs.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // GRAFIK DISTRIBUSI MBTI (DONUT CHART)
        const mbtiCtx = document.getElementById("mbtiDistributionChart");
        if (mbtiCtx) {
            new Chart(mbtiCtx.getContext("2d"), {
                type: "doughnut",
                data: {
                    labels: @json($mbtiLabels),
                    datasets: [{
                        data: @json($mbtiData),
                        backgroundColor: @json($mbtiColors),
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12,
                                    family: "Poppins"
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // GRAFIK DISTRIBUSI RIASEC (BAR CHART)
        const riasecCtx = document.getElementById("riasecDistributionChart");
        if (riasecCtx) {
            new Chart(riasecCtx.getContext("2d"), {
                type: "bar",
                data: {
                    labels: @json($riasecLabels),
                    datasets: [{
                        label: "Jumlah Kandidat",
                        data: @json($riasecData),
                        backgroundColor: [
                            '#4caf50', // Realistic - Green
                            '#2196f3', // Investigative - Blue
                            '#9c27b0', // Artistic - Purple
                            '#ff9800', // Social - Orange
                            '#f44336', // Enterprising - Red
                            '#607d8b'  // Conventional - Blue Grey
                        ],
                        borderColor: [
                            '#388e3c',
                            '#1976d2',
                            '#7b1fa2',
                            '#f57c00',
                            '#d32f2f',
                            '#455a64'
                        ],
                        borderWidth: 1,
                        borderRadius: 5,
                        maxBarThickness: 60
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    const labels = {
                                        'Realistic': 'Realistic (Praktis, berorientasi alat)',
                                        'Investigative': 'Investigative (Analitis, peneliti)',
                                        'Artistic': 'Artistic (Kreatif, ekspresif)',
                                        'Social': 'Social (Membantu, interpersonal)',
                                        'Enterprising': 'Enterprising (Persuasif, pemimpin)',
                                        'Conventional': 'Conventional (Terorganisir, detail)'
                                    };
                                    return labels[context[0].label] || context[0].label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 12,
                                    family: "Poppins"
                                }
                            },
                            grid: {
                                borderDash: [5, 5],
                                color: 'rgba(0, 0, 0, 0.08)'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 12,
                                    family: "Poppins"
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush