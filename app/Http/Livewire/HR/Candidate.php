<?php

namespace App\Http\Livewire\HR;

use App\Models\User;
use App\Models\Test;
use App\Models\JobPosition;
use Livewire\Component;
use Livewire\WithPagination;

class Candidate extends Component
{
    use WithPagination;
    public string $search = '';
    public string $status = '';
    public string $mbtiType = '';
    public string $jobPositionId = '';
    public string $riasecType = '';
    public string $sortBy = 'created_at';
    public string $sortOrder = 'desc';
    
    public $jobPositions;

    protected $queryString = ['search', 'status', 'mbtiType', 'jobPositionId', 'riasecType'];
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->jobPositions = JobPosition::orderBy('name')->get();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatus() { $this->resetPage(); }
    public function updatingMbtiType() { $this->resetPage(); }
    public function updatingJobPositionId() { $this->resetPage(); }
    public function updatingRiasecType() { $this->resetPage(); }

    public function render()
    {
        $totalTests = Test::count() ?: 3;

        $query = User::where('role', User::ROLE_CANDIDATE)
                    ->with([
                        'testProgress.test', 
                        'latestMbtiScore',    
                        'latestRiasecScore',   
                        'jobPosition'
                    ]);

        $query->when($this->search, function($q) {
            $q->where(function($subQuery) {
                $subQuery->where('name', 'like', '%'.$this->search.'%')
                         ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        });

        $query->when($this->status, function($q) use ($totalTests) {
            switch($this->status) {
                case 'completed':
                    $q->whereHas('testProgress', function($subQ) {
                        $subQ->where('test_id', 1)->where('status', 'completed');
                    })
                    ->whereHas('latestRiasecScore')
                    ->whereHas('latestMbtiScore');
                    break;
                    
                case 'in_progress':
                    $q->where(function($subQ) {
                        $subQ->whereHas('testProgress', fn($tq) => $tq->where('status', 'in_progress'))
                             ->orWhere(function($sq) {
                                 $sq->where(function($innerQ) {
                                     $innerQ->whereHas('testProgress', fn($tq) => $tq->where('status', 'completed'))
                                            ->orWhereHas('latestRiasecScore')
                                            ->orWhereHas('latestMbtiScore');
                                 })
                                 ->where(function($innerQ) {
                                     $innerQ->whereDoesntHave('testProgress', fn($tq) => $tq->where('test_id', 1)->where('status', 'completed'))
                                            ->orWhereDoesntHave('latestRiasecScore')
                                            ->orWhereDoesntHave('latestMbtiScore');
                                 });
                             });
                    });
                    break;
                    
                case 'not_started':
                    $q->whereDoesntHave('testProgress')
                      ->whereDoesntHave('latestRiasecScore')
                      ->whereDoesntHave('latestMbtiScore');
                    break;
            }
        });
        
        $query->when($this->mbtiType, function($q) {
            $q->whereHas('latestMbtiScore', function($subQuery) {
                $subQuery->where('mbti_type', $this->mbtiType);
            });
        });
        
        $query->when($this->jobPositionId, function($q) {
            $q->where('job_position_id', $this->jobPositionId);
        });
        
        $query->when($this->riasecType, function($q) {
            $q->whereHas('latestRiasecScore', function($subQuery) {
                $subQuery->where('riasec_code', 'like', $this->riasecType . '%');
            });
        });
        
        $candidates = $query->orderBy($this->sortBy, $this->sortOrder)->paginate(10);

        return view('livewire.hr.candidate', [
            'candidates' => $candidates,
            'pageTitle' => 'Manajemen Kandidat'
        ]);
    }
}