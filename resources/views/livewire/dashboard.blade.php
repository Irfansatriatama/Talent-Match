<div class="container-fluid py-4">

    @if (Auth::check() && $showProfileCompletionNotice)
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

    @if(Auth::check())
    <div x-data="{ open: @json($showTutorial), currentStep: 1, totalSteps: 4 }" 
         @open-tutorial.window="open = true"
         x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-90"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-90"
         class="row mb-4"
         style="display: none;">
        <div class="col-12">
            <div class="card bg-gradient-primary">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h4 class="text-white mb-0">
                            <i class="material-icons text-white me-2" style="vertical-align: middle;">school</i>
                            Selamat Datang di Talent Match!
                        </h4>
                        <button @click="open = false; @this.dismissTutorial()" 
                                class="btn btn-sm btn-light mb-0">
                            <i class="material-icons text-sm">close</i>
                        </button>
                    </div>
                    
                    <div class="d-flex justify-content-center mb-4">
                        <template x-for="step in totalSteps" :key="step">
                            <div class="mx-1">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     :class="currentStep >= step ? 'bg-white text-primary' : 'bg-white bg-opacity-25 text-white'"
                                     style="width: 30px; height: 30px; cursor: pointer;"
                                     @click="currentStep = step"
                                     x-text="step">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="text-white">
                        <div x-show="currentStep === 1" x-transition>
                            <h5 class="text-white mb-3">Langkah 1: Lengkapi Profil Anda</h5>
                            <p class="mb-3">Sebelum memulai asesmen, pastikan profil Anda sudah lengkap:</p>
                            <ul class="mb-3">
                                <li>Isi <strong>Nomor Telepon</strong> yang aktif</li>
                                <li>Pilih <strong>Posisi yang Dilamar</strong></li>
                                <li>Tulis <strong>Ringkasan Profil</strong> yang menarik</li>
                            </ul>
                            <a href="{{ route('profile') }}" class="btn btn-white btn-sm mb-0">
                                <i class="material-icons text-sm me-1">person</i>
                                Ke Halaman Profil
                            </a>
                        </div>

                        <div x-show="currentStep === 2" x-transition>
                            <h5 class="text-white mb-3">Langkah 2: Pahami Asesmen yang Akan Dijalani</h5>
                            <p class="mb-3">Anda akan menjalani 3 tes penting:</p>
                            <ol class="mb-3">
                                <li><strong>Tes Kemampuan Pemrograman</strong> - Mengukur kemampuan teknis Anda</li>
                                <li><strong>Tes Minat RIASEC</strong> - Mengidentifikasi minat karir Anda</li>
                                <li><strong>Tes Kepribadian MBTI</strong> - Memahami tipe kepribadian Anda</li>
                            </ol>
                            <p class="text-white-50 mb-0">
                                <i class="material-icons text-sm align-middle">info</i>
                                Setiap tes memiliki batas waktu, jadi persiapkan diri dengan baik!
                            </p>
                        </div>

                        <div x-show="currentStep === 3" x-transition>
                            <h5 class="text-white mb-3">Langkah 3: Kerjakan Asesmen Secara Berurutan</h5>
                            <p class="mb-3">Penting untuk diingat:</p>
                            <ul class="mb-3">
                                <li>Tes harus dikerjakan <strong>secara berurutan</strong></li>
                                <li>Anda tidak bisa memulai tes berikutnya sebelum menyelesaikan tes sebelumnya</li>
                                <li>Setelah dimulai, tes harus diselesaikan dalam satu sesi</li>
                                <li>Hasil akan otomatis tersimpan setelah Anda menyelesaikan tes</li>
                            </ul>
                            <a href="{{ route('candidate.assessment.test') }}" class="btn btn-white btn-sm mb-0">
                                <i class="material-icons text-sm me-1">assignment</i>
                                Lihat Daftar Asesmen
                            </a>
                        </div>

                        <div x-show="currentStep === 4" x-transition>
                            <h5 class="text-white mb-3">Langkah 4: Tips Sukses</h5>
                            <p class="mb-3">Untuk hasil terbaik:</p>
                            <ul class="mb-3">
                                <li>Siapkan <strong>koneksi internet yang stabil</strong></li>
                                <li>Gunakan <strong>laptop atau komputer</strong> (tidak disarankan menggunakan ponsel)</li>
                                <li>Cari <strong>tempat yang tenang</strong> tanpa gangguan</li>
                                <li>Jawab dengan <strong>jujur</strong>, terutama untuk tes kepribadian</li>
                            </ul>
                            <p class="text-white mb-0">
                                <strong>Semoga sukses! Tim HR kami akan mengevaluasi hasil Anda dengan sistem ANP yang canggih.</strong>
                            </p>
                        </div>
                    </div>

                    {{-- Navigation Buttons --}}
                    <div class="d-flex justify-content-between mt-4">
                        <button @click="currentStep = Math.max(1, currentStep - 1)" 
                                x-show="currentStep > 1"
                                class="btn btn-white btn-sm mb-0">
                            <i class="material-icons text-sm me-1">arrow_back</i>
                            Sebelumnya
                        </button>
                        <div x-show="currentStep === 1"></div>
                        
                        <button @click="currentStep = Math.min(totalSteps, currentStep + 1)" 
                                x-show="currentStep < totalSteps"
                                class="btn btn-white btn-sm mb-0 ms-auto">
                            Selanjutnya
                            <i class="material-icons text-sm ms-1">arrow_forward</i>
                        </button>
                        <button @click="open = false; @this.dismissTutorial()" 
                                x-show="currentStep === totalSteps"
                                class="btn btn-success btn-sm mb-0 ms-auto text-white">
                            <i class="material-icons text-sm me-1">check</i>
                            Saya Mengerti, Mulai!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Dashboard Card --}}
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
            
            {{-- NEW: Tutorial Toggle Button --}}
            @if(Auth::check())
            <div class="mt-3 text-end">
                <button wire:click="showTutorialManually" 
                        class="btn btn-sm btn-outline-primary mb-0">
                    <i class="material-icons text-sm me-1">help_outline</i>
                    Tampilkan Panduan
                </button>
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
                <div class="card @if($test['display_status_text'] === 'Terkunci' || ($test['status_internal'] === 'not_started' && !$test['can_start'])) bg-light @endif">
                    <div class="card-body p-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
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