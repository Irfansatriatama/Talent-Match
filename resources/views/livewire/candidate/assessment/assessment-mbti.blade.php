<div x-data="assessmentMBTI()" x-init="init()">
    <div class="card shadow-sm mb-4 sticky-top" style="top: 15px; z-index: 1020; background-color: #ffffff;">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-6 col-12 mb-2 mb-md-0">
                    <h5 class="mb-1 font-weight-bolder">{{ $currentTest->test_name ?? 'Tes Kepribadian MBTI' }}</h5>
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
                <div class="col-md-6 col-12">
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
    <div class="card shadow-none border mb-4">
        <div class="card-body p-3">
            <p class="text-sm mb-0 text-secondary">
                Untuk setiap pasangan pernyataan, pilih salah satu yang paling menggambarkan diri Anda. Tidak ada jawaban benar atau salah. Pilihlah yang paling alami bagi Anda.
            </p>
        </div>
    </div>
    
    <div id="notification-area-mbti" class="mb-3">
        @if ($showUnansweredQuestionsModal && $submissionErrorMessage)
        <div class="modal fade show" id="unansweredQuestionsModalMbti" tabindex="-1" aria-labelledby="unansweredModalLabelMbti" 
             style="display: block; background-color: rgba(0,0,0,0.6);" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bolder" id="unansweredModalLabelMbti">
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
                <span class="alert-text d-flex align-items-center"><i class="material-icons text-white me-2">error_outline</i><strong>Error!</strong>&nbsp; {{ session('error') }}</span>
                <button type="button" class="btn-close text-lg py-3 opacity-10" onclick="this.parentElement.remove()" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
         @if (session()->has('success'))
            <div class="alert alert-success text-white alert-dismissible fade show" role="alert">
                <span class="alert-text d-flex align-items-center"><i class="material-icons text-white me-2">check_circle</i><strong>Sukses!</strong>&nbsp; {{ session('success') }}</span>
                <button type="button" class="btn-close text-lg py-3 opacity-10" onclick="this.parentElement.remove()" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
    </div>

    <form wire:submit.prevent="finishTest(false)">
        @if($questions && $questions->count() > 0)
            @foreach ($questions as $index => $question)
                <div class="card mb-4 question-item" id="question-card-{{ $question->question_id }}">
                    <div class="card-body p-3 p-md-4">
                        @if($question->question_text !== 'Pilih pernyataan yang paling menggambarkan diri Anda:')
                            <h6 class="font-weight-bolder mb-4 text-center text-gray-700">{{ $question->question_text }}</h6>
                        @endif
                        
                        @if($question->options->count() == 2)
                            @php
                                $optionA = $question->options[0];
                                $optionB = $question->options[1];
                            @endphp

                            <div @click="selectAnswer({{ $question->question_id }}, {{ $optionA->option_id }})"
                                 wire:click="selectAnswer({{ $question->question_id }}, {{ $optionA->option_id }})"
                                 class="form-check card card-body shadow-xs border mb-3 p-3 p-md-4 cursor-pointer option-box mbti-option transition-all"
                                 :class="{ 
                                     'active-option border-primary bg-gradient-primary text-white': localAnswers[{{ $question->question_id }}] == {{ $optionA->option_id }},
                                     'border-light hover-shadow-sm': localAnswers[{{ $question->question_id }}] != {{ $optionA->option_id }},
                                     'opacity-50': savingAnswers[{{ $question->question_id }}]
                                 }">
                                <input class="form-check-input visually-hidden" type="radio" 
                                       name="mbti_answer_{{ $question->question_id }}" 
                                       id="option_{{ $optionA->option_id }}" 
                                       value="{{ $optionA->option_id }}"
                                       :checked="localAnswers[{{ $question->question_id }}] == {{ $optionA->option_id }}">
                                <label class="form-check-label w-100 mb-0 text-center d-flex align-items-center justify-content-center" 
                                       for="option_{{ $optionA->option_id }}">
                                    <span :class="{ 'font-weight-bolder text-white': localAnswers[{{ $question->question_id }}] == {{ $optionA->option_id }}, 'font-weight-normal text-dark': localAnswers[{{ $question->question_id }}] != {{ $optionA->option_id }} }" 
                                          class="d-block" style="font-size: 1.05rem; line-height: 1.6;">
                                        {{ $optionA->option_text }}
                                    </span>
                                </label>
                            </div>

                            <div @click="selectAnswer({{ $question->question_id }}, {{ $optionB->option_id }})"
                                 wire:click="selectAnswer({{ $question->question_id }}, {{ $optionB->option_id }})"
                                 class="form-check card card-body shadow-xs border mb-3 p-3 p-md-4 cursor-pointer option-box mbti-option transition-all"
                                 :class="{ 
                                     'active-option border-primary bg-gradient-primary text-white': localAnswers[{{ $question->question_id }}] == {{ $optionB->option_id }},
                                     'border-light hover-shadow-sm': localAnswers[{{ $question->question_id }}] != {{ $optionB->option_id }},
                                     'opacity-50': savingAnswers[{{ $question->question_id }}]
                                 }">
                                <input class="form-check-input visually-hidden" type="radio" 
                                       name="mbti_answer_{{ $question->question_id }}" 
                                       id="option_{{ $optionB->option_id }}" 
                                       value="{{ $optionB->option_id }}"
                                       :checked="localAnswers[{{ $question->question_id }}] == {{ $optionB->option_id }}">
                                <label class="form-check-label w-100 mb-0 text-center d-flex align-items-center justify-content-center" 
                                       for="option_{{ $optionB->option_id }}">
                                     <span :class="{ 'font-weight-bolder text-white': localAnswers[{{ $question->question_id }}] == {{ $optionB->option_id }}, 'font-weight-normal text-dark': localAnswers[{{ $question->question_id }}] != {{ $optionB->option_id }} }" 
                                           class="d-block" style="font-size: 1.05rem; line-height: 1.6;">
                                        {{ $optionB->option_text }}
                                    </span>
                                </label>
                            </div>
                        @else
                            <p class="text-danger text-center">Konfigurasi soal MBTI ini tidak valid (seharusnya 2 pilihan per item).</p>
                        @endif
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
                    <span wire:loading.remove wire:target="finishTest">Selesaikan Tes MBTI</span>
                </button>
            </div>
        @else
            <p class="text-center text-secondary py-5">Tidak ada item yang tersedia untuk tes ini atau tes tidak dapat dimulai.</p> 
        @endif
    </form>
</div>

<style>
.transition-all {
    transition: all 0.2s ease-in-out;
}

.mbti-option {
    transition: all 0.15s ease-in-out;
    position: relative;
    overflow: hidden;
}

.mbti-option::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.mbti-option:hover::before {
    left: 100%;
}

.mbti-option:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.mbti-option.active-option {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(13,110,253,0.25);
}

.hover-shadow-sm:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.question-item {
    will-change: transform;
}

.mbti-option.opacity-50 {
    pointer-events: none;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.text-success i {
    animation: pulse 0.5s ease-in-out;
}

.mbti-option label {
    min-height: 60px;
}
</style>

@push('scripts')
<script>
function assessmentMBTI() {
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
                
                const globalNotifArea = document.getElementById('notification-area-mbti'); 
                if(globalNotifArea) {
                    globalNotifArea.innerHTML = ''; 
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-warning text-white alert-dismissible fade show';
                    alertDiv.setAttribute('role', 'alert');
                    alertDiv.style.fontSize = '0.875rem'; 
                    alertDiv.innerHTML = `<span class="alert-text"><strong>Ups!</strong> ${eventDetail.message}</span> <button type="button" class="btn-close text-lg py-3 opacity-10" onclick="this.parentElement.remove()" aria-label="Close"><span aria-hidden="true">&times;</span></button>`;
                    globalNotifArea.appendChild(alertDiv);
                } else { 
                    alert('Ups! ' + eventDetail.message); 
                }
            });
            
            Livewire.on('testFinishedSuccessfully', () => {
                console.log('[MBTI Blade] Event "testFinishedSuccessfully" caught.');
                window.onbeforeunload = null; 
            });
        },
        
        selectAnswer(questionId, optionId) {
            const previousAnswer = this.localAnswers[questionId];
            this.localAnswers[questionId] = optionId;
            this.savingAnswers[questionId] = true;
            this.savedAnswers[questionId] = false;
            
        }
    }
}
</script>
@endpush