<?php

namespace App\Http\Livewire\HR;

use App\Models\User;
use App\Models\Test;
use Livewire\Component;
use Livewire\WithPagination;

class Candidate extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $mbtiType = '';
    public string $sortBy = 'created_at';
    public string $sortOrder = 'desc';

    protected $queryString = ['search', 'status', 'mbtiType'];
    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingStatus()
    {
        $this->resetPage();
    }
    public function updatingMbtiType()
    {
        $this->resetPage();
    }

    public function render()
    {
        $totalTests = Test::count();
        if ($totalTests == 0) $totalTests = 3;

        $query = User::where('role', User::ROLE_CANDIDATE)
                    ->with(['testProgress.test', 'latestMbtiScore']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->status) {
            match ($this->status) {
                'completed' => $query->whereHas('testProgress', fn($q) => $q->where('status', 'completed'), '>=', $totalTests),
                'in_progress' => $query->whereHas('testProgress', fn($q) => $q->where('status', 'in_progress'))->whereDoesntHave('testProgress', fn($q) => $q->where('status', 'completed'), '>=', $totalTests),
                'not_started' => $query->whereDoesntHave('testProgress'),
                default => null,
            };
        }
        
        if ($this->mbtiType) {
            $query->whereHas('latestMbtiScore', function($q) {
                $q->where('mbti_type', $this->mbtiType);
            });
        }
        
        $candidates = $query->orderBy($this->sortBy, $this->sortOrder)->paginate(10);

        return view('livewire.hr.candidate', [
            'candidates' => $candidates,
            'pageTitle' => 'Manajemen Kandidat'
        ]);
    }
}