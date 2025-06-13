<?php

namespace App\Http\Livewire\Candidate\Assessment;

use App\Models\Test;
use App\Models\Question; 
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

class AssessmentRiasec extends Component
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
    public string $pageTitle = 'Tes Minat RIASEC';
    public bool $isSubmitting = false;

    public bool $showUnansweredQuestionsModal = false;
    public ?string $submissionErrorMessage = null;

    const RIASEC_TEST_ID = 2; 
    const PREVIOUS_TEST_ID = 1;  
    const PREVIOUS_TEST_NAME = 'Tes Kemampuan Pemrograman'; 

    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            $this->setErrorState('Sesi pengguna tidak ditemukan. Silakan login kembali.'); 
            session()->flash('error', 'Sesi tidak valid.');
            return $this->redirectRoute('login');
        }

        try {
            $this->testModel = Test::findOrFail(self::RIASEC_TEST_ID); 
            $this->pageTitle = $this->testModel->test_name ?? $this->pageTitle; 
            $this->timeLimitMinutes = $this->testModel->time_limit_minutes; 
        } catch (ModelNotFoundException $e) {
            Log::error('AssessmentRiasec: Test model not found', ['test_id' => self::RIASEC_TEST_ID, 'exception' => $e->getMessage()]); 
            $this->setErrorState('Konfigurasi tes tidak ditemukan. Harap hubungi administrator.'); 
            return;
        }

        $this->progress = UserTestProgress::firstOrCreate(
            ['user_id' => $user->id, 'test_id' => $this->testModel->test_id],
            ['status' => 'not_started', 'started_at' => null] 
        );

        if (!$this->progress->wasRecentlyCreated && $this->progress->started_at) {
            $this->progress->refresh(); 
        }
        
        Log::info('RIASEC mount: Progress loaded.', ['id' => $this->progress->id ?? 'NEW', 'status' => $this->progress->status, 'started_at' => $this->progress->started_at ? $this->progress->started_at->toDateTimeString() : null]); 

        if ($this->progress->status === 'completed') { 
            $this->testCompleted = true; 
            return;
        }

        if (!$this->checkPrerequisite($user->id)) { 
            $this->setErrorState('Anda harus menyelesaikan ' . self::PREVIOUS_TEST_NAME . ' terlebih dahulu.'); 
            return;
        }

        $this->initializeTestSession($user); 

        if ($this->showErrorView) return; 

        if (!$this->loadQuestions()) { 
            return; 
        }

        $this->loadExistingAnswers($user->id); 
        
        if ($this->progress->started_at && !$this->testCompleted && !$this->showErrorView) {
            $this->calculateRemainingTime(); 
            if ($this->timeRemaining !== null && $this->timeRemaining <= 0) {
                if (!$this->isSubmitting) $this->handleTimeExpired(); 
            }
        } else if (!$this->progress->started_at && $this->progress->status === 'in_progress') {
             Log::error('AssessmentRiasec mount: In_progress with null started_at after init. Critical error.', ['progress_id' => $this->progress->id]); 
             $this->setErrorState('Gagal memuat sesi tes. Waktu mulai tidak tervalidasi.'); 
        } else {
             $this->timeRemaining = null; 
        }
        Log::info('RIASEC: Mount completed.', ['time_remaining_on_mount' => $this->timeRemaining]); 
    }

    protected function setErrorState(string $message): void { 
        $this->showErrorView = true; 
        $this->errorMessage = $message; 
        Log::warning('RIASEC: Error state set in component', ['message' => $message]); 
    }

    protected function checkPrerequisite(int $userId): bool { 
        $previousProgress = UserTestProgress::where('user_id', $userId) 
            ->where('test_id', self::PREVIOUS_TEST_ID) 
            ->where('status', 'completed') 
            ->first(); 
        return $previousProgress !== null; 
    }

    protected function initializeTestSession($user): void { 
        if ($this->progress->status === 'not_started') { 
            Log::info('RIASEC: Initializing new test session for not_started progress', ['user_id' => $user->id]); 
            $this->startNewTestSession($user); 
            $this->progress->refresh(); 
        } elseif ($this->progress->status === 'in_progress' && !$this->progress->started_at) { 
            Log::warning('RIASEC: In-progress test missing started_at during initializeTestSession, setting now', ['progress_id' => $this->progress->id]); 
            $this->progress->update(['started_at' => now()]); 
            $this->progress->refresh(); 
        }
    }

    protected function startNewTestSession($user): void { 
        try {
            DB::transaction(function () use ($user) { 
                if (!$this->testModel) throw new \Exception("Test model not loaded in startNewTestSession.");
                $questionIds = $this->testModel->questions()->pluck('question_id'); 
                UserAnswer::where('user_id', $user->id) 
                    ->whereIn('question_id', $questionIds) 
                    ->delete(); 
                Log::info('RIASEC startNewTestSession: Cleared previous answers.', ['user_id' => $user->id, 'test_id' => $this->testModel->test_id]); 
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
            });
            Log::info('RIASEC: New test session started successfully in DB.', ['user_id' => $user->id, 'started_at' => $this->progress->started_at ? $this->progress->started_at->toDateTimeString() : 'N/A']); 
        } catch (\Exception $e) {
            Log::error('RIASEC: Failed to start new test session transaction', ['user_id' => $user->id, 'error' => $e->getMessage()]); 
            $this->setErrorState('Gagal memulai sesi tes baru. Silakan coba lagi.'); 
        }
    }

    protected function loadQuestions(): bool { 
        if (!$this->testModel) {
            $this->setErrorState('Model tes tidak terinisialisasi.'); 
            return false; 
        }
        $this->questions = $this->testModel->questions()->orderBy('question_order')->get(); 
        if ($this->questions->isEmpty()) { 
            Log::error('RIASEC: No questions found for test', ['test_id' => $this->testModel->test_id]); 
            $this->setErrorState('Tidak ada pertanyaan ditemukan untuk tes ini.'); 
            if ($this->progress && $this->progress->status === 'in_progress' && !$this->isSubmitting) { 
                $this->isSubmitting = true;
                $this->completeTestTransaction(Auth::user(), true, 'Error: Tidak ada pertanyaan ditemukan.');
            }
            return false; 
        }
        return true; 
    }

    protected function loadExistingAnswers(int $userId): void { 
        if (!$this->questions || $this->questions->isEmpty()) return; 
        $existingAnswers = UserAnswer::where('user_id', $userId) 
            ->whereIn('question_id', $this->questions->pluck('question_id')) 
            ->get(); 
        foreach ($existingAnswers as $answer) {
            $this->userAnswers[$answer->question_id] = $answer->riasec_score_selected; 
        }
        Log::info('RIASEC: Loaded existing answers', ['user_id' => $userId, 'answers_count' => count($this->userAnswers)]); 
    }

    protected function calculateRemainingTime(): void { 
        if (!$this->timeLimitMinutes || !$this->progress || !$this->progress->started_at) { 
            $this->timeRemaining = null; 
            return; 
        }
        if ($this->testCompleted || $this->showErrorView) { 
             $this->timeRemaining = 0; 
             return; 
        }
        $startTime = ($this->progress->started_at instanceof Carbon)  
            ? $this->progress->started_at 
            : Carbon::parse($this->progress->started_at); 
        $elapsedSeconds = now()->diffInSeconds($startTime, true); 
        $totalSeconds = $this->timeLimitMinutes * 60; 
        $this->timeRemaining = max(0, $totalSeconds - $elapsedSeconds); 
    }

    public function handleTimerTick(): void { 
        if ($this->testCompleted || $this->showErrorView || $this->isSubmitting) { 
            return; 
        }
        $this->calculateRemainingTime(); 
        if ($this->timeRemaining !== null && $this->timeRemaining <= 0) {
            Log::info('RIASEC: Time expired during timer tick (handleTimerTick).'); 
            if (!$this->isSubmitting) $this->handleTimeExpired(); 
        }
    }

    protected function handleTimeExpired(): void { 
        if ($this->testCompleted || $this->showErrorView || $this->isSubmitting) { 
            return; 
        }
        Log::info('RIASEC: Handling time expiration logic.'); 
        $this->finishTest(true, 'Waktu habis'); 
        if ($this->testCompleted) {
            session()->flash('warning', 'Waktu pengerjaan Tes RIASEC telah habis dan tes otomatis diselesaikan.');
            $this->redirectRoute('candidate.assessment.test');
        }
    }

    public function selectAnswer($questionId, $scoreSelected): void { 
        if ($this->testCompleted || $this->showErrorView || $this->isSubmitting) return; 
        if ($this->timeLimitMinutes) { 
            $this->calculateRemainingTime(); 
            if ($this->timeRemaining <= 0) { 
                if (!$this->isSubmitting) $this->handleTimeExpired(); 
                $this->dispatch('answer-save-error', message: 'Waktu telah habis. Jawaban tidak dapat disimpan.'); 
                return; 
            }
        }
        try {
            $this->userAnswers[$questionId] = $scoreSelected; 
            UserAnswer::updateOrCreate( 
                ['user_id' => Auth::id(), 'question_id' => $questionId], 
                ['riasec_score_selected' => $scoreSelected, 'selected_option_id' => null, 'answered_at' => now()] 
            );
            $this->dispatch('answer-saved', questionId: $questionId, message: 'Pilihan disimpan.'); 
            Log::info('RIASEC: Answer selected', ['q_id' => $questionId, 'score' => $scoreSelected]); 
        } catch (\Exception $e) {
            Log::error('RIASEC: Failed to save answer', ['question_id' => $questionId, 'error' => $e->getMessage()]); 
            $this->dispatch('answer-save-error', message: 'Gagal menyimpan jawaban. Silakan coba lagi.'); 
        }
    }

    public function closeUnansweredModal()
    {
        $this->showUnansweredQuestionsModal = false;
        $this->submissionErrorMessage = null; 
        }

    public function finishTest(bool $autoSubmit = false, string $summaryMessage = 'Selesai'): void
    {
        if ($this->testCompleted || $this->showErrorView || ($this->isSubmitting && !$autoSubmit) ) {
            Log::info('RIASEC: Finish test called but conditions not met or already submitting.', [
                'completed' => $this->testCompleted, 'error' => $this->showErrorView, 'submitting' => $this->isSubmitting
            ]);
            if ($this->isSubmitting && !$autoSubmit) $this->isSubmitting = false; 
        }

        $this->isSubmitting = true;
        $this->submissionErrorMessage = null;

        Log::info('RIASEC finishTest: Attempting to finish test.', [
            'autoSubmit' => $autoSubmit, 
            'user_id' => Auth::id(),
            'current_answered_count' => count($this->userAnswers),
            'total_questions' => $this->questions ? $this->questions->count() : 'N/A'
        ]);
        
        $user = Auth::user();
        if (!$user || !$this->progress || !$this->progress->started_at) {
            Log::error('RIASEC finishTest: Invalid state (user, progress, or started_at missing). Cannot finish.');
            $this->setErrorState('Sesi tes tidak valid untuk menyelesaikan.');
            $this->isSubmitting = false;
            return;
        }
        
        if (empty($this->questions)) {
            Log::error('RIASEC finishTest: Questions not loaded. Cannot validate or score.');
            $this->setErrorState('Tidak dapat memvalidasi jawaban karena data pertanyaan tidak ada.');
            $this->isSubmitting = false;
            return;
        }

        $totalQuestions = $this->questions->count();
        $answeredQuestions = count($this->userAnswers);
        
        if (!$autoSubmit && $totalQuestions > 0 && $answeredQuestions < $totalQuestions) {
             $this->submissionErrorMessage = "Harap berikan tanggapan untuk semua pernyataan. ({$answeredQuestions}/{$totalQuestions} dijawab)";
             Log::warning('RIASEC finishTest: Not all questions answered. Showing modal: ' . $this->submissionErrorMessage);
             $this->showUnansweredQuestionsModal = true; 
             $this->isSubmitting = false;
             return; 
        }

        Log::info('RIASEC finishTest: Validation passed or autoSubmit. Proceeding to completeTestTransaction.');
        
        try {
            $this->completeTestTransaction($user, $autoSubmit, $summaryMessage);
            session()->flash('success', 'Tes ' . ($this->testModel->test_name ?? 'RIASEC') . ' telah selesai!');
            $this->redirectRoute('candidate.assessment.test');

        } catch (\Exception $e) {
            Log::error('RIASEC finishTest: Exception during completion transaction.', ['error' => $e->getMessage(), 'trace_snippet' => substr($e->getTraceAsString(),0,500)]);
            $this->setErrorState('Terjadi kesalahan teknis saat menyimpan hasil tes. Silakan coba lagi atau hubungi administrator.');
            $this->isSubmitting = false;
        }
    }

    protected function completeTestTransaction($user, bool $autoSubmit, string $summaryMessage): void
    {
        if (!$this->progress) {
            throw new \Exception("UserTestProgress is not loaded in completeTestTransaction for user {$user->id}, test {$this->testModel->test_id}");
        }
        if (!$this->testModel) {
            throw new \Exception("TestModel is not loaded in completeTestTransaction for user {$user->id}");
        }
        if (!$this->progress->started_at) {
            Log::critical('RIASEC executeTestCompletion: started_at is NULL! Setting default.', ['progress_id' => $this->progress->id]); 
            $this->progress->started_at = now()->subMinutes($this->timeLimitMinutes ?? 60); 
        }

        DB::transaction(function () use ($user, $autoSubmit, $summaryMessage) {
            $scoringService = app(TestScoringService::class);
            $scoreResult = $scoringService->calculateScore($this->testModel, $user);
            
            $startTime = ($this->progress->started_at instanceof Carbon) 
                ? $this->progress->started_at 
                : Carbon::parse($this->progress->started_at);
            
            $timeSpentSeconds = now()->diffInSeconds($startTime);
            if ($this->timeLimitMinutes) {
                $timeSpentSeconds = min($timeSpentSeconds, $this->timeLimitMinutes * 60);
            }

            // UPDATED: Tidak lagi menyimpan result_summary untuk RIASEC
            $this->progress->update([
                'status' => 'completed', 
                'score' => null, // RIASEC tidak memiliki skor numerik tunggal
                'result_summary' => null, // UPDATED: Set null, data disimpan di tabel terpisah
                'completed_at' => now(), 
                'time_spent_seconds' => $timeSpentSeconds
            ]);
            
            // NEW: Simpan skor detail RIASEC ke tabel terpisah
            $scoringService->saveRiasecDetailedScores($user, $scoreResult);
            
            $session = TestSession::where('user_id', $user->id)
                    ->where('test_id', $this->testModel->test_id)
                    ->whereNull('completed_at')->latest('started_at')->first();
            if ($session) {
                $session->update(['completed_at' => now(), 'time_spent_seconds' => $timeSpentSeconds]);
            }

            $this->testCompleted = true;
            $this->timeRemaining = 0; 
            Log::info('RIASEC: Test completed successfully in DB.', ['user_id' => $user->id, 'progress_id' => $this->progress->id]);
            $this->dispatch('testFinishedSuccessfully');
        });
    }

    public function render()
    {
        $testNameForView = $this->testModel ? $this->testModel->test_name : $this->pageTitle;

        if ($this->showErrorView) {
            $viewParameters = ['errorMessage' => $this->errorMessage, 'testName' => $testNameForView];
            if (str_contains($this->errorMessage, 'terlebih dahulu')) { 
                 return view('livewire.candidate.assessment.assessment-prerequisite-failed', $viewParameters)->layout('layouts.test-taker');
            }
            return view('livewire.candidate.assessment.assessment-error', $viewParameters)->layout('layouts.test-taker');
        }

        if ($this->testCompleted) {
            return view('livewire.candidate.assessment.assessment-completed', ['testName' => $testNameForView])->layout('layouts.test-taker');
        }
        
        if (empty($this->questions)) { 
             if (!$this->showErrorView && !$this->testCompleted) {
                Log::warning('RIASEC render: Questions collection is empty or null when trying to render test view.');
                 $this->setErrorState('Tidak dapat memuat data pertanyaan tes (render).');
             }
             return view('livewire.candidate.assessment.assessment-error', [
                'errorMessage' => $this->errorMessage ?: 'Tidak dapat memuat pertanyaan tes. Silakan coba muat ulang halaman atau hubungi dukungan.',
                'testName' => $testNameForView
            ])->layout('layouts.test-taker');
        }
        
        if (!$this->isSubmitting && $this->timeLimitMinutes && $this->progress && $this->progress->started_at) {
            $this->calculateRemainingTime();
        }

        return view('livewire.candidate.assessment.assessment-riasec', [
            'currentTest' => $this->testModel,
            'questions' => $this->questions,
            'totalQuestions' => $this->questions->count(),
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