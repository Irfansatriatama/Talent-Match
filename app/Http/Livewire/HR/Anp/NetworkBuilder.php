<?php

namespace App\Http\Livewire\HR\Anp;

use App\Models\AnpAnalysis;
use App\Models\AnpNetworkStructure;
use App\Models\AnpElement;
use App\Models\AnpCluster;
use App\Models\AnpDependency;
use App\Models\AnpStructureSnapshot;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        
        $this->initializeNetworkStructure();
        $this->loadNetworkData();
    }

    private function initializeNetworkStructure()
    {
        $this->resetFormFields();
        
        if (!$this->analysis->anp_network_structure_id) {
            // Create NEW unique structure for this analysis
            $this->networkStructure = AnpNetworkStructure::create([
                'name' => 'Network untuk ' . $this->analysis->name . ' (ID: ' . $this->analysis->id . ')',
                'description' => 'Struktur jaringan unik untuk analisis ' . $this->analysis->name,
                'is_frozen' => false,
                'version' => 1
            ]);
            
            $this->analysis->anp_network_structure_id = $this->networkStructure->id;
            $this->analysis->save();
            
            // Copy template structure if exists
            $this->copyTemplateStructure();
            
            Log::info('Created new network structure', [
                'analysis_id' => $this->analysis->id,
                'structure_id' => $this->networkStructure->id
            ]);
        } else {
            // Load existing structure
            $this->networkStructure = AnpNetworkStructure::find($this->analysis->anp_network_structure_id);
            
            // Check if structure is frozen
            if ($this->networkStructure->is_frozen) {
                // If frozen, we need to work on a new version
                $this->dispatch('notify', [
                    'message' => 'Struktur ini telah di-freeze. Bekerja pada versi baru.',
                    'type' => 'info'
                ]);
            }
        }
    }

    private function copyTemplateStructure()
    {
        $templateStructure = AnpNetworkStructure::where('name', 'LIKE', '%Template%')
            ->orWhere('name', 'LIKE', '%Default Template%')
            ->first();
            
        if (!$templateStructure) {
            Log::info('No template structure found');
            return;
        }
        
        DB::transaction(function() use ($templateStructure) {
            // Copy clusters with NEW IDs
            $clusterMapping = [];
            foreach ($templateStructure->clusters as $oldCluster) {
                $newCluster = $this->networkStructure->clusters()->create([
                    'name' => $oldCluster->name,
                    'description' => $oldCluster->description,
                ]);
                $clusterMapping[$oldCluster->id] = $newCluster->id;
            }
            
            // Copy elements with NEW IDs
            $elementMapping = [];
            foreach ($templateStructure->elements as $oldElement) {
                $newElement = $this->networkStructure->elements()->create([
                    'name' => $oldElement->name,
                    'description' => $oldElement->description,
                    'anp_cluster_id' => isset($clusterMapping[$oldElement->anp_cluster_id]) 
                        ? $clusterMapping[$oldElement->anp_cluster_id] 
                        : null,
                ]);
                $elementMapping[$oldElement->id] = $newElement->id;
            }
            
            // Copy dependencies with NEW IDs
            foreach ($templateStructure->dependencies as $oldDep) {
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
        });
        
        Log::info('Template structure copied successfully');
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
            // Only load NON-deleted data
            $this->allElements = $this->networkStructure->elements()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get();
                
            $this->allClusters = $this->networkStructure->clusters()
                ->whereNull('deleted_at')
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

        if ($this->networkStructure->is_frozen) {
            $this->dispatch('notify', [
                'message' => 'Struktur ini sudah di-freeze. Tidak dapat menambah elemen.',
                'type' => 'error'
            ]);
            return;
        }

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
        if ($this->networkStructure->is_frozen) {
            $this->dispatch('notify', [
                'message' => 'Struktur ini sudah di-freeze. Tidak dapat menghapus elemen.',
                'type' => 'error'
            ]);
            return;
        }

        $element = AnpElement::find($elementId);
        
        if ($element) {
            DB::transaction(function() use ($element) {
                // Soft delete dependencies using model relationships
                $element->sourceDependencies()->each(function($dep) {
                    $dep->delete(); // This triggers soft delete
                });
                
                $element->targetDependencies()->each(function($dep) {
                    $dep->delete(); // This triggers soft delete
                });
                
                // Soft delete element
                $element->delete();
            });
            
            $this->loadNetworkData();
            $this->dispatch('notify', ['message' => 'Elemen berhasil dihapus.', 'type' => 'success']);
        }
    }

    public function addCluster()
    {
        $this->validate([
            'newClusterName' => 'required|string|max:255',
        ]);

        if ($this->networkStructure->is_frozen) {
            $this->dispatch('notify', [
                'message' => 'Struktur ini sudah di-freeze. Tidak dapat menambah cluster.',
                'type' => 'error'
            ]);
            return;
        }

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
        if ($this->networkStructure->is_frozen) {
            $this->dispatch('notify', [
                'message' => 'Struktur ini sudah di-freeze. Tidak dapat menghapus cluster.',
                'type' => 'error'
            ]);
            return;
        }

        $cluster = AnpCluster::find($clusterId);
        
        if ($cluster) {
            DB::transaction(function() use ($cluster) {
                // Use model's soft delete which will cascade
                $cluster->delete();
            });
            
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

        if ($this->networkStructure->is_frozen) {
            $this->dispatch('notify', [
                'message' => 'Struktur ini sudah di-freeze. Tidak dapat menambah dependensi.',
                'type' => 'error'
            ]);
            return;
        }

        if ($this->sourceId == $this->targetId && $this->sourceType == $this->targetType) {
            $this->dispatch('notify', ['message' => 'Sumber dan target tidak boleh sama.', 'type' => 'error']);
            return;
        }

        $exists = $this->networkStructure->dependencies()
            ->where('sourceable_type', $this->sourceType == 'element' ? AnpElement::class : AnpCluster::class)
            ->where('sourceable_id', $this->sourceId)
            ->where('targetable_type', $this->targetType == 'element' ? AnpElement::class : AnpCluster::class)
            ->where('targetable_id', $this->targetId)
            ->whereNull('deleted_at') // Check for soft deletes
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
        if ($this->networkStructure->is_frozen) {
            $this->dispatch('notify', [
                'message' => 'Struktur ini sudah di-freeze. Tidak dapat menghapus dependensi.',
                'type' => 'error'
            ]);
            return;
        }

        $dependency = AnpDependency::find($dependencyId);
        if ($dependency) {
            $dependency->delete(); // Soft delete
            $this->loadNetworkData();
            $this->dispatch('notify', ['message' => 'Dependensi berhasil dihapus.', 'type' => 'success']);
        }
    }

    public function proceedToCriteriaComparison()
    {
        if ($this->networkStructure->elements()->count() < 2) {
            $this->dispatch('notify', ['message' => 'Minimal harus ada 2 elemen untuk melanjutkan.', 'type' => 'error']);
            return;
        }
        
        DB::transaction(function() {
            // Create snapshot before freezing
            AnpStructureSnapshot::createFromStructure(
                $this->analysis,
                $this->networkStructure,
                'proceed_to_comparison',
                'Snapshot created when proceeding to criteria comparison'
            );
            
            // Freeze current structure
            if (!$this->networkStructure->is_frozen) {
                $this->networkStructure->freeze();
                
                Log::info('Network structure frozen', [
                    'analysis_id' => $this->analysis->id,
                    'structure_id' => $this->networkStructure->id
                ]);
            }
            
            // Update analysis status
            $this->analysis->status = 'criteria_comparison_pending';
            $this->analysis->save();
        });
        
        // Set session for next step
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
                    ->whereNull('deleted_at') // Only show non-deleted
                    ->with(['sourceable', 'targetable'])
                    ->get() : 
                collect(),
            'isStructureFrozen' => $this->networkStructure ? $this->networkStructure->is_frozen : false,
        ]);
    }
}