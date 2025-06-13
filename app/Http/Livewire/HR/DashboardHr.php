<?php

namespace App\Http\Livewire\HR;

use App\Models\Test;
use App\Models\User;
use App\Models\UserTestProgress;
use App\Models\AnpAnalysis;
use App\Models\JobPosition;
use App\Models\UserMbtiScore;
use App\Models\UserRiasecScore; // Pastikan model ini di-import
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardHr extends Component
{
    public array $statistics = [];
    public $topCandidates = [];
    public $recentAnalyses = [];
    
    // Data untuk Chart.js
    public array $mbtiLabels = [];
    public array $mbtiData = [];
    public array $mbtiColors = [];
    
    public array $riasecLabels = ['Realistic', 'Investigative', 'Artistic', 'Social', 'Enterprising', 'Conventional'];
    public array $riasecData = [];

    public function mount()
    {
        $this->loadStatistics();
        $this->loadTopCandidates();
        $this->loadRecentAnalyses();
        $this->loadMbtiDistribution();
        $this->loadRiasecDistribution();
    }

    public function loadStatistics(): void
    {
        $candidates = User::where('role', User::ROLE_CANDIDATE);
        $totalTests = Test::count() ?: 3;
        
        $weekStart = Carbon::now()->startOfWeek();
        $newCandidatesThisWeek = User::where('role', User::ROLE_CANDIDATE)
            ->where('created_at', '>=', $weekStart)
            ->count();
        
        $monthStart = Carbon::now()->startOfMonth();
        $anpThisMonth = AnpAnalysis::where('created_at', '>=', $monthStart)->count();

        $totalCandidates = $candidates->count();
        
        $completedAll = User::where('role', User::ROLE_CANDIDATE)
            ->whereHas('testProgress', function($q) {
                $q->where('test_id', 1)->where('status', 'completed');
            })
            ->whereHas('latestRiasecScore')
            ->whereHas('latestMbtiScore')
            ->count();

        $this->statistics = [
            'total_candidates' => $totalCandidates,
            'completed_all_tests' => $completedAll,
            'tests_in_progress' => UserTestProgress::where('status', 'in_progress')->distinct('user_id')->count(),
            'average_programming_score' => round(UserTestProgress::where('test_id', 1)
                ->where('status', 'completed')
                ->avg('score') ?? 0, 2),
            'new_candidates_this_week' => $newCandidatesThisWeek,
            'anp_analyses_count' => AnpAnalysis::count(),
            'anp_this_month' => $anpThisMonth,
            'completion_rate' => $totalCandidates > 0 ? round(($completedAll / $totalCandidates) * 100, 1) : 0
        ];
    }

    /**
     * PERBAIKAN: Tambahkan ->values() untuk mereset key collection
     */
    public function loadTopCandidates(): void
    {
        $this->topCandidates = User::where('role', User::ROLE_CANDIDATE)
            ->whereHas('testProgress', function($q) {
                $q->where('test_id', 1)->where('status', 'completed');
            })
            ->whereHas('latestRiasecScore')
            ->whereHas('latestMbtiScore')
            ->with([
                'testProgress' => function($q) {
                    $q->where('test_id', 1)->where('status', 'completed');
                }, 
                'jobPosition'
            ])
            ->get()
            ->map(function($user) {
                $progScore = $user->testProgress->first()->score ?? 0;
                $user->average_score = round($progScore, 2);
                return $user;
            })
            ->sortByDesc('average_score')
            ->take(5)
            ->values(); // <-- TAMBAHKAN BARIS INI
    }
    
    public function loadRecentAnalyses(): void
    {
        $this->recentAnalyses = AnpAnalysis::with(['jobPosition', 'candidates'])
            ->withCount('candidates')
            ->latest()
            ->limit(5)
            ->get();
    }
    
    public function loadMbtiDistribution(): void
    {
        $mbtiDistribution = UserMbtiScore::selectRaw('mbti_type, COUNT(*) as count')
            ->whereHas('user', function($q) {
                $q->where('role', User::ROLE_CANDIDATE);
            })
            ->groupBy('mbti_type')
            ->orderByDesc('count')
            ->get();
        
        $this->mbtiLabels = $mbtiDistribution->pluck('mbti_type')->toArray();
        $this->mbtiData = $mbtiDistribution->pluck('count')->toArray();
        
        $this->mbtiColors = [
            '#e91e63', '#9c27b0', '#673ab7', '#3f51b5',
            '#2196f3', '#03a9f4', '#00bcd4', '#009688',
            '#4caf50', '#8bc34a', '#cddc39', '#ffeb3b',
            '#ffc107', '#ff9800', '#ff5722', '#795548'
        ];
    }
    
    public function loadRiasecDistribution(): void
    {
        $riasecCounts = ['R' => 0, 'I' => 0, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0];
        
        $riasecScores = UserRiasecScore::whereHas('user', function($q) {
                $q->where('role', User::ROLE_CANDIDATE);
            })
            ->whereNotNull('riasec_code')
            ->pluck('riasec_code');
        
        foreach ($riasecScores as $code) {
            if (strlen($code) > 0) {
                $dominantType = substr($code, 0, 1);
                if (array_key_exists($dominantType, $riasecCounts)) {
                    $riasecCounts[$dominantType]++;
                }
            }
        }
        
        $this->riasecData = array_values($riasecCounts);
    }

    public function render()
    {
        return view('livewire.hr.dashboard-hr', [
            'pageTitle' => 'Dashboard HR'
        ]);
    }
}