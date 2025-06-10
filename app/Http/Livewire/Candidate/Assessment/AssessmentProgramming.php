<?php

namespace App\Http\Livewire\Candidate\Assessment;

use App\Models\Test;
use App\Models\UserAnswer;
use App\Models\UserTestProgress;
use App\Models\TestSession;
use App\Services\TestScoringService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class AssessmentProgramming extends Component
{
    public ?Test $testModel = null;
    public $questions;
    public array $userAnswers = [];
    public ?UserTestProgress $progress = null;
    public ?int $timeLimitMinutes = null;
    public ?int $timeRemaining = null;
    
    public bool $testCompleted = false;
    public bool $showErrorView = false; 
    public string $errorMessage = '';   
    public string $pageTitle = 'Tes Kemampuan Pemrograman';
    public bool $isSubmitting = false;

    public bool $showUnansweredQuestionsModal = false;
    public ?string $submissionErrorMessage = null;

    private bool $isNewSessionInitiatedThisMountCycle = false;

    const PROGRAMMING_TEST_ID = 1;

    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            $this->setErrorState('Sesi pengguna tidak ditemukan. Silakan login kembali.');
            session()->flash('error', 'Sesi tidak valid.');
            return $this->redirectRoute('login');
        }

        try {
            $this->testModel = Test::findOrFail(self::PROGRAMMING_TEST_ID);
            $this->pageTitle = $this->testModel->test_name ?? $this->pageTitle;
            $this->timeLimitMinutes = $this->testModel->time_limit_minutes;
        } catch (ModelNotFoundException $e) {
            Log::error('AssessmentProgramming Mount: Test model not found', ['test_id' => self::PROGRAMMING_TEST_ID, 'exception' => $e->getMessage()]);
            $this->setErrorState('Konfigurasi tes tidak ditemukan.');
            return;
        }

        $this->progress = UserTestProgress::firstOrNew(
            ['user_id' => $user->id, 'test_id' => $this->testModel->test_id],
            ['status' => 'not_started', 'started_at' => null]
        );

        if ($this->progress->exists) {
            $this->progress->refresh();
        }
        
        Log::info('Programming Mount: Initial progress check.', [
            'progress_id' => $this->progress->id ?? 'NEW', 
            'exists' => $this->progress->exists,
            'status' => $this->progress->status, 
            'started_at' => $this->progress->started_at ? $this->progress->started_at->toDateTimeString() : null
        ]);

        if ($this->progress->status === 'completed') {
            $this->testCompleted = true; 
            Log::info('Programming Mount: Test already completed as per DB.', ['progress_id' => $this->progress->id]);
            return; 
        }


        if ($this->progress->status === 'not_started') {
            if (!$this->progress->exists) {
                $this->progress->save();
                Log::info('Programming Mount: Saved new "not_started" progress record.', ['id' => $this->progress->id]);
            }
            Log::info('Programming Mount: Status is "not_started". Starting new test session.');
            $this->startNewTestSession($user);
            Log::info('Programming Mount: New session started.', ['progress_id' => $this->progress->id, 'started_at' => $this->progress->started_at->toDateTimeString()]);
        }
        
        $this->isNewSessionInitiatedThisMountCycle = false; 

        if (!$this->testCompleted && !$this->showErrorView) {
            if (!$this->loadQuestions()) { return; }
            $this->loadExistingAnswers($user->id);
            
            if ($this->progress->started_at) { 
                $this->calculateRemainingTime();
                if ($this->timeRemaining !== null && $this->timeRemaining <= 0) {
                    if (!$this->isSubmitting) $this->handleTimeExpired(); 
                }
            } else {
                 if ($this->progress->status === 'in_progress') {
                     Log::critical('Programming Mount: CRITICAL - started_at is null for "in_progress" session post-init.', ['progress_id' => $this->progress->id]);
                     $this->setErrorState('Kesalahan internal: Waktu mulai tes tidak terdefinisi dengan benar untuk sesi yang sedang berjalan.');
                 }
            }
        }
        Log::info('Programming Mount: End of mount logic for active test.', ['time_remaining' => $this->timeRemaining, 'testCompleted' => $this->testCompleted, 'showErrorView' => $this->showErrorView]);
    }
    
    protected function setErrorState(string $message): void { 
        $this->showErrorView = true; 
        $this->errorMessage = $message; 
        Log::warning('Programming: Error state set', ['message' => $message]); 
    }
    
    protected function startNewTestSession($user): void { 
        try {
            DB::transaction(function () use ($user) {
                if (!$this->testModel) throw new \Exception("Test model not loaded in startNewTestSession.");
                $questionIds = $this->testModel->questions()->pluck('question_id');
                
                UserAnswer::where('user_id', $user->id)->whereIn('question_id', $questionIds)->delete();
                $this->userAnswers = [];

                $this->progress->status = 'in_progress';
                $this->progress->started_at = now();
                $this->progress->score = null;
                $this->progress->result_summary = null;
                $this->progress->completed_at = null;
                $this->progress->time_spent_seconds = null;
                $this->progress->save();

                TestSession::create([
                    'user_id' => $user->id,
                    'test_id' => $this->testModel->test_id,
                    'started_at' => $this->progress->started_at,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
                $this->isNewSessionInitiatedThisMountCycle = true; 
            });
            Log::info('Programming: New test session transaction completed.', ['user_id' => $user->id, 'started_at' => $this->progress->started_at->toDateTimeString()]);
        } catch (\Exception $e) {
            Log::error('Programming: Failed to start new test session', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            $this->setErrorState('Gagal memulai sesi tes baru.');
        }
    }
    
    protected function loadQuestions(): bool { 
        if (!$this->testModel) { $this->setErrorState('Model tes tidak terinisialisasi.'); return false; }
        $this->questions = $this->testModel->questions()->with('options')->orderBy('question_order')->get();
        if ($this->questions->isEmpty()) {
            Log::error('Programming: No questions found for test', ['test_id' => $this->testModel->test_id]);
            $this->setErrorState('Tidak ada pertanyaan ditemukan untuk tes ini.');
            if ($this->progress && $this->progress->status === 'in_progress' && !$this->isSubmitting) {
                try {
                    $this->isSubmitting = true;
                    $this->executeTestCompletion(Auth::user(), true, 'Error: Tidak ada pertanyaan ditemukan.');
                } catch (\Exception $e) {
                    Log::error('Programming loadQuestions: Failed to auto-complete test with no questions.', ['error' => $e->getMessage()]);
                  
                } finally {
                    $this->isSubmitting = false;
                }
            }
            return false;
        }
        return true;
    }

    protected function loadExistingAnswers(int $userId): void { 
        if (!$this->questions || $this->questions->isEmpty()) return;
        $existingAnswers = UserAnswer::where('user_id', $userId)->whereIn('question_id', $this->questions->pluck('question_id'))->get();
        foreach ($existingAnswers as $answer) { 
            $this->userAnswers[$answer->question_id] = $answer->selected_option_id; 
        }
        Log::info('Programming: Loaded existing answers', ['user_id' => $userId, 'answers_count' => count($this->userAnswers)]);
    }

    protected function calculateRemainingTime(): void { 
        if (!$this->timeLimitMinutes || !$this->progress || !$this->progress->started_at) { $this->timeRemaining = null; return; }
        if ($this->testCompleted || $this->showErrorView) { $this->timeRemaining = 0; return; }
        $startTime = ($this->progress->started_at instanceof Carbon) ? $this->progress->started_at : Carbon::parse($this->progress->started_at);
        $elapsedSeconds = now()->diffInSeconds($startTime, true);
        $totalSeconds = $this->timeLimitMinutes * 60;
        $this->timeRemaining = max(0, $totalSeconds - $elapsedSeconds);
    }

    public function handleTimerTick(): void { 
        if ($this->testCompleted || $this->showErrorView || $this->isSubmitting) { return; }
        $this->calculateRemainingTime();
        if ($this->timeRemaining !== null && $this->timeRemaining <= 0) {
            Log::info('Programming: Time expired via handleTimerTick.');
            if (!$this->isSubmitting) $this->handleTimeExpired();
        }
    }

    protected function handleTimeExpired(): void { 
        if ($this->testCompleted || $this->showErrorView || $this->isSubmitting) { return; }
        Log::info('Programming: Handling time expiration.');
        
        try {
            $this->isSubmitting = true;
            $this->executeTestCompletion(Auth::user(), true, 'Waktu habis');
            if ($this->testCompleted) { 
                session()->flash('warning', 'Waktu pengerjaan Tes Pemrograman telah habis dan tes otomatis diselesaikan.');
                $this->redirectRoute('candidate.assessment.test');
            }
        } catch (\Exception $e) {
            Log::error('Programming handleTimeExpired: Exception during completion.', ['error' => $e->getMessage()]);
            $this->setErrorState('Gagal menyelesaikan tes karena waktu habis.');
        } finally {
             if (!$this->testCompleted || $this->showErrorView) {
                $this->isSubmitting = false;
            }
        }
    }

    public function selectAnswer($questionId, $optionId): void { 
        if ($this->testCompleted || $this->showErrorView || $this->isSubmitting) return;
        if ($this->timeLimitMinutes) {
            $this->calculateRemainingTime();
            if ($this->timeRemaining <= 0) {
                if (!$this->isSubmitting) $this->handleTimeExpired();
                return;
            }
        }
        try {
            $this->userAnswers[$questionId] = $optionId;
            UserAnswer::updateOrCreate(
                ['user_id' => Auth::id(), 'question_id' => $questionId],
                ['selected_option_id' => $optionId, 'riasec_score_selected' => null, 'answered_at' => now()]
            );
            $this->dispatch('answer-saved', questionId: $questionId, message: 'Jawaban disimpan.');
            Log::info('Programming: Answer selected', ['q_id' => $questionId, 'opt_id' => $optionId]);
        } catch (\Exception $e) {
            Log::error('Programming: Failed to save answer', ['q_id' => $questionId, 'error' => $e->getMessage()]);
            $this->dispatch('answer-save-error', message: 'Gagal menyimpan jawaban.');
        }
    }
    
    public function finishTest(bool $autoSubmit = false, string $summaryMessage = 'Selesai'): void
    {
        if ($this->testCompleted || $this->showErrorView || ($this->isSubmitting && !$autoSubmit) ) {
            Log::info('Programming: Manual finishTest called but conditions not met or already submitting.', ['completed' => $this->testCompleted, 'error' => $this->showErrorView, 'submitting' => $this->isSubmitting]);
            if ($this->isSubmitting && !$autoSubmit) $this->isSubmitting = false;
            return;
        }
        
        $this->isSubmitting = true;
        $this->submissionErrorMessage = null;

        Log::info('Programming finishTest (Manual): Attempting.', ['user_id' => Auth::id()]);
        
        $user = Auth::user();
        if (!$user || !$this->progress || !$this->progress->started_at || empty($this->questions)) { 
            Log::error('Programming finishTest (Manual): Invalid state (user/progress/started_at/questions missing).'); 
            $this->setErrorState('Sesi atau data tes tidak valid.');
            $this->isSubmitting = false; 
            return;
        }

        $totalQuestions = $this->questions->count();
        $answeredQuestions = count($this->userAnswers);
        
        if (!$autoSubmit && $totalQuestions > 0 && $answeredQuestions < $totalQuestions) {
             $this->submissionErrorMessage = "Anda belum menjawab semua pertanyaan ({$answeredQuestions}/{$totalQuestions}). Apakah Anda yakin ingin menyelesaikan tes sekarang dengan jawaban yang ada, atau kembali mengerjakan?";
             Log::warning('Programming finishTest (Manual): Not all questions answered: ' . $this->submissionErrorMessage);
             $this->showUnansweredQuestionsModal = true;
             $this->isSubmitting = false; 
             return; 
        }

        Log::info('Programming finishTest (Manual): Validation passed. Proceeding to execute.');
        try {
            $this->executeTestCompletion($user, false, $summaryMessage);
            session()->flash('success', 'Tes ' . ($this->testModel->test_name ?? 'Pemrograman') . ' telah selesai!');
            $this->redirectRoute('candidate.assessment.test');
        } catch (\Exception $e) {
            Log::error('Programming finishTest (Manual): Exception during completion.', ['error' => $e->getMessage()]);
            $this->setErrorState('Terjadi kesalahan teknis saat menyimpan hasil tes.');
            $this->isSubmitting = false;
        }
    }

    public function forceFinishTest() 
    {
        Log::info('Programming forceFinishTest: User chose to finish from modal.');
        $this->closeUnansweredModal();
        
        if ($this->isSubmitting) {
             Log::warning('Programming forceFinishTest: Already submitting, action ignored.');
             return;
        }
        
        try {
            $this->isSubmitting = true;
            Log::info('Programming forceFinishTest: Proceeding to executeTestCompletion.');
            $user = Auth::user();
            if (!$user || !$this->progress) { throw new \Exception("User or progress not available for forceFinishTest.");}
            
            if (!$this->testModel) {
                $this->testModel = Test::find(self::PROGRAMMING_TEST_ID);
                if (!$this->testModel) throw new \Exception("Test model could not be loaded for forceFinishTest.");
            }

            $this->executeTestCompletion($user, true, 'Diselesaikan oleh pengguna (jawaban tidak lengkap)');
            session()->flash('success', 'Tes ' . ($this->testModel->test_name ?? 'Pemrograman') . ' telah diselesaikan.');
            $this->redirectRoute('candidate.assessment.test');
        } catch (\Exception $e) {
             Log::error('Programming forceFinishTest: Exception.', ['error' => $e->getMessage()]);
             $this->setErrorState('Terjadi kesalahan teknis saat menyelesaikan tes.');
             $this->isSubmitting = false;
        }
    }

    protected function executeTestCompletion($user, bool $isConsideredAutoSubmit, string $summaryMessage): void
    {
        if (!$this->progress) {
            throw new \Exception("UserTestProgress is not loaded in executeTestCompletion for user {$user->id}, test ID " . ($this->testModel->test_id ?? self::PROGRAMMING_TEST_ID));
        }
        if (!$this->testModel) {
            $this->testModel = Test::find($this->progress->test_id);
            if (!$this->testModel) {
                throw new \Exception("TestModel is not loaded and could not be reloaded in executeTestCompletion for user {$user->id}, test ID {$this->progress->test_id}");
            }
        }
        if (!$this->progress->started_at) {
             Log::critical('Programming executeTestCompletion: started_at is NULL! Setting default based on time limit.', ['progress_id' => $this->progress->id]);
             $this->progress->started_at = now()->subMinutes($this->testModel->time_limit_minutes ?? 60); 
        }

        try {
            DB::transaction(function () use ($user, $isConsideredAutoSubmit, $summaryMessage) {
                $scoringService = app(TestScoringService::class);
                $scoreResult = $scoringService->calculateScore($this->testModel, $user);
                
                $startTime = ($this->progress->started_at instanceof Carbon) ? $this->progress->started_at : Carbon::parse($this->progress->started_at);
                $timeSpentSeconds = now()->diffInSeconds($startTime);
                if ($this->testModel->time_limit_minutes) {
                     $timeSpentSeconds = min($timeSpentSeconds, $this->testModel->time_limit_minutes * 60);
                }

                $this->progress->update([
                    'status' => 'completed',
                    'score' => $scoreResult['score'] ?? 0,
                    'result_summary' => $isConsideredAutoSubmit ? $summaryMessage : ($scoreResult['summary'] ?? 'Selesai'),
                    'completed_at' => now(),
                    'time_spent_seconds' => $timeSpentSeconds
                ]);
                
                $session = TestSession::where('user_id', $user->id)
                          ->where('test_id', $this->testModel->test_id)
                          ->whereNull('completed_at')->latest('started_at')->first();
                if ($session) {
                    $session->update(['completed_at' => now(), 'time_spent_seconds' => $timeSpentSeconds]);
                }
                $this->testCompleted = true;
                $this->timeRemaining = 0;
                Log::info('Programming: Test transaction completed successfully in DB.', ['user_id' => $user->id, 'progress_id' => $this->progress->id]);
                $this->dispatch('testFinishedSuccessfullyProgramming');
            });
        } catch (\Exception $e) {
            Log::error('Programming executeTestCompletion: Exception during DB transaction.', ['error' => $e->getMessage(), 'trace_snippet' => substr($e->getTraceAsString(), 0, 500)]);
           
            throw $e; 
        }
    }

    public function handleLeavePage()
    {
        $user = Auth::user();
        if (!$user || !$this->progress || $this->progress->status !== 'in_progress' || $this->testCompleted || $this->isSubmitting) {
            Log::info('Programming handleLeavePage: Conditions not met or test already handled.', [
                'user_id' => $user->id ?? null,
                'progress_status' => $this->progress->status ?? 'N/A',
                'test_completed' => $this->testCompleted,
                'is_submitting' => $this->isSubmitting
            ]);
            return;
        }

        Log::warning('Programming handleLeavePage: User is leaving the page. Auto-finishing test.', [
            'user_id' => $user->id,
            'progress_id' => $this->progress->id,
        ]);

        if (!$this->testModel) {
            try {
                $this->testModel = Test::findOrFail(self::PROGRAMMING_TEST_ID);
            } catch (ModelNotFoundException $e) {
                Log::error('Programming handleLeavePage: Test model not found during attempt to load.', ['exception' => $e->getMessage()]);
                
                return;
            }
        }
        
        try {
            $this->isSubmitting = true;
            $this->executeTestCompletion($user, true, 'Tes dihentikan karena meninggalkan halaman.');
        } catch (\Exception $e) {
             Log::error('Programming handleLeavePage: Exception during auto-finish transaction.', ['error' => $e->getMessage()]);
            
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function closeUnansweredModal() 
    {
        $this->showUnansweredQuestionsModal = false;
        $this->submissionErrorMessage = null;  
    }

    public function render() 
    {
        $testNameForView = $this->testModel ? $this->testModel->test_name : $this->pageTitle;

        if ($this->showErrorView) {
            return view('livewire.candidate.assessment.assessment-error', [
                'errorMessage' => $this->errorMessage, 'testName' => $testNameForView
            ])->layout('layouts.test-taker');
        }
        if ($this->testCompleted) {
            return view('livewire.candidate.assessment.assessment-completed', ['testName' => $testNameForView])->layout('layouts.test-taker');
        }
        
        if (empty($this->questions)) { 
             if (!$this->showErrorView && !$this->testCompleted) { 
                Log::critical('Programming render: Questions collection empty unexpectedly.', ['progress_id' => $this->progress->id ?? null]);
                
                if (!$this->errorMessage) { 
                    $this->errorMessage = 'Gagal memuat data pertanyaan tes (render).';
                }
                 return view('livewire.candidate.assessment.assessment-error', [
                    'errorMessage' => $this->errorMessage,
                    'testName' => $testNameForView
                ])->layout('layouts.test-taker');
             }
        }
        
        if (!$this->isSubmitting && $this->timeLimitMinutes && $this->progress && $this->progress->started_at) {
            $this->calculateRemainingTime();
        }

        return view('livewire.candidate.assessment.assessment-programming', [
            'currentTest' => $this->testModel,
            'questions' => $this->questions,
            'totalQuestions' => $this->questions ? $this->questions->count() : 0, 
            'answeredQuestions' => count($this->userAnswers),
            'userAnswers' => $this->userAnswers,
            'timeRemaining' => $this->timeRemaining,
            'timeLimitMinutes' => $this->timeLimitMinutes,
            'isSubmitting' => $this->isSubmitting,
            'submissionErrorMessage' => $this->submissionErrorMessage,
            'showUnansweredQuestionsModal' => $this->showUnansweredQuestionsModal
        ])->layout('layouts.test-taker');
    }
}