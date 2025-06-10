<?php

namespace App\Http\Livewire\HR\Anp;

use App\Models\AnpAnalysis;
use Livewire\Component;
use Livewire\WithPagination;

class AnalysisList extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $perPage = 10;
    protected $paginationTheme = 'bootstrap';

    public function updatingSearchTerm()
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
        $analyses = AnpAnalysis::with(['jobPosition', 'hrUser'])
            ->where('name', 'like', '%' . $this->searchTerm . '%')
            ->orWhereHas('jobPosition', function ($query) {
                $query->where('name', 'like', '%' . $this->searchTerm . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.hr.anp.analysis-list', [
            'analyses' => $analyses,
        ]);
    }
}