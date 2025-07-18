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

    public function mount(AnpAnalysis $anpAnalysis = null)
    {
        if ($anpAnalysis) {
            $this->analysis = $anpAnalysis;
            session()->put('current_anp_analysis_id', $anpAnalysis->id);
        } else {
            $analysisId = session('current_anp_analysis_id');
            if (!$analysisId) {
                return redirect()->route('h-r.anp.analysis.index');
            }
            $this->analysis = AnpAnalysis::findOrFail($analysisId);
        }
        
        $this->resetFormFields();
        
        // SELALU buat struktur BARU untuk setiap analisis!
        if (!$this->analysis->anp_network_structure_id) {
            // CREATE NEW unique structure
            $this->networkStructure = AnpNetworkStructure::create([
                'name' => 'Network untuk ' . $this->analysis->name . ' (ID: ' . $this->analysis->id . ')',
                'description' => 'Struktur jaringan unik untuk analisis ' . $this->analysis->name,
            ]);
            
            $this->analysis->anp_network_structure_id = $this->networkStructure->id;
            $this->analysis->save();
            
            // Copy template struktur jika ada
            $this->copyTemplateStructure();
        } else {
            // Load struktur yang sudah ada untuk analisis ini
            $this->networkStructure = AnpNetworkStructure::find($this->analysis->anp_network_structure_id);
        }
        
        $this->loadNetworkData();
    }

    private function copyTemplateStructure()
    {
        // Copy dari template, BUKAN dari struktur yang dipakai analisis lain
        $templateStructure = AnpNetworkStructure::where('name', 'LIKE', '%Template%')
            ->orWhere('name', 'LIKE', '%Default Template%')
            ->first();
            
        if (!$templateStructure) {
            // Jika tidak ada template, kosongkan saja
            return;
        }
        
        // Copy clusters dengan ID BARU
        $clusterMapping = [];
        foreach ($templateStructure->clusters as $oldCluster) {
            $newCluster = $this->networkStructure->clusters()->create([
                'name' => $oldCluster->name,
                'description' => $oldCluster->description,
            ]);
            $clusterMapping[$oldCluster->id] = $newCluster->id;
        }
        
        // Copy elements dengan ID BARU
        $elementMapping = [];
        foreach ($templateStructure->elements as $oldElement) {
            $newElement = $this->networkStructure->elements()->create([
                'name' => $oldElement->name,
                'description' => $oldElement->description,
                'anp_cluster_id' => $clusterMapping[$oldElement->anp_cluster_id] ?? null,
            ]);
            $elementMapping[$oldElement->id] = $newElement->id;
        }
        
        // Copy dependencies dengan ID BARU
        foreach ($templateStructure->dependencies as $oldDep) {
            // Map ke ID baru
            $sourceId = null;
            $targetId = null;
            
            if ($oldDep->sourceable_type == AnpCluster::class) {
                $sourceId = $clusterMapping[$oldDep->sourceable_id] ?? null;
            } else {
                $sourceId = $elementMapping[$oldDep->sourceable_id] ?? null;
            }
            
            if ($oldDep->targetable_type == AnpCluster::class) {
                $targetId = $clusterMapping[$oldDep->targetable_id] ?? null;
            } else {
                $targetId = $elementMapping[$oldDep->targetable_id] ?? null;
            }
            
            if ($sourceId && $targetId) {
                $this->networkStructure->dependencies()->create([
                    'sourceable_type' => $oldDep->sourceable_type,
                    'sourceable_id' => $sourceId,
                    'targetable_type' => $oldDep->targetable_type,
                    'targetable_id' => $targetId,
                    'description' => $oldDep->description,
                ]);
            }
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
        $cluster = AnpCluster::find($clusterId);
        if ($cluster) {
            // Hapus dependencies terkait cluster
            AnpDependency::where(function ($q) use ($clusterId) {
                $q->where('sourceable_type', AnpCluster::class)->where('sourceable_id', $clusterId);
            })->orWhere(function ($q) use ($clusterId) {
                $q->where('targetable_type', AnpCluster::class)->where('targetable_id', $clusterId);
            })->delete();
            
            // Hapus dependencies terkait elements dalam cluster
            $elementIds = AnpElement::where('anp_cluster_id', $clusterId)->pluck('id')->toArray();
            if (!empty($elementIds)) {
                AnpDependency::where(function ($q) use ($elementIds) {
                    $q->where('sourceable_type', AnpElement::class)->whereIn('sourceable_id', $elementIds);
                })->orWhere(function ($q) use ($elementIds) {
                    $q->where('targetable_type', AnpElement::class)->whereIn('targetable_id', $elementIds);
                })->delete();
            }
            
            // CASCADE DELETE: Hapus semua elements dalam cluster
            AnpElement::where('anp_cluster_id', $clusterId)->delete();
            
            // Hapus cluster
            $cluster->delete();
            
            $this->loadNetworkData();
            $this->dispatch('notify', ['message' => 'Cluster berhasil dihapus.', 'type' => 'success']);
        }
    }

    public function addDependency()
    {
        $this->validate([
            'sourceId' => 'required|integer',
            'targetId' => 'required|integer',
        ]);

        if ($this->sourceId == $this->targetId && $this->sourceType == $this->targetType) {
            $this->dispatch('notify', ['message' => 'Sumber dan target tidak boleh sama.', 'type' => 'error']);
            return;
        }

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
        
        // Set session untuk langkah berikutnya
        session()->put('anp_pairwise_context', [
            'control_criterion_context_type' => 'goal',
            'control_criterion_context_id' => null,
        ]);
        
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