<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Question;
use App\Models\UserAnswer;
use App\Models\UserTestProgress;
use App\Models\TestSession;
use App\Services\TestScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    protected $scoringService;
    
    public function __construct(TestScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }
    
    public function show($testId)
    {
        $test = Test::with(['questions.options'])->findOrFail($testId);
        $user = Auth::user();
        
        if (!$this->canAccessTest($testId, $user)) {
            return redirect()->route('assessment.index')
                           ->with('error', 'Please complete the previous test first.');
        }
        
        $progress = UserTestProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'test_id' => $testId
            ],
            [
                'status' => 'not_started',
                'started_at' => now()
            ]
        );
        
        if ($progress->status === 'not_started') {
            $progress->update([
                'status' => 'in_progress',
                'started_at' => now()
            ]);
            
            TestSession::create([
                'user_id' => $user->id,
                'test_id' => $testId,
                'started_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }
        
        $existingAnswers = UserAnswer::where('user_id', $user->id)
                                   ->whereIn('question_id', $test->questions->pluck('question_id'))
                                   ->get()
                                   ->keyBy('question_id');
        
        return view('test.show', compact('test', 'progress', 'existingAnswers'));
    }
    
    public function saveAnswer(Request $request, $testId)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,question_id',
            'answer' => 'required'
        ]);
        
        $user = Auth::user();
        $question = Question::findOrFail($validated['question_id']);
        
        $answerData = [
            'user_id' => $user->id,
            'question_id' => $question->question_id,
            'answered_at' => now()
        ];
        
        if ($question->test->test_type === 'riasec') {
            $answerData['riasec_score_selected'] = $validated['answer'];
        } else {
            $answerData['selected_option_id'] = $validated['answer'];
        }
        
        UserAnswer::updateOrCreate(
            [
                'user_id' => $user->id,
                'question_id' => $question->question_id
            ],
            $answerData
        );
        
        return response()->json(['success' => true]);
    }
    
    public function submit($testId)
    {
        $test = Test::findOrFail($testId);
        $user = Auth::user();
        
        DB::transaction(function () use ($test, $user) {
            $score = $this->scoringService->calculateScore($test, $user);
            
            $progress = UserTestProgress::where('user_id', $user->id)
                                      ->where('test_id', $test->test_id)
                                      ->first();
            
            $progress->update([
                'status' => 'completed',
                'score' => $score['score'] ?? null,
                'result_summary' => $score['summary'] ?? null,
                'completed_at' => now(),
                'time_spent_seconds' => now()->diffInSeconds($progress->started_at)
            ]);
            
            TestSession::where('user_id', $user->id)
                      ->where('test_id', $test->test_id)
                      ->whereNull('completed_at')
                      ->latest()
                      ->first()
                      ->update([
                          'completed_at' => now(),
                          'time_spent_seconds' => now()->diffInSeconds($progress->started_at)
                      ]);
            
            if ($test->test_type === 'mbti') {
                $this->scoringService->saveMbtiDetailedScores($user, $score);
            }
        });
        
        return redirect()->route('assessment.index')
                       ->with('success', 'Test completed successfully!');
    }
    
    private function canAccessTest($testId, $user)
    {
        if ($testId == 1) return true;
        
        $previousTestId = $testId - 1;
        return UserTestProgress::where('user_id', $user->id)
                             ->where('test_id', $previousTestId)
                             ->where('status', 'completed')
                             ->exists();
    }
}