<div x-data="assessmentRIASEC()" x-init="init()">
    {{-- BAR STICKY UNTUK INFORMASI TES --}}
    <div class="card shadow-sm mb-4 sticky-top" style="top: 15px; z-index: 1020; background-color: #ffffff;">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-lg-7 col-md-6 col-12 mb-2 mb-md-0">
                    <h5 class="mb-1 font-weight-bolder">{{ $currentTest->test_name ?? 'Tes Minat RIASEC' }}</h5>
                    <div class="d-flex justify-content-start align-items-center">
                        <p class="text-sm text-muted mb-0 me-3">Progres: <span x-text="answeredCount">{{ $answeredQuestions }}</span> / {{ $totalQuestions }}</p>
                        <div class="progress w-50" style="height: 8px;">
                            <div class="progress-bar bg-success transition-all" role="progressbar" 
                                 :style="`width: ${progressPercentage}%;`" 
                                 :aria-valuenow="progressPercentage" 
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

    <div id="js-notification-area" class="mb-3">
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
                $likertColors = [
                    1 => 'danger', 2 => 'warning', 3 => 'secondary',
                    4 => 'info', 5 => 'success',
                ];
            @endphp

            @foreach ($questions as $index => $question)
                <div class="card mb-3 question-item" id="question-card-{{ $question->question_id }}">
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <p class="text-dark my-2" style="font-size: 1.1rem; line-height: 1.6;">
                                {{ $question->question_text }}
                            </p>
                        </div>
                        <h6 class="text-xs text-muted mb-3 text-center">Seberapa Anda tertarik atau menyukai aktivitas ini?</h6>
                        
                        {{-- Desktop View --}}
                        <div class="row justify-content-center gx-2 gy-2 d-none d-md-flex">
                            @foreach ($likertScale as $scoreValue => $scoreText)
                                <div class="col-sm col mb-2">
                                    <div @click="selectAnswer({{ $question->question_id }}, {{ $scoreValue }})"
                                         wire:click="selectAnswer({{ $question->question_id }}, {{ $scoreValue }})"
                                         class="form-check card card-body shadow-xs border text-center p-2 h-100 d-flex flex-column justify-content-center align-items-center cursor-pointer option-box riasec-option transition-all"
                                         :class="{ 
                                             'active-option border-{{ $likertColors[$scoreValue] }} bg-gradient-{{ $likertColors[$scoreValue] }} text-white': localAnswers[{{ $question->question_id }}] == {{ $scoreValue }},
                                             'border-light hover-shadow-sm': localAnswers[{{ $question->question_id }}] != {{ $scoreValue }},
                                             'opacity-50': savingAnswers[{{ $question->question_id }}]
                                         }">
                                        <input class="form-check-input visually-hidden" 
                                               type="radio" 
                                               name="answer_{{ $question->question_id }}" 
                                               id="option_{{ $question->question_id }}_{{ $scoreValue }}" 
                                               value="{{ $scoreValue }}"
                                               :checked="localAnswers[{{ $question->question_id }}] == {{ $scoreValue }}">
                                        <label class="form-check-label w-100 mt-1 mb-0" for="option_{{ $question->question_id }}_{{ $scoreValue }}">
                                            <span class="d-block" :class="{ 'font-weight-bolder text-white': localAnswers[{{ $question->question_id }}] == {{ $scoreValue }}, 'font-weight-bold text-dark': localAnswers[{{ $question->question_id }}] != {{ $scoreValue }} }" style="font-size: 1.05rem;">
                                                {{ $scoreValue }}
                                            </span>
                                            <span class="text-xs d-block mt-1" :class="{ 'text-white': localAnswers[{{ $question->question_id }}] == {{ $scoreValue }}, 'text-muted': localAnswers[{{ $question->question_id }}] != {{ $scoreValue }} }" style="line-height: 1.2; min-height: 2.4em;">
                                                {{ $scoreText }}
                                            </span>
                                        </label>
                                        <div x-show="savingAnswers[{{ $question->question_id }}] && localAnswers[{{ $question->question_id }}] == {{ $scoreValue }}"
                                             class="position-absolute top-0 end-0 m-1">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- Mobile View --}}
                        <div class="d-md-none">
                            <div class="likert-scale-mobile">
                                @foreach ($likertScale as $scoreValue => $scoreText)
                                    <div @click="selectAnswer({{ $question->question_id }}, {{ $scoreValue }})"
                                         wire:click="selectAnswer({{ $question->question_id }}, {{ $scoreValue }})"
                                         class="likert-option-mobile d-flex align-items-center justify-content-between p-3 mb-2 rounded cursor-pointer transition-all"
                                         :class="{ 
                                             'bg-gradient-{{ $likertColors[$scoreValue] }} text-white shadow': localAnswers[{{ $question->question_id }}] == {{ $scoreValue }},
                                             'bg-light': localAnswers[{{ $question->question_id }}] != {{ $scoreValue }},
                                             'opacity-50': savingAnswers[{{ $question->question_id }}]
                                         }">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <span :class="{ 'font-weight-bolder': localAnswers[{{ $question->question_id }}] == {{ $scoreValue }} }">
                                                    {{ $scoreValue }} - {{ $scoreText }}
                                                </span>
                                            </div>
                                        </div>
                                        <div x-show="savingAnswers[{{ $question->question_id }}] && localAnswers[{{ $question->question_id }}] == {{ $scoreValue }}">
                                            <div class="spinner-border spinner-border-sm text-white" role="status">
                                                <span class="visually-hidden">Menyimpan...</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
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
</div>

<style>
.transition-all {
    transition: all 0.2s ease-in-out;
}

.riasec-option {
    transition: all 0.15s ease-in-out;
    position: relative;
    overflow: hidden;
    min-height: 120px;
}

.riasec-option::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.riasec-option:hover::before {
    left: 100%;
}

.riasec-option:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.riasec-option.active-option {
    transform: translateY(0) scale(1);
}

.bg-gradient-danger { background: linear-gradient(135deg, #ea4c89 0%, #d32f2f 100%) !important; }
.bg-gradient-warning { background: linear-gradient(135deg, #f9a825 0%, #f57c00 100%) !important; }
.bg-gradient-secondary { background: linear-gradient(135deg, #757575 0%, #424242 100%) !important; }
.bg-gradient-info { background: linear-gradient(135deg, #039be5 0%, #0277bd 100%) !important; }
.bg-gradient-success { background: linear-gradient(135deg, #43a047 0%, #2e7d32 100%) !important; }

.border-danger { border-color: #ea4c89 !important; }
.border-warning { border-color: #f9a825 !important; }
.border-secondary { border-color: #757575 !important; }
.border-info { border-color: #039be5 !important; }
.border-success { border-color: #43a047 !important; }

.hover-shadow-sm:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.question-item {
    will-change: transform;
}

.riasec-option.opacity-50 {
    pointer-events: none;
}

.likert-option-mobile {
    transition: all 0.15s ease-in-out;
    border: 1px solid #dee2e6;
}

.likert-option-mobile:hover {
    transform: translateX(5px);
    border-color: #adb5bd;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.text-success i {
    animation: pulse 0.5s ease-in-out;
}
</style>

@push('scripts')
<script>
function assessmentRIASEC() {
    return {
        localAnswers: @json($userAnswers),
        savingAnswers: {},
        savedAnswers: {},
        totalQuestions: {{ $totalQuestions }},
        
        get answeredCount() {
            return Object.keys(this.localAnswers).length;
        },
        
        get progressPercentage() {
            return this.totalQuestions > 0 ? Math.round((this.answeredCount / this.totalQuestions) * 100) : 0;
        },
        
        init() {
            Object.keys(this.localAnswers).forEach(questionId => {
                this.savedAnswers[questionId] = true;
            });
            
            Livewire.on('answer-saved', (eventDetail) => {
                this.savingAnswers[eventDetail.questionId] = false;
                this.savedAnswers[eventDetail.questionId] = true;
                
                setTimeout(() => {
                    this.savedAnswers[eventDetail.questionId] = false;
                }, 2000);
            });
            
            Livewire.on('answer-save-error', (eventDetail) => {
                const questionId = eventDetail.questionId;
                if (questionId) {
                    this.savingAnswers[questionId] = false;
                    delete this.localAnswers[questionId];
                }
                
                const jsNotifArea = document.getElementById('js-notification-area');
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
        },
        
        selectAnswer(questionId, scoreValue) {
            const previousAnswer = this.localAnswers[questionId];
            this.localAnswers[questionId] = scoreValue;
            this.savingAnswers[questionId] = true;
            this.savedAnswers[questionId] = false;
            
        }
    }
}
</script>
@endpush