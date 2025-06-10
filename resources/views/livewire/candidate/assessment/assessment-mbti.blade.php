<div>
    {{-- BAR STICKY UNTUK INFORMASI TES --}}
    <div class="card shadow-sm mb-4 sticky-top" style="top: 15px; z-index: 1020; background-color: #ffffff;">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-6 col-12 mb-2 mb-md-0">
                    <h5 class="mb-1 font-weight-bolder">{{ $currentTest->test_name ?? 'Tes Kepribadian MBTI' }}</h5>
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

    {{-- DESKRIPSI TES --}}
    <div class="card shadow-none border mb-4">
        <div class="card-body p-3">
            <p class="text-sm mb-0 text-secondary">
                Untuk setiap pasangan pernyataan, pilih salah satu yang paling menggambarkan diri Anda. Tidak ada jawaban benar atau salah. Pilihlah yang paling alami bagi Anda.
            </p>
        </div>
    </div>
    
    {{-- Area Notifikasi Modal & Session Flash --}}
    <div id="notification-area-mbti" class="mb-3">
        {{-- MODAL UNTUK NOTIFIKASI SOAL BELUM DIJAWAB --}}
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
        {{-- AKHIR MODAL --}}

        {{-- Notifikasi dari session() akan muncul di sini setelah redirect --}}
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
                        <p class="font-weight-bold text-sm mb-3 text-center">Activity {{ $index + 1 }}</p>
                        {{-- Untuk MBTI, question_text di model Question bisa berisi instruksi umum --}}
                        {{-- seperti "Pilih pernyataan yang lebih menggambarkan diri Anda:" --}}
                        {{-- Karena tiap opsi adalah pernyataan itu sendiri. --}}
                        {{-- Jika question_text di seeder Anda kosong untuk MBTI, baris di bawah ini bisa dikomentari --}}
                        @if($question->question_text !== 'Pilih pernyataan yang paling menggambarkan diri Anda:')
                            <h6 class="font-weight-bolder mb-4 text-center text-gray-700">{{ $question->question_text }}</h6>
                        @endif
                        
                        @if($question->options->count() == 2)
                            @php
                                $optionA = $question->options[0];
                                $optionB = $question->options[1];
                            @endphp

                            {{-- Opsi A --}}
                            <div wire:click="selectAnswer({{ $question->question_id }}, {{ $optionA->option_id }})"
                                 class="form-check card card-body shadow-xs border mb-3 p-3 p-md-4 cursor-pointer option-box mbti-option
                                        {{ ($userAnswers[$question->question_id] ?? null) == $optionA->option_id ? 'active-option border-primary bg-gradient-primary text-white' : 'border-light hover-shadow-sm' }}">
                                <input class="form-check-input visually-hidden" type="radio" name="mbti_answer_{{ $question->question_id }}" id="option_{{ $optionA->option_id }}" value="{{ $optionA->option_id }}"
                                       {{ ($userAnswers[$question->question_id] ?? null) == $optionA->option_id ? 'checked' : '' }}>
                                <label class="form-check-label w-100 mb-0 text-center" for="option_{{ $optionA->option_id }}">
                                    <span class="{{ ($userAnswers[$question->question_id] ?? null) == $optionA->option_id ? 'font-weight-bolder text-white' : 'font-weight-normal text-dark' }} d-block" style="font-size: 1.05rem; line-height: 1.6;">
                                        {{ $optionA->option_text }}
                                    </span>
                                </label>
                            </div>

                            {{-- Opsi B --}}
                            <div wire:click="selectAnswer({{ $question->question_id }}, {{ $optionB->option_id }})"
                                 class="form-check card card-body shadow-xs border mb-3 p-3 p-md-4 cursor-pointer option-box mbti-option
                                        {{ ($userAnswers[$question->question_id] ?? null) == $optionB->option_id ? 'active-option border-primary bg-gradient-primary text-white' : 'border-light hover-shadow-sm' }}">
                                <input class="form-check-input visually-hidden" type="radio" name="mbti_answer_{{ $question->question_id }}" id="option_{{ $optionB->option_id }}" value="{{ $optionB->option_id }}"
                                       {{ ($userAnswers[$question->question_id] ?? null) == $optionB->option_id ? 'checked' : '' }}>
                                <label class="form-check-label w-100 mb-0 text-center" for="option_{{ $optionB->option_id }}">
                                     <span class="{{ ($userAnswers[$question->question_id] ?? null) == $optionB->option_id ? 'font-weight-bolder text-white' : 'font-weight-normal text-dark' }} d-block" style="font-size: 1.05rem; line-height: 1.6;">
                                        {{ $optionB->option_text }}
                                    </span>
                                </label>
                            </div>
                        @else
                            <p class="text-danger text-center">Konfigurasi soal MBTI ini tidak valid (seharusnya 2 pilihan per item).</p>
                        @endif
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
                    <span wire:loading.remove wire:target="finishTest">Selesaikan Tes MBTI</span>
                </button>
            </div>
        @else
            <p class="text-center text-secondary py-5">Tidak ada item yang tersedia untuk tes ini atau tes tidak dapat dimulai.</p> 
        @endif
    </form>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            console.log('[MBTI Blade] Livewire init: Scripts loaded.');
            
            Livewire.on('answer-saved', (eventDetail) => { 
                const notifArea = document.getElementById('notification-area-q' + eventDetail.questionId);
                if(notifArea) {
                    notifArea.innerHTML = `<p class="text-xs text-success mt-1 mb-0 fst-italic">${eventDetail.message}</p>`;
                    setTimeout(() => { notifArea.innerHTML = ''; }, 2000);
                }
            });
            
            Livewire.on('answer-save-error', (eventDetail) => {
                const globalNotifArea = document.getElementById('notification-area-mbti'); 
                if(globalNotifArea) {
                    globalNotifArea.innerHTML = ''; 
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-warning text-white alert-dismissible fade show';
                    alertDiv.setAttribute('role', 'alert');
                    alertDiv.style.fontSize = '0.875rem'; 
                    alertDiv.innerHTML = `<span class="alert-text"><strong>Ups!</strong> ${eventDetail.message}</span> <button type="button" class="btn-close text-lg py-3 opacity-10" onclick="this.parentElement.remove()" aria-label="Close"><span aria-hidden="true">&times;</span></button>`;
                    globalNotifArea.appendChild(alertDiv);
                } else { alert('Ups! ' + eventDetail.message); }
            });

            Livewire.on('testFinishedSuccessfully', () => {
                console.log('[MBTI Blade] Event "testFinishedSuccessfully" caught.');
                window.onbeforeunload = null; 
            });
        });
    </script>
    @endpush
</div>