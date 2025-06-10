<?php

namespace App\Http\Livewire\HR;

use App\Models\Test;
use App\Models\User;
use App\Models\UserTestProgress;
use Livewire\Component;

class DashboardHr extends Component
{
    public array $statistics = [];
    public $recentActivities = [];
    
    public array $chartLabels = [];
    public array $chartData = [];

    public function mount()
    {
        $this->loadStatistics();
        $this->loadRecentActivities();
        $this->loadChartData(); 
    }

    public function loadStatistics(): void
    {
        $candidates = User::where('role', User::ROLE_CANDIDATE);
        $totalTests = Test::count() ?: 3;

        $this->statistics = [
            'total_candidates' => $candidates->count(),
            'completed_all_tests' => $candidates->clone()->whereHas('testProgress', function ($q) {
                $q->where('status', 'completed');
            }, '>=', $totalTests)->count(),
            'tests_in_progress' => UserTestProgress::where('status', 'in_progress')->distinct('user_id')->count(),
            'average_programming_score' => round(UserTestProgress::where('test_id', 1) 
                ->where('status', 'completed')
                ->avg('score') ?? 0, 2),
        ];
    }

    public function loadRecentActivities($limit = 5): void
    {
        $this->recentActivities = UserTestProgress::with(['user', 'test'])
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }
    
    public function loadChartData(): void
    {
        $stats = Test::withCount([
            'userProgress as completed_count' => fn($q) => $q->where('status', 'completed'),
            'userProgress as total_attempts'
        ])->orderBy('test_order')->get();
        
        $this->chartLabels = [];
        $this->chartData = [];

        foreach ($stats as $test) {
            $this->chartLabels[] = $test->test_name;
            $this->chartData[] = $test->total_attempts > 0 ? round(($test->completed_count / $test->total_attempts) * 100) : 0;
        }
    }

    public function render()
    {
        return view('livewire.hr.dashboard-hr', [
            'pageTitle' => 'Dashboard HR'
        ]);
    }
}