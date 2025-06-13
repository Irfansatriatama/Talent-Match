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

    // Properti untuk filter
    public string $search = '';
    public string $status = '';
    public string $mbtiType = '';
    public string $jobPositionId = '';
    public string $riasecType = '';
    public string $sortBy = 'created_at';
    public string $sortOrder = 'desc';
    
    // Data untuk dropdown
    public $jobPositions;

    // Tambahkan ke queryString agar filter tersimpan di URL
    protected $queryString = ['search', 'status', 'mbtiType', 'jobPositionId', 'riasecType'];
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        // Load data posisi pekerjaan untuk dropdown filter
        $this->jobPositions = JobPosition::orderBy('name')->get();
    }

    // Reset halaman saat filter berubah
    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatus() { $this->resetPage(); }
    public function updatingMbtiType() { $this->resetPage(); }
    public function updatingJobPositionId() { $this->resetPage(); }
    public function updatingRiasecType() { $this->resetPage(); }

    public function render()
    {
        $totalTests = Test::count() ?: 3;

        // Query builder dengan eager loading yang diperbarui
        $query = User::where('role', User::ROLE_CANDIDATE)
                    ->with([
                        'testProgress.test', 
                        'latestMbtiScore',      // Existing relation
                        'latestRiasecScore',    // NEW: Load RIASEC scores
                        'jobPosition'
                    ]);

        // FILTER: Pencarian nama/email
        $query->when($this->search, function($q) {
            $q->where(function($subQuery) {
                $subQuery->where('name', 'like', '%'.$this->search.'%')
                         ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        });

        // UPDATED FILTER: Status penyelesaian tes
        $query->when($this->status, function($q) use ($totalTests) {
            switch($this->status) {
                case 'completed':
                    // Kandidat yang sudah selesai semua tes
                    // Check programming test completion
                    $q->whereHas('testProgress', function($subQ) {
                        $subQ->where('test_id', 1)->where('status', 'completed');
                    })
                    // Check RIASEC completion
                    ->whereHas('latestRiasecScore')
                    // Check MBTI completion
                    ->whereHas('latestMbtiScore');
                    break;
                    
                case 'in_progress':
                    // Kandidat yang sedang mengerjakan (ada tes in_progress atau sudah selesai sebagian)
                    $q->where(function($subQ) {
                        $subQ->whereHas('testProgress', fn($tq) => $tq->where('status', 'in_progress'))
                             ->orWhere(function($sq) {
                                 // Atau sudah selesai sebagian tapi belum semua
                                 $sq->where(function($innerQ) {
                                     $innerQ->whereHas('testProgress', fn($tq) => $tq->where('status', 'completed'))
                                            ->orWhereHas('latestRiasecScore')
                                            ->orWhereHas('latestMbtiScore');
                                 })
                                 ->where(function($innerQ) {
                                     // Tapi belum selesai semua
                                     $innerQ->whereDoesntHave('testProgress', fn($tq) => $tq->where('test_id', 1)->where('status', 'completed'))
                                            ->orWhereDoesntHave('latestRiasecScore')
                                            ->orWhereDoesntHave('latestMbtiScore');
                                 });
                             });
                    });
                    break;
                    
                case 'not_started':
                    // Kandidat yang belum mulai tes apapun
                    $q->whereDoesntHave('testProgress')
                      ->whereDoesntHave('latestRiasecScore')
                      ->whereDoesntHave('latestMbtiScore');
                    break;
            }
        });
        
        // UPDATED FILTER MBTI: Gunakan relasi yang benar
        $query->when($this->mbtiType, function($q) {
            $q->whereHas('latestMbtiScore', function($subQuery) {
                $subQuery->where('mbti_type', $this->mbtiType);
            });
        });
        
        // FILTER: Posisi Pekerjaan (tidak berubah)
        $query->when($this->jobPositionId, function($q) {
            $q->where('job_position_id', $this->jobPositionId);
        });
        
        // UPDATED FILTER RIASEC: Gunakan relasi baru
        $query->when($this->riasecType, function($q) {
            $q->whereHas('latestRiasecScore', function($subQuery) {
                // Filter berdasarkan huruf pertama dari riasec_code
                $subQuery->where('riasec_code', 'like', $this->riasecType . '%');
            });
        });
        
        // Sorting dan pagination
        $candidates = $query->orderBy($this->sortBy, $this->sortOrder)->paginate(10);

        return view('livewire.hr.candidate', [
            'candidates' => $candidates,
            'pageTitle' => 'Manajemen Kandidat'
        ]);
    }
}