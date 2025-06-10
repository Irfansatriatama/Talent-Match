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

class AssessmentMbti extends Component
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
    public string $pageTitle = 'Tes Kepribadian MBTI';
    public bool $isSubmitting = false;
    public bool $showUnansweredQuestionsModal = false;
    public ?string $submissionErrorMessage = null;

    const MBTI_TEST_ID = 3; 
    const PREVIOUS_TEST_ID = 2; 
    const PREVIOUS_TEST_NAME = 'Tes Minat RIASEC'; 

    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            $this->setErrorState('Sesi pengguna tidak ditemukan. Silakan login kembali.');
            return;
        }

        try {
            $this->testModel = Test::findOrFail(self::MBTI_TEST_ID); 
            $this->pageTitle = $this->testModel->test_name ?? $this->pageTitle;
            $this->timeLimitMinutes = $this->testModel->time_limit_minutes; 
        } catch (ModelNotFoundException $e) {
            Log::error('AssessmentMbti Mount: Test model not found', ['test_id' => self::MBTI_TEST_ID, 'exception' => $e->getMessage()]);
            $this->setErrorState('Konfigurasi tes tidak ditemukan.');
            return;
        }

        $this->progress = UserTestProgress::firstOrCreate(
            ['user_id' => $user->id, 'test_id' => $this->testModel->test_id],
            ['status' => 'not_started', 'started_at' => null] 
        );
        
        if (!$this->progress->wasRecentlyCreated && $this->progress->started_at) {
            $this->progress->refresh(); 
        }
        Log::info('MBTI mount: Progress loaded.', ['id' => $this->progress->id, 'status' => $this->progress->status, 'started_at' => $this->progress->started_at ? $this->progress->started_at->toDateTimeString() : null]);


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
                $this->handleTimeExpired(); 
            }
        } else if (!$this->progress->started_at && $this->progress->status === 'in_progress') {
             Log::error('AssessmentMbti mount: In_progress with null started_at after init.', ['progress_id' => $this->progress->id]);
             $this->setErrorState('Gagal memuat sesi tes. Waktu mulai tidak tervalidasi.');
        } else {
             $this->timeRemaining = null; 
        }
        Log::info('MBTI: Mount completed.', ['time_remaining_on_mount' => $this->timeRemaining]);
    }


    protected function setErrorState(string $message): void { $this->showErrorView = true; $this->errorMessage = $message; Log::warning('MBTI: Error state set', ['message' => $message]); }
    protected function checkPrerequisite(int $userId): bool {
        $previousProgress = UserTestProgress::where('user_id', $userId)->where('test_id', self::PREVIOUS_TEST_ID)->where('status', 'completed')->first();
        return $previousProgress !== null;
    }
    protected function initializeTestSession($user): void {
        if ($this->progress->status === 'not_started') {
            Log::info('MBTI: Initializing new test session', ['user_id' => $user->id]);
            $this->startNewTestSession($user); $this->progress->refresh();
        } elseif ($this->progress->status === 'in_progress' && !$this->progress->started_at) {
            Log::warning('MBTI: In-progress test missing started_at, setting now', ['progress_id' => $this->progress->id]);
            $this->progress->update(['started_at' => now()]); $this->progress->refresh();
        }
    }
    protected function startNewTestSession($user): void {
        try {
            DB::transaction(function () use ($user) {
                $questionIds = $this->testModel->questions()->pluck('question_id');
                UserAnswer::where('user_id', $user->id)->whereIn('question_id', $questionIds)->delete();
                $this->userAnswers = [];
                $this->progress->status = 'in_progress'; $this->progress->started_at = now();
                $this->progress->fill(['score' => null, 'result_summary' => null, 'completed_at' => null, 'time_spent_seconds' => null])->save();
                TestSession::create(['user_id' => $user->id, 'test_id' => $this->testModel->test_id, 'started_at' => $this->progress->started_at, 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent()]);
            });
            Log::info('MBTI: New test session started.', ['user_id' => $user->id, 'started_at' => $this->progress->started_at->toDateTimeString()]);
        } catch (\Exception $e) {
            Log::error('MBTI: Failed to start new test session', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            $this->setErrorState('Gagal memulai sesi tes baru.');
        }
    }
    protected function loadQuestions(): bool {
        if (!$this->testModel) { $this->setErrorState('Model tes tidak terinisialisasi.'); return false; }
        $this->questions = $this->testModel->questions()->with('options')->orderBy('question_order')->get();
        if ($this->questions->isEmpty()) {
            Log::error('MBTI: No questions found', ['test_id' => $this->testModel->test_id]);
            $this->setErrorState('Tidak ada pertanyaan ditemukan untuk tes ini.');
            if ($this->progress && $this->progress->status === 'in_progress') {
                $this->progress->update(['status' => 'completed', 'result_summary' => 'Error: Tidak ada pertanyaan']);
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
        Log::info('MBTI: Loaded existing answers', ['user_id' => $userId, 'answers_count' => count($this->userAnswers)]);
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
            Log::info('MBTI: Time expired via handleTimerTick.');
            $this->handleTimeExpired();
        }
    }
    protected function handleTimeExpired(): void { 
        if ($this->testCompleted || $this->showErrorView || $this->isSubmitting) { return; }
        Log::info('MBTI: Handling time expiration.');
        if (!$this->isSubmitting) {
            $this->finishTest(true, 'Waktu habis');
        }
    }
    public function selectAnswer($questionId, $optionId): void {
        if ($this->testCompleted || $this->showErrorView || $this->isSubmitting) return;
        if ($this->timeLimitMinutes) {
            $this->calculateRemainingTime();
            if ($this->timeRemaining <= 0) {
                $this->handleTimeExpired();
                return;
            }
        }
        try {
            $this->userAnswers[$questionId] = $optionId;
            UserAnswer::updateOrCreate(
                ['user_id' => Auth::id(), 'question_id' => $questionId],
                ['selected_option_id' => $optionId, 'riasec_score_selected' => null, 'answered_at' => now()]
            );
            $this->dispatch('answer-saved', questionId: $questionId, message: 'Pilihan disimpan.');
            Log::info('MBTI: Answer selected', ['q_id' => $questionId, 'opt_id' => $optionId]);
        } catch (\Exception $e) {
            Log::error('MBTI: Failed to save answer', ['q_id' => $questionId, 'error' => $e->getMessage()]);
            $this->dispatch('answer-save-error', message: 'Gagal menyimpan jawaban.');
        }
    }

    public function finishTest(bool $autoSubmit = false, string $summaryMessage = 'Selesai'): void
    {
        if ($this->testCompleted || $this->showErrorView || $this->isSubmitting) {
            Log::info('MBTI: Finish test called but conditions not met or submitting.', [/*...*/]);
            return;
        }
        $this->isSubmitting = true;
        $this->submissionErrorMessage = null; 
        Log::info('MBTI finishTest: Attempting.', ['autoSubmit' => $autoSubmit, 'user_id' => Auth::id()]);
        
        $user = Auth::user();
        if (!$user || !$this->progress || !$this->progress->started_at) {
            Log::error('MBTI finishTest: Invalid state.'); $this->setErrorState('Sesi tes tidak valid.');
            $this->isSubmitting = false; return;
        }
        if (empty($this->questions)) {
            Log::error('MBTI finishTest: Questions not loaded.'); $this->setErrorState('Data pertanyaan tidak ada.');
            $this->isSubmitting = false; return;
        }

        $totalQuestions = $this->questions->count();
        $answeredQuestions = count($this->userAnswers);
        Log::info('MBTI finishTest: Validation details.', ['total' => $totalQuestions, 'answered' => $answeredQuestions, 'auto' => $autoSubmit]);

        if (!$autoSubmit && $totalQuestions > 0 && $answeredQuestions < $totalQuestions) {
             $this->submissionErrorMessage = "Harap pilih pernyataan yang paling menggambarkan diri Anda untuk semua item. ({$answeredQuestions}/{$totalQuestions} dijawab)";
             Log::warning('MBTI finishTest: Not all questions answered: ' . $this->submissionErrorMessage);
             $this->showUnansweredQuestionsModal = true; 
             $this->isSubmitting = false; 
             return; 
        }

        Log::info('MBTI finishTest: Validation passed or autoSubmit. Proceeding.');
        try {
            $this->completeTestTransaction($user, $autoSubmit, $summaryMessage);
            session()->flash('success', 'Tes ' . ($this->testModel->test_name ?? 'MBTI') . ' telah selesai!');
            $this->redirectRoute('candidate.assessment.test');
        } catch (\Exception $e) {
            Log::error('MBTI finishTest: Exception.', ['error' => $e->getMessage()]);
            $this->setErrorState('Terjadi kesalahan teknis saat menyimpan hasil tes.');
            $this->isSubmitting = false;
        }
    }

    protected function completeTestTransaction($user, bool $autoSubmit, string $summaryMessage): void
    {
        DB::transaction(function () use ($user, $autoSubmit, $summaryMessage) {
            $scoringService = app(TestScoringService::class);
            $scoreResult = $scoringService->calculateScore($this->testModel, $user);
            
            
            $startTime = ($this->progress->started_at instanceof Carbon) ? $this->progress->started_at : Carbon::parse($this->progress->started_at);
            $timeSpentSeconds = now()->diffInSeconds($startTime);
            if ($this->timeLimitMinutes) {
                 $timeSpentSeconds = min($timeSpentSeconds, $this->timeLimitMinutes * 60);
            }

            $this->progress->update([
                'status' => 'completed', 
                'score' => null, 
                'result_summary' => $scoreResult['summary'] ?? ($autoSubmit ? $summaryMessage : 'Selesai'),
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
            Log::info('MBTI: Test completed successfully in DB.', ['user_id' => $user->id]);
            $this->dispatch('testFinishedSuccessfully'); 
        });
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
             Log::warning('MBTI render: Questions collection empty.');
             return view('livewire.candidate.assessment.assessment-error', [
                'errorMessage' => $this->errorMessage ?: 'Tidak dapat memuat pertanyaan tes.',
                'testName' => $testNameForView
            ])->layout('layouts.test-taker');
        }
        
        if (!$this->isSubmitting && $this->timeLimitMinutes && $this->progress && $this->progress->started_at) {
            $this->calculateRemainingTime();
        }

        return view('livewire.candidate.assessment.assessment-mbti', [
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