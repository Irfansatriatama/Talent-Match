<?php

namespace App\Http\Livewire\HR\Anp;

use App\Models\AnpAnalysis;
use Livewire\Component;
use Livewire\WithPagination;

class AnalysisList extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $statusFilter = ''; // TAMBAHAN: Filter status
    public $perPage = 10;
    protected $paginationTheme = 'bootstrap';

    // Reset halaman saat filter berubah
    public function updatingSearchTerm()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function deleteAnalysis($analysisId)
    {
        try {
            $analysis = AnpAnalysis::findOrFail($analysisId);
            $analysis->delete();
            session()->flash('message', 'Analisis ANP berhasil dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus analisis ANP: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = AnpAnalysis::with(['jobPosition', 'hrUser']);
        
        // Filter pencarian
        $query->when($this->searchTerm, function($q) {
            $q->where('name', 'like', '%' . $this->searchTerm . '%')
              ->orWhereHas('jobPosition', function ($subQuery) {
                  $subQuery->where('name', 'like', '%' . $this->searchTerm . '%');
              });
        });
        
        // FILTER BARU: Berdasarkan status
        $query->when($this->statusFilter, function($q) {
            $q->where('status', $this->statusFilter);
        });
        
        // Ordering dan pagination
        $analyses = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.hr.anp.analysis-list', [
            'analyses' => $analyses,
        ]);
    }
}