<?php

namespace App\Http\Livewire\HR\Anp;

use App\Models\AnpAnalysis;
use App\Models\AnpNetworkStructure;
use App\Models\AnpElement;
use App\Models\AnpCluster;
use App\Models\AnpDependency;
use Livewire\Component;

class NetworkBuilder extends Component
{
    public AnpAnalysis $analysis;
    public ?AnpNetworkStructure $networkStructure = null;

    public $newElementName = '';
    public $newElementDescription = '';
    public $selectedClusterForNewElement = null;

    public $newClusterName = '';
    public $newClusterDescription = '';

    public $sourceType = 'element'; 
    public $sourceId = null;
    public $targetType = 'element'; 
    public $targetId = null;
    public $dependencyDescription = '';

    public $allElements = [];
    public $allClusters = [];

    protected $listeners = ['networkStructureUpdated' => 'loadNetworkData'];

    public function mount()
    {
        $analysisId = session('current_anp_analysis_id');

        if (!$analysisId) {
            session()->flash('error', 'Sesi analisis tidak ditemukan. Harap mulai dari awal.');
            return redirect()->route('h-r.anp.analysis.index');
        }

        $this->analysis = AnpAnalysis::findOrFail($analysisId);
        
        // Reset semua state properties
        $this->resetFormFields();
        
        if ($this->analysis->anp_network_structure_id) {
            // Mode EDIT: Load existing network structure
            $this->networkStructure = AnpNetworkStructure::find($this->analysis->anp_network_structure_id);
            
            // Hanya load data yang terkait dengan network structure ini
            if ($this->networkStructure) {
                $this->loadNetworkData();
            }
        } else {
            // Mode CREATE: Buat struktur baru
            $this->networkStructure = AnpNetworkStructure::create([
                'name' => 'Jaringan untuk ' . $this->analysis->name,
                'description' => 'Struktur jaringan default untuk analisis ' . $this->analysis->name,
            ]);
            
            $this->analysis->anp_network_structure_id = $this->networkStructure->id;
            $this->analysis->save();
        }
    }

    private function resetFormFields()
    {
        $this->newElementName = '';
        $this->newElementDescription = '';
        $this->selectedClusterForNewElement = null;
        $this->newClusterName = '';
        $this->newClusterDescription = '';
        $this->sourceType = 'element';
        $this->sourceId = null;
        $this->targetType = 'element';
        $this->targetId = null;
        $this->dependencyDescription = '';
    }

    public function loadNetworkData()
    {
        if ($this->networkStructure) {
            // PENTING: Hanya load data yang terkait dengan network structure ini
            $this->allElements = $this->networkStructure->elements()
                ->orderBy('name')
                ->get();
                
            $this->allClusters = $this->networkStructure->clusters()
                ->orderBy('name')
                ->get();
        } else {
            $this->allElements = collect();
            $this->allClusters = collect();
        }
    }

    public function addElement()
    {
        $this->validate([
            'newElementName' => 'required|string|max:255',
            'selectedClusterForNewElement' => 'nullable|exists:anp_clusters,id',
        ]);

        // PENTING: Pastikan element dibuat dengan network structure yang benar
        $this->networkStructure->elements()->create([
            'name' => $this->newElementName,
            'description' => $this->newElementDescription,
            'anp_cluster_id' => $this->selectedClusterForNewElement ?: null,
        ]);
        
        $this->newElementName = '';
        $this->newElementDescription = '';
        $this->selectedClusterForNewElement = null;
        
        $this->loadNetworkData();
        $this->dispatch('notify', ['message' => 'Elemen berhasil ditambahkan.', 'type' => 'success']);
    }

    public function deleteElement($elementId)
    {
        // Hapus dependencies terkait
        AnpDependency::where(function ($q) use ($elementId) {
            $q->where('sourceable_type', AnpElement::class)->where('sourceable_id', $elementId);
        })->orWhere(function ($q) use ($elementId) {
            $q->where('targetable_type', AnpElement::class)->where('targetable_id', $elementId);
        })->delete();
        
        // Soft delete element
        AnpElement::find($elementId)->delete();
        
        $this->loadNetworkData();
        $this->dispatch('notify', ['message' => 'Elemen berhasil dihapus.', 'type' => 'success']);
    }

    public function addCluster()
    {
        $this->validate([
            'newClusterName' => 'required|string|max:255',
        ]);
        
        // PENTING: Pastikan cluster dibuat dengan network structure yang benar
        $this->networkStructure->clusters()->create([
            'name' => $this->newClusterName,
            'description' => $this->newClusterDescription,
        ]);
        
        $this->newClusterName = '';
        $this->newClusterDescription = '';
        
        $this->loadNetworkData();
        $this->dispatch('notify', ['message' => 'Cluster berhasil ditambahkan.', 'type' => 'success']);
    }

    public function deleteCluster($clusterId)
    {
        // Dengan cascade delete, elements akan otomatis terhapus
        $cluster = AnpCluster::find($clusterId);
        if ($cluster) {
            // Hapus dependencies terkait cluster
            AnpDependency::where(function ($q) use ($clusterId) {
                $q->where('sourceable_type', AnpCluster::class)->where('sourceable_id', $clusterId);
            })->orWhere(function ($q) use ($clusterId) {
                $q->where('targetable_type', AnpCluster::class)->where('targetable_id', $clusterId);
            })->delete();
            
            $cluster->delete();
            $this->loadNetworkData();
            $this->dispatch('notify', ['message' => 'Cluster berhasil dihapus.', 'type' => 'success']);
        }
    }

    public function addDependency()
    {
        $this->validate([
            'sourceId' => 'required',
            'targetId' => 'required',
        ]);

        if ($this->sourceId == $this->targetId && $this->sourceType == $this->targetType) {
            $this->dispatch('notify', ['message' => 'Sumber dan target tidak boleh sama.', 'type' => 'error']);
            return;
        }

        // Check if dependency already exists
        $exists = $this->networkStructure->dependencies()
            ->where('sourceable_type', $this->sourceType == 'element' ? AnpElement::class : AnpCluster::class)
            ->where('sourceable_id', $this->sourceId)
            ->where('targetable_type', $this->targetType == 'element' ? AnpElement::class : AnpCluster::class)
            ->where('targetable_id', $this->targetId)
            ->exists();

        if ($exists) {
            $this->dispatch('notify', ['message' => 'Dependensi tersebut sudah ada.', 'type' => 'warning']);
            return;
        }

        $this->networkStructure->dependencies()->create([
            'sourceable_type' => $this->sourceType == 'element' ? AnpElement::class : AnpCluster::class,
            'sourceable_id' => $this->sourceId,
            'targetable_type' => $this->targetType == 'element' ? AnpElement::class : AnpCluster::class,
            'targetable_id' => $this->targetId,
            'description' => $this->dependencyDescription,
        ]);
        
        $this->reset(['sourceType', 'sourceId', 'targetType', 'targetId', 'dependencyDescription']);
        $this->sourceType = 'element'; 
        $this->targetType = 'element'; 
        
        $this->loadNetworkData();
        $this->dispatch('notify', ['message' => 'Dependensi berhasil ditambahkan.', 'type' => 'success']);
    }

    public function deleteDependency($dependencyId)
    {
        AnpDependency::find($dependencyId)->delete();
        $this->loadNetworkData();
        $this->dispatch('notify', ['message' => 'Dependensi berhasil dihapus.', 'type' => 'success']);
    }

    public function proceedToCriteriaComparison()
    {
        if ($this->networkStructure->elements()->count() < 2) {
            $this->dispatch('notify', ['message' => 'Minimal harus ada 2 elemen untuk melanjutkan.', 'type' => 'error']);
            return;
        }
        
        $this->analysis->status = 'criteria_comparison_pending';
        $this->analysis->save();
        
        return redirect()->route('h-r.anp.analysis.pairwise-criteria', $this->analysis->id);
    }

    public function render()
    {
        return view('livewire.h-r.anp.network-builder', [
            'dependencies' => $this->networkStructure ? 
                $this->networkStructure->dependencies()
                    ->with(['sourceable', 'targetable'])
                    ->get() : 
                collect(),
        ]);
    }
}