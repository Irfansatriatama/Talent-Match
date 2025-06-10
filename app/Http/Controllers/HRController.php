<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserTestProgress;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HRController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'hr') {
                abort(403, 'Unauthorized access');
            }
            return $next($request);
        });
    }
    
    public function dashboard()
    {
        $statistics = $this->getStatistics();
        $recentActivities = $this->getRecentActivities();
        $testCompletionStats = $this->getTestCompletionStats();
        
        return view('hr.dashboard', compact('statistics', 'recentActivities', 'testCompletionStats'));
    }
    
    public function candidateList(Request $request)
    {
        $query = User::where('role', 'candidate')
                    ->with(['testProgress.test', 'mbtiScores']);
        
        if ($request->has('status')) {
            switch ($request->status) {
                case 'completed':
                    $query->whereHas('testProgress', function($q) {
                        $q->where('status', 'completed')
                          ->groupBy('user_id')
                          ->havingRaw('COUNT(DISTINCT test_id) = 3');
                    });
                    break;
                case 'in_progress':
                    $query->whereHas('testProgress', function($q) {
                        $q->where('status', 'in_progress');
                    });
                    break;
                case 'not_started':
                    $query->whereDoesntHave('testProgress');
                    break;
            }
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $candidates = $query->paginate(20);
        
        $candidates->getCollection()->transform(function ($candidate) {
            $completedTests = $candidate->testProgress->where('status', 'completed')->count();
            $candidate->completion_percentage = ($completedTests / 3) * 100;
            return $candidate;
        });
        
        return view('hr.candidates', compact('candidates'));
    }
    
    public function candidateDetail(Request $request, $userId)
    {
        $candidate = User::where('role', 'candidate')
                       ->with([
                           'testProgress.test',
                           'mbtiScores',
                           'testSessions',
                           'userAnswers.question'
                       ])
                       ->findOrFail($userId);
        
        $testResults = [];
        foreach ($candidate->testProgress as $progress) {
            if ($progress->status === 'completed') {
                $testResults[$progress->test->test_name] = [
                    'score' => $progress->score,
                    'summary' => $progress->result_summary,
                    'completed_at' => $progress->completed_at,
                    'time_spent' => $progress->time_spent_seconds
                ];
            }
        }
        
        $answerDetails = [];
        if ($request->show_answers ?? false) {
            $answerDetails = $this->getAnswerDetails($candidate);
        }
        
        return view('hr.candidate-detail', compact('candidate', 'testResults', 'answerDetails'));
    }
    
    public function exportCandidates(Request $request)
    {
        $candidates = User::where('role', 'candidate')
                        ->with(['testProgress', 'mbtiScores'])
                        ->get();
        
        $exportData = $candidates->map(function($candidate) {
            $progress = $candidate->testProgress->keyBy('test_id');
            
            return [
                'Name' => $candidate->name,
                'Email' => $candidate->email,
                'Registration Date' => $candidate->created_at->format('Y-m-d'),
                'Programming Score' => $progress->get(1)?->score ?? 'N/A',
                'RIASEC Result' => $progress->get(2)?->result_summary ?? 'N/A',
                'MBTI Type' => $progress->get(3)?->result_summary ?? 'N/A',
                'Completion Status' => $candidate->hasCompletedAllTests() ? 'Completed' : 'In Progress',
                'Last Activity' => $candidate->testProgress->max('updated_at')?->format('Y-m-d H:i') ?? 'N/A'
            ];
        });
        
        return response()->json($exportData);
    }
    
    public function testStatistics()
    {
        $tests = Test::with(['questions', 'userProgress'])->get();
        
        $statistics = $tests->map(function($test) {
            $progress = $test->userProgress;
            $completed = $progress->where('status', 'completed');
            
            return [
                'test_name' => $test->test_name,
                'total_attempts' => $progress->count(),
                'completed' => $completed->count(),
                'in_progress' => $progress->where('status', 'in_progress')->count(),
                'average_score' => $completed->avg('score'),
                'min_score' => $completed->min('score'),
                'max_score' => $completed->max('score'),
                'average_time' => $completed->avg('time_spent_seconds'),
                'completion_rate' => $progress->count() > 0 
                    ? ($completed->count() / $progress->count()) * 100 
                    : 0
            ];
        });
        
        return view('hr.test-statistics', compact('statistics'));
    }
    
    private function getStatistics()
    {
        $candidates = User::where('role', 'candidate');
        
        return [
            'total_candidates' => $candidates->count(),
            'completed_all_tests' => $candidates->clone()
                ->whereHas('testProgress', function($q) {
                    $q->where('status', 'completed')
                      ->groupBy('user_id')
                      ->havingRaw('COUNT(DISTINCT test_id) = 3');
                })->count(),
            'tests_in_progress' => UserTestProgress::where('status', 'in_progress')->count(),
            'tests_today' => UserTestProgress::whereDate('created_at', today())->count(),
            'average_programming_score' => UserTestProgress::where('test_id', 1)
                ->where('status', 'completed')
                ->avg('score'),
            'most_common_mbti' => DB::table('user_mbti_scores')
                ->select('mbti_type', DB::raw('COUNT(*) as count'))
                ->groupBy('mbti_type')
                ->orderByDesc('count')
                ->first()?->mbti_type ?? 'N/A',
            'most_common_riasec' => UserTestProgress::where('test_id', 2)
                ->where('status', 'completed')
                ->select('result_summary', DB::raw('COUNT(*) as count'))
                ->groupBy('result_summary')
                ->orderByDesc('count')
                ->first()?->result_summary ?? 'N/A'
        ];
    }
    
    private function getRecentActivities($limit = 10)
    {
        return UserTestProgress::with(['user', 'test'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($progress) {
                return [
                    'user_name' => $progress->user->name,
                    'test_name' => $progress->test->test_name,
                    'status' => $progress->status,
                    'score' => $progress->score,
                    'time' => $progress->updated_at->diffForHumans()
                ];
            });
    }
    
    private function getTestCompletionStats()
    {
        $tests = Test::all();
        $stats = [];
        
        foreach ($tests as $test) {
            $total = UserTestProgress::where('test_id', $test->test_id)->count();
            $completed = UserTestProgress::where('test_id', $test->test_id)
                                       ->where('status', 'completed')
                                       ->count();
            
            $stats[] = [
                'test_name' => $test->test_name,
                'total_attempts' => $total,
                'completed' => $completed,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0
            ];
        }
        
        return $stats;
    }
    
    private function getAnswerDetails($candidate)
    {
        $details = [];
        
        foreach ($candidate->testProgress as $progress) {
            if ($progress->status !== 'completed') continue;
            
            $test = $progress->test;
            $answers = $candidate->userAnswers()
                               ->whereHas('question', function($q) use ($test) {
                                   $q->where('test_id', $test->test_id);
                               })
                               ->with(['question', 'selectedOption'])
                               ->get();
            
            $details[$test->test_name] = $answers->map(function($answer) {
                return [
                    'question' => $answer->question->question_text,
                    'answer' => $answer->selectedOption?->option_text ?? 
                               'Score: ' . $answer->riasec_score_selected,
                    'is_correct' => $answer->selectedOption?->is_correct_programming ?? null
                ];
            });
        }
        
        return $details;
    }
}