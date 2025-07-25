<?php

namespace App\Http\Livewire\HR\Anp;

use App\Models\AnpAnalysis;
use Livewire\Component;
use Livewire\WithPagination;

class AnalysisList extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $statusFilter = ''; 
    public $perPage = 10;
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        session()->forget('current_anp_analysis_id');
        session()->forget('anp_pairwise_context');
        
        $this->searchTerm = '';
        $this->statusFilter = '';
        $this->perPage = 10;
    }

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
        
        $query->when($this->searchTerm, function($q) {
            $q->where('name', 'like', '%' . $this->searchTerm . '%')
              ->orWhereHas('jobPosition', function ($subQuery) {
                  $subQuery->where('name', 'like', '%' . $this->searchTerm . '%');
              });
        });
        
        $query->when($this->statusFilter, function($q) {
            $q->where('status', $this->statusFilter);
        });
        
        $analyses = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.h-r.anp.analysis-list', [
            'analyses' => $analyses,
        ]);
    }
}