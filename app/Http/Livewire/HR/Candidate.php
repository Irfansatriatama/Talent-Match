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
    public string $jobPositionId = ''; // TAMBAHAN: Filter posisi pekerjaan
    public string $riasecType = '';     // TAMBAHAN: Filter tipe RIASEC
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

        // Query builder dengan eager loading
        $query = User::where('role', User::ROLE_CANDIDATE)
                    ->with(['testProgress.test', 'latestMbtiScore', 'jobPosition']);

        // FILTER: Pencarian nama/email
        $query->when($this->search, function($q) {
            $q->where(function($subQuery) {
                $subQuery->where('name', 'like', '%'.$this->search.'%')
                         ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        });

        // FILTER: Status penyelesaian tes
        $query->when($this->status, function($q) use ($totalTests) {
            switch($this->status) {
                case 'completed':
                    // Kandidat yang sudah selesai semua tes
                    $q->whereHas('testProgress', fn($subQ) => $subQ->where('status', 'completed'), '>=', $totalTests);
                    break;
                case 'in_progress':
                    // Kandidat yang sedang mengerjakan (ada tes in_progress tapi belum selesai semua)
                    $q->whereHas('testProgress', fn($subQ) => $subQ->where('status', 'in_progress'))
                      ->whereDoesntHave('testProgress', fn($subQ) => $subQ->where('status', 'completed'), '>=', $totalTests);
                    break;
                case 'not_started':
                    // Kandidat yang belum mulai tes apapun
                    $q->whereDoesntHave('testProgress');
                    break;
            }
        });
        
        // PERBAIKAN FILTER MBTI: Gunakan whereHas dengan benar
        $query->when($this->mbtiType, function($q) {
            $q->whereHas('latestMbtiScore', function($subQuery) {
                $subQuery->where('mbti_type', $this->mbtiType);
            });
        });
        
        // FILTER BARU: Posisi Pekerjaan
        $query->when($this->jobPositionId, function($q) {
            $q->where('job_position_id', $this->jobPositionId);
        });
        
        // FILTER BARU: Tipe RIASEC (berdasarkan huruf dominan di hasil RIASEC)
        $query->when($this->riasecType, function($q) {
            $q->whereHas('testProgress', function($subQuery) {
                $subQuery->where('test_id', 2) // Asumsi test_id 2 adalah tes RIASEC
                         ->where('status', 'completed')
                         ->where('result_summary', 'like', $this->riasecType . '%'); // Cek huruf pertama
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