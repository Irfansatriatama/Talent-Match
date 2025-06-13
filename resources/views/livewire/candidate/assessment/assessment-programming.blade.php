<div>
    {{-- BAR STICKY UNTUK INFORMASI TES --}}
    <div class="card shadow-sm mb-4 sticky-top" style="top: 15px; z-index: 1020; background-color: #ffffff;">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-lg-7 col-md-6 col-12 mb-2 mb-md-0">
                    <h5 class="mb-1 font-weight-bolder">{{ $currentTest->test_name ?? 'Tes Kemampuan Pemrograman' }}</h5>
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
                Jawablah setiap pertanyaan dengan memilih opsi yang paling tepat. Perhatikan setiap detail pada soal dan kode yang diberikan.
            </p>
        </div>
    </div>

    {{-- Area Notifikasi Session Flash --}}
    <div id="js-session-notification-area-programming" class="mb-3">
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


    @if ($showUnansweredQuestionsModal && $submissionErrorMessage)
    <div class="modal fade show" id="unansweredModalProgramming" tabindex="-1" aria-labelledby="unansweredModalLabelProgramming" 
         style="display: block; background-color: rgba(0,0,0,0.6);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bolder" id="unansweredModalLabelProgramming">
                        <i class="material-icons text-warning me-2" style="vertical-align: middle;">warning</i>
                        Konfirmasi Penyelesaian Tes
                    </h5>
                    <button type="button" class="btn-close text-dark" wire:click="closeUnansweredModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">{{ $submissionErrorMessage }}</p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn bg-gradient-secondary" wire:click="closeUnansweredModal">Kerjakan Kembali</button>
                    <button type="button" class="btn bg-gradient-success" wire:click="forceFinishTest" wire:loading.attr="disabled" wire:target="forceFinishTest, finishTest">
                        <span wire:loading wire:target="forceFinishTest, finishTest" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Ya, Selesaikan Tes Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <form wire:submit.prevent="finishTest(false)">
        @if($questions && $questions->count() > 0)
            @foreach ($questions as $index => $question)
                <div class="card mb-3 question-item" id="question-card-{{ $question->question_id }}">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between">
                           <p class="font-weight-bold text-sm mb-1">Pertanyaan {{ $index + 1 }}</p>
                        </div>
                        <div class="mb-3">
                            <div class="p-3 border rounded bg-light mb-2">
                                <pre class="text-dark mb-0" style="font-size: 0.9rem; line-height: 1.6; white-space: pre-wrap; word-wrap: break-word;"><code class="language-python">
{!! nl2br(e($question->question_text)) !!}
                                </code></pre>
                            </div>
                        </div>

                        <h6 class="text-xs text-muted mb-2">Pilih jawaban Anda:</h6>
                        <div class="row gx-3 gy-2">
                            @php $optionLetters = ['A', 'B', 'C', 'D', 'E']; @endphp
                            @foreach ($question->options as $optionIndex => $option)
                                <div class="col-md-12">
                                    <div wire:click="selectAnswer({{ $question->question_id }}, {{ $option->option_id }})"
                                         class="form-check card card-body shadow-xs border p-3 ps-4 mb-2 h-100 d-flex flex-row align-items-center cursor-pointer option-box programming-option
                                                {{ ($userAnswers[$question->question_id] ?? null) == $option->option_id ? 'active-option border-primary bg-gradient-primary text-white' : 'border-light hover-shadow-sm' }}">
                                        <input class="form-check-input visually-hidden" 
                                               type="radio" 
                                               name="answer_{{ $question->question_id }}" 
                                               id="option_{{ $option->option_id }}" 
                                               value="{{ $option->option_id }}"
                                               {{ ($userAnswers[$question->question_id] ?? null) == $option->option_id ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 mb-0 {{ ($userAnswers[$question->question_id] ?? null) == $option->option_id ? 'text-white' : 'text-dark' }}" 
                                               for="option_{{ $option->option_id }}">
                                            <span class="font-weight-bold me-2">{{ $optionLetters[$optionIndex] ?? '•' }}.</span>
                                            <span class="{{ ($userAnswers[$question->question_id] ?? null) == $option->option_id ? 'font-weight-bold' : '' }}">{{ $option->option_text }}</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="notification-area-q{{ $question->question_id }}" class="mt-2" style="min-height: 20px;"></div>
                    </div>
                </div>
            @endforeach

            <div class="mt-4 mb-3 text-center">
                <button type="submit" 
                        wire:loading.attr="disabled" 
                        wire:target="finishTest, forceFinishTest" 
                        class="btn bg-gradient-success w-100 btn-lg"
                        @if($isSubmitting) disabled @endif>
                    <span wire:loading wire:target="finishTest, forceFinishTest" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    <span wire:loading wire:target="finishTest, forceFinishTest">Memproses...</span>
                    <span wire:loading.remove wire:target="finishTest, forceFinishTest">Selesaikan Tes Pemrograman</span>
                </button>
            </div>
        @else
            <p class="text-center text-secondary py-5">Tidak ada pertanyaan yang tersedia untuk tes ini atau tes tidak dapat dimulai.</p> 
        @endif
    </form>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            console.log('[Programming Blade] Livewire init: Scripts loaded.');
            
            let jsTestCompleted = @json($testCompleted ?? false);
            let jsIsSubmitting = @json($isSubmitting ?? false);
            let jsIsNavigatingAway = false;

            Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                if (component.snapshot.data.testCompleted !== undefined) {
                    jsTestCompleted = component.snapshot.data.testCompleted;
                }
                if (component.snapshot.data.isSubmitting !== undefined) {
                    jsIsSubmitting = component.snapshot.data.isSubmitting;
                }
                if (jsTestCompleted) {
                    window.onbeforeunload = null;
                }
            });
            
            Livewire.on('answer-saved', (eventDetail) => { 
                const notifArea = document.getElementById('notification-area-q' + eventDetail.questionId);
                if(notifArea) {
                    notifArea.innerHTML = `<p class="text-xs text-success mt-1 mb-0 fst-italic">${eventDetail.message}</p>`;
                    setTimeout(() => { notifArea.innerHTML = ''; }, 2000);
                }
            });

            Livewire.on('answer-save-error', (eventDetail) => {
                const globalNotifArea = document.getElementById('js-session-notification-area-programming'); 
                if(globalNotifArea) {
                    globalNotifArea.innerHTML = ''; 
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-warning text-white alert-dismissible fade show';
                    alertDiv.innerHTML = `<span class="alert-text d-flex align-items-center"><i class="material-icons text-white me-2">warning</i><strong>Ups!</strong>&nbsp; ${eventDetail.message}</span> <button type="button" class="btn-close text-lg py-3 opacity-10" onclick="this.parentElement.remove()" aria-label="Close"><span aria-hidden="true">&times;</span></button>`;
                    globalNotifArea.appendChild(alertDiv);
                } else { 
                    alert('Ups! ' + eventDetail.message); 
                }
            });

            Livewire.on('testFinishedSuccessfullyProgramming', () => {
                console.log('[Programming Blade] Event "testFinishedSuccessfullyProgramming" caught.');
                jsTestCompleted = true;
                window.onbeforeunload = null;
            });

            window.addEventListener('beforeunload', (e) => {
                if (!jsTestCompleted && !jsIsSubmitting) {
                    const perfEntries = performance.getEntriesByType('navigation');
                    let isReload = false;
                    if (perfEntries.length > 0 && perfEntries[0].type === 'reload') {
                        isReload = true;
                    }

                    if (isReload) {
                        console.log('[Programming Blade] Reload detected, test continues.');
                    } else {
                        jsIsNavigatingAway = true;
                        console.log('[Programming Blade] Navigation away detected. Attempting to notify server.');
                        
                        const wireComponentElement = document.querySelector('[wire\\:id]');
                        if (!wireComponentElement) {
                            console.error('[Programming Blade] Livewire component element not found.');
                            return;
                        }
                        const livewireComponentId = wireComponentElement.getAttribute('wire:id');
                        const livewireComponent = Livewire.find(livewireComponentId);

                        if (livewireComponent) {
                            const componentName = livewireComponent.fingerprint.name || livewireComponent.name; 
                                                        
                            if (!componentName) {
                                console.error('[Programming Blade] Could not determine Livewire component name for sendBeacon URL.');
                                if (livewireComponent) livewireComponent.call('handleLeavePage');
                                const confirmationMessage = 'PERHATIAN! Jika Anda meninggalkan halaman ini, Tes Pemrograman akan dianggap selesai dengan jawaban yang sudah ada. Apakah Anda yakin ingin keluar?';
                                (e || window.event).returnValue = confirmationMessage;
                                return confirmationMessage;
                            }

                            const data = {
                                fingerprint: livewireComponent.fingerprint,
                                serverMemo: livewireComponent.serverMemo,
                                updates: [{
                                    type: 'callMethod',
                                    payload: { method: 'handleLeavePage', params: [] }
                                }]
                            };
                            const formData = new FormData();
                            formData.append('components', JSON.stringify([data]));
                            let livewireMessageUrl = `/livewire/message/${componentName}`;

                            if (navigator.sendBeacon) {
                                try {
                                    navigator.sendBeacon(livewireMessageUrl, formData);
                                    console.log('[Programming Blade] Sent beacon for handleLeavePage to:', livewireMessageUrl);
                                } catch (beaconError) {
                                    console.error('[Programming Blade] Error sending beacon:', beaconError, 'Falling back to direct call.');
                                    livewireComponent.call('handleLeavePage');
                                }
                            } else {
                                console.warn('[Programming Blade] navigator.sendBeacon not available. Using direct call for handleLeavePage.');
                                livewireComponent.call('handleLeavePage');
                            }
                        } else {
                            console.error('[Programming Blade] Livewire component instance not found for handleLeavePage.');
                        }
                        
                        const confirmationMessage = 'PERHATIAN! Jika Anda meninggalkan halaman ini, Tes Pemrograman akan dianggap selesai dengan jawaban yang sudah ada. Apakah Anda yakin ingin keluar?';
                        (e || window.event).returnValue = confirmationMessage;
                        return confirmationMessage;
                    }
                }
            });

            window.addEventListener('pageshow', (event) => {
                if (event.persisted || (performance.getEntriesByType("navigation")[0] && performance.getEntriesByType("navigation")[0].type === 'back_forward')) {
                    console.log('[Programming Blade] Returned via back/forward. Client state: testCompleted=' + jsTestCompleted);
             
                    if (event.persisted && !jsTestCompleted) {
                        const wireComponentElement = document.querySelector('[wire\\:id]');
                        if (wireComponentElement) {
                           const livewireComponent = Livewire.find(wireComponentElement.getAttribute('wire:id'));
                           if (livewireComponent) {
                               console.log('[Programming Blade] Page loaded from bfcache, consider $refresh or full reload if state is stale.');
                           }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</div>