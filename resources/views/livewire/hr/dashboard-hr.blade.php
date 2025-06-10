<div class="container-fluid py-4">
    <div class="row">
        {{-- Card Total Kandidat --}}
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">group</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Total Kandidat</p>
                        <h4 class="mb-0">{{ $statistics['total_candidates'] ?? 0 }}</h4>
                    </div>
                </div>
                <div class="card-footer p-3"></div>
            </div>
        </div>

        {{-- Card Selesai Semua Tes --}}
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
                <div class="card-footer p-3"></div>
            </div>
        </div>

        {{-- Card Tes Dikerjakan --}}
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-warning shadow-warning text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">pending_actions</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Kandidat Aktif Tes</p>
                        <h4 class="mb-0">{{ $statistics['tests_in_progress'] ?? 0 }}</h4>
                    </div>
                </div>
                <div class="card-footer p-3"></div>
            </div>
        </div>

        {{-- Card Rata-rata Skor Programming --}}
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">leaderboard</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Avg. Skor Prog.</p>
                        <h4 class="mb-0">{{ $statistics['average_programming_score'] ?? 'N/A' }}</h4>
                    </div>
                </div>
                <div class="card-footer p-3"></div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        {{-- Grafik --}}
        <div class="col-lg-7 mb-lg-0 mb-4">
            <div class="card z-index-2 h-100">
                <div class="card-header pb-0 pt-3 bg-transparent">
                    <h6 class="text-capitalize">Tingkat Penyelesaian Tes</h6>
                    <p class="text-sm mb-0">
                        <i class="fa fa-users text-info" aria-hidden="true"></i>
                        <span class="font-weight-bold ms-1">Berdasarkan total percobaan</span> per jenis tes.
                    </p>
                </div>
                <div class="card-body p-3">
                    <div class="chart">
                        {{-- Canvas ini akan menjadi target untuk Chart.js --}}
                        <canvas id="testCompletionChart" class="chart-canvas" height="170"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aktivitas Terbaru --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6>Aktivitas Kandidat Terbaru</h6>
                </div>
                <div class="card-body p-3">
                    <div class="timeline timeline-one-side">
                        @forelse ($recentActivities as $activity)
                            <div class="timeline-block mb-3">
                                <span class="timeline-step
                                    @if($activity->status == 'completed') bg-success @elseif($activity->status == 'in_progress') bg-warning @else bg-info @endif">
                                    <i class="material-icons text-white">
                                        @if($activity->status == 'completed') check @elseif($activity->status == 'in_progress') play_arrow @else fiber_new @endif
                                    </i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark text-sm font-weight-bold mb-0">
                                        <a href="{{ route('hr.detail-candidate', ['candidate' => $activity->user_id]) }}">{{ $activity->user->name ?? 'User Dihapus' }}</a>
                                        {{ $activity->status == 'completed' ? 'menyelesaikan' : ($activity->status == 'in_progress' ? 'melanjutkan' : 'memulai') }}
                                        <strong>{{ $activity->test->test_name ?? '' }}</strong>
                                        @if($activity->status == 'completed' && !is_null($activity->score))
                                            (Skor: {{ $activity->score }})
                                        @endif
                                    </h6>
                                    <p class="text-secondary font-weight-normal text-xs mt-1 mb-0">{{ $activity->updated_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center">
                                <p class="text-muted">Belum ada aktivitas terbaru dari kandidat.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT GRAFIK YANG SUDAH DISEMPURNAKAN --}}
@push('js')
{{-- Pastikan file chartjs.min.js ada di public/assets/js/plugins/ --}}
<script src="{{ asset('assets') }}/js/plugins/chartjs.min.js"></script>
<script>
    // Menggunakan DOMContentLoaded yang merupakan praktik terbaik untuk eksekusi script
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById("testCompletionChart");
        
        if (ctx) {
            const labels = @json($chartLabels);
            const data = @json($chartData);

            new Chart(ctx.getContext("2d"), {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Tingkat Penyelesaian (%)",
                        tension: 0.4,
                        borderWidth: 0,
                        borderRadius: 4,
                        borderSkipped: false,
                        backgroundColor: [
                            "rgba(94, 114, 228, .8)",   // Biru untuk Programming
                            "rgba(251, 99, 64, .8)",    // Orange untuk RIASEC
                            "rgba(54, 185, 204, .8)"    // Cyan untuk MBTI
                        ],
                        data: data,
                        maxBarThickness: 30
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + '%';
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    scales: {
                        y: {
                            grid: {
                                drawBorder: false,
                                display: true,
                                drawOnChartArea: true,
                                drawTicks: false,
                                borderDash: [5, 5],
                                color: 'rgba(0, 0, 0, .08)'
                            },
                            ticks: {
                                suggestedMin: 0,
                                suggestedMax: 100,
                                beginAtZero: true,
                                padding: 10,
                                font: {
                                    size: 14,
                                    weight: 300,
                                    family: "Poppins",
                                    style: 'normal',
                                    lineHeight: 2
                                },
                                color: "#6c757d",
                                callback: function(value) {
                                    return value + '%'
                                }
                            },
                        },
                        x: {
                            grid: {
                                drawBorder: false,
                                display: false,
                                drawOnChartArea: false,
                                drawTicks: false,
                            },
                            ticks: {
                                display: true,
                                color: '#6c757d',
                                padding: 20,
                                font: {
                                    size: 14,
                                    weight: 300,
                                    family: "Poppins",
                                    style: 'normal',
                                    lineHeight: 2
                                },
                            }
                        },
                    },
                },
            });
        }
    });
</script>
@endpush