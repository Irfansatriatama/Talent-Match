<div>
    {{-- BAR STICKY UNTUK INFORMASI TES --}}
    <div class="card shadow-sm mb-4 sticky-top" style="top: 15px; z-index: 1020; background-color: #ffffff;">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-lg-7 col-md-6 col-12 mb-2 mb-md-0">
                    <h5 class="mb-1 font-weight-bolder">{{ $currentTest->test_name ?? 'Tes Minat RIASEC' }}</h5>
                    <div class="d-flex justify-content-start align-items-center">
                        <p class="text-sm text-muted mb-0 me-3">Progres: {{ $answeredQuestions }} / {{ $totalQuestions }}</p>
                        <div class="progress w-50" style="height: 8px;" title="{{ $totalQuestions > 0 ? round(($answeredQuestions/$totalQuestions)*100) : 0 }}% selesai">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $totalQuestions > 0 ? ($answeredQuestions/$totalQuestions)*100 : 0 }}%;" 
                                 aria-valuenow="{{ $totalQuestions > 0 ? ($answeredQuestions/$totalQuestions)*100 : 0 }}" 
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-md-6 col-12">
                    @if ($timeLimitMinutes)
                        <div wire:poll.1s="handleTimerTick" class="text-md-end text-center">
                            <h6 class="mb-1 text-xs text-uppercase">Sisa Waktu</h6>
                            <h4 class="mb-0 font-weight-bolder @if($timeRemaining !== null && $timeRemaining < 300 && $timeRemaining > 0) text-danger 
                                                            @elseif($timeRemaining !== null && $timeRemaining <= 0) text-danger fst-italic @endif">
                                {{ gmdate('H:i:s', $timeRemaining ?? 0) }}
                                @if($timeRemaining !== null && $timeRemaining <= 0 && !$testCompleted) (Waktu Habis) @endif
                            </h4>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- DESKRIPSI TES --}}
    <div class="card shadow-none border mb-4">
        <div class="card-body p-3">
            <p class="text-sm mb-0 text-secondary">
                Untuk setiap aktivitas berikut, tunjukkan seberapa Anda tertarik atau menyukainya.
            </p>
        </div>
    </div>

    {{-- Area Notifikasi Modal & Session Flash --}}
    <div id="js-notification-area" class="mb-3">
        {{-- MODAL UNTUK NOTIFIKASI SOAL BELUM DIJAWAB (GAYA MBTI) --}}
        @if ($showUnansweredQuestionsModal && $submissionErrorMessage)
        <div class="modal fade show" id="unansweredQuestionsModalRiasec" tabindex="-1" aria-labelledby="unansweredModalLabelRiasec" 
             style="display: block; background-color: rgba(0,0,0,0.6);" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bolder" id="unansweredModalLabelRiasec">
                            <i class="material-icons text-warning me-2" style="vertical-align: middle;">warning</i>
                            Perhatian!
                        </h5>
                        <button type="button" class="btn-close text-dark" wire:click="closeUnansweredModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">{{ $submissionErrorMessage }}</p>
                        <p class="text-sm text-muted mt-2">Silakan lengkapi semua item sebelum melanjutkan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gradient-warning" wire:click="closeUnansweredModal">Mengerti, Saya Akan Lengkapi</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
        {{-- AKHIR MODAL --}}

        {{-- Notifikasi dari session() akan muncul di sini setelah redirect --}}
        @if (session()->has('error'))
            <div class="alert alert-danger text-white alert-dismissible fade show" role="alert">
                <span class="alert-text d-flex align-items-center">
                    <i class="material-icons text-white me-2">error_outline</i>
                    <strong>Error!</strong>&nbsp; {{ session('error') }}
                </span>
                <button type="button" class="btn-close text-lg py-3 opacity-10" onclick="this.parentElement.remove()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
         @if (session()->has('success'))
            <div class="alert alert-success text-white alert-dismissible fade show" role="alert">
                <span class="alert-text d-flex align-items-center">
                    <i class="material-icons text-white me-2">check_circle</i>
                    <strong>Sukses!</strong>&nbsp; {{ session('success') }}
                </span>
                <button type="button" class="btn-close text-lg py-3 opacity-10" onclick="this.parentElement.remove()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    </div>


    <form wire:submit.prevent="finishTest(false)">
        @if($questions && $questions->count() > 0)
            @php
                $likertScale = [
                    1 => 'Sangat Tidak Suka', 2 => 'Tidak Suka', 3 => 'Netral',
                    4 => 'Suka', 5 => 'Sangat Suka',
                ];
            @endphp

            {{-- Progress bar yang sebelumnya ada di sini sudah dipindahkan ke sticky bar --}}

            @foreach ($questions as $index => $question)
                <div class="card mb-3 question-item" id="question-card-{{ $question->question_id }}">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between">
                            <p class="font-weight-bold text-sm mb-1">Aktivitas {{ $index + 1 }}</p>
                        </div>
                        <div class="mb-3">
                            <p class="text-dark my-2" style="font-size: 1.1rem; line-height: 1.6;">
                                {{ $question->question_text }}
                            </p>
                        </div>
                        <h6 class="text-xs text-muted mb-3 text-center">Seberapa Anda tertarik atau menyukai aktivitas ini?</h6>
                        <div class="row justify-content-center gx-2 gy-2">
                            @foreach ($likertScale as $scoreValue => $scoreText)
                                <div class="col-sm col mb-2"> {{-- `col-sm col` untuk layout responsif skala Likert --}}
                                    <div wire:click="selectAnswer({{ $question->question_id }}, {{ $scoreValue }})"
                                         class="form-check card card-body shadow-xs border text-center p-2 h-100 d-flex flex-column justify-content-center align-items-center cursor-pointer option-box riasec-option
                                                {{ ($userAnswers[$question->question_id] ?? null) == $scoreValue ? 'active-option border-primary bg-gradient-primary text-white' : 'border-light hover-shadow-sm' }}">
                                        <input class="form-check-input visually-hidden" 
                                               type="radio" 
                                               name="answer_{{ $question->question_id }}" 
                                               id="option_{{ $question->question_id }}_{{ $scoreValue }}" 
                                               value="{{ $scoreValue }}"
                                               {{ ($userAnswers[$question->question_id] ?? null) == $scoreValue ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 mt-1 mb-0" for="option_{{ $question->question_id }}_{{ $scoreValue }}">
                                            <span class="d-block {{ ($userAnswers[$question->question_id] ?? null) == $scoreValue ? 'font-weight-bolder text-white' : 'font-weight-bold text-dark' }}" style="font-size: 1.05rem;">
                                                {{ $scoreValue }}
                                            </span>
                                            <span class="text-xs d-block mt-1 {{ ($userAnswers[$question->question_id] ?? null) == $scoreValue ? 'text-white' : 'text-muted' }}" style="line-height: 1.2; min-height: 2.4em;">
                                                ({{ $scoreText }})
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="notification-area-q{{ $question->question_id }}" class="mt-2 text-center" style="min-height: 20px;"></div>
                    </div>
                </div>
            @endforeach

            <div class="mt-4 mb-3 text-center">
                <button type="submit" 
                        wire:loading.attr="disabled" 
                        wire:target="finishTest" 
                        class="btn bg-gradient-success w-100 btn-lg"
                        @if($isSubmitting) disabled @endif>
                    <span wire:loading wire:target="finishTest" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    <span wire:loading wire:target="finishTest">Memproses...</span>
                    <span wire:loading.remove wire:target="finishTest">Selesaikan Tes RIASEC</span>
                </button>
            </div>
        @else
            <p class="text-center text-secondary py-5">Tidak ada aktivitas yang tersedia untuk tes ini atau tes tidak dapat dimulai dengan benar.</p> 
        @endif
    </form>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            console.log('Livewire init: assessment-riasec.blade.php scripts loaded.');
            const jsNotifArea = document.getElementById('js-notification-area'); 

            Livewire.on('answer-saved', (eventDetail) => { 
                const notifArea = document.getElementById('notification-area-q' + eventDetail.questionId);
                if(notifArea) {
                    notifArea.innerHTML = `<p class="text-xs text-success mt-1 mb-0 fst-italic">${eventDetail.message}</p>`;
                    setTimeout(() => { notifArea.innerHTML = ''; }, 2000);
                }
            });

            Livewire.on('answer-save-error', (eventDetail) => {
                if(jsNotifArea && typeof showJsNotification === 'function') {
                    showJsNotification(eventDetail.message, 'danger');
                } else if (jsNotifArea) {
                    jsNotifArea.innerHTML = ''; 
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-warning text-white alert-dismissible fade show';
                    alertDiv.innerHTML = `<span class="alert-text d-flex align-items-center"><i class="material-icons text-white me-2">warning</i><strong>Ups!</strong>&nbsp; ${eventDetail.message}</span> <button type="button" class="btn-close text-lg py-3 opacity-10" onclick="this.parentElement.remove()" aria-label="Close"><span aria-hidden="true">&times;</span></button>`;
                    jsNotifArea.appendChild(alertDiv);
                } else { 
                    alert('Ups! ' + eventDetail.message); 
                }
            });

            Livewire.on('testFinishedSuccessfully', () => {
                console.log('Event testFinishedSuccessfully (RIASEC) diterima');
                window.onbeforeunload = null; 
            });
        });
    </script>
    @endpush
</div>