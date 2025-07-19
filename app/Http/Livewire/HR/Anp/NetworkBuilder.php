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
            // ALWAYS create NEW unique structure for each analysis
            $this->networkStructure = AnpNetworkStructure::create([
                'name' => 'Network untuk ' . $this->analysis->name . ' (Analisis #' . $this->analysis->id . ')',
                'description' => 'Struktur jaringan eksklusif untuk analisis: ' . $this->analysis->name,
                'is_frozen' => false,
                'version' => 1,
                'original_analysis_id' => $this->analysis->id, // Track ownership
                'is_template' => false
            ]);
            
            $this->analysis->anp_network_structure_id = $this->networkStructure->id;
            $this->analysis->save();
            
            Log::info('Created unique network structure', [
                'analysis_id' => $this->analysis->id,
                'analysis_name' => $this->analysis->name,
                'structure_id' => $this->networkStructure->id,
                'structure_name' => $this->networkStructure->name
            ]);
            
            // OPTIONAL: Copy from template if needed
            if (request()->has('use_template') && request('use_template') == 'true') {
                $this->copyFromTemplate();
            }
            
        } else {
            $this->networkStructure = AnpNetworkStructure::find($this->analysis->anp_network_structure_id);
            
            // CRITICAL SECURITY CHECK
            if (!$this->networkStructure) {
                throw new \Exception('Network structure not found');
            }
            
            // Verify ownership
            $usageCount = AnpAnalysis::where('anp_network_structure_id', $this->networkStructure->id)
                ->where('id', '!=', $this->analysis->id)
                ->count();
                
            if ($usageCount > 0) {
                Log::warning('CRITICAL: Network structure is shared!', [
                    'structure_id' => $this->networkStructure->id,
                    'analysis_id' => $this->analysis->id,
                    'other_analyses_using' => $usageCount
                ]);
                
                // Force create new structure
                $this->networkStructure = $this->createNewIsolatedStructure();
            }
        }
        
        // Verify isolation
        $this->verifyNetworkIsolation();
    }

    private function createIsolatedStructure()
    {
        $oldStructure = $this->networkStructure;
        
        // Create new isolated structure
        $this->networkStructure = AnpNetworkStructure::create([
            'name' => 'Network untuk ' . $this->analysis->name . ' (Isolated)',
            'description' => 'Isolated structure - previously shared with other analyses',
            'is_frozen' => false,
            'version' => 1,
            'original_analysis_id' => $this->analysis->id,
            'is_template' => false
        ]);
        
        // Deep copy all components
        DB::transaction(function () use ($oldStructure) {
            // Copy clusters
            $clusterMap = [];
            foreach ($oldStructure->clusters as $cluster) {
                $newCluster = $this->networkStructure->clusters()->create([
                    'name' => $cluster->name,
                    'description' => $cluster->description
                ]);
                $clusterMap[$cluster->id] = $newCluster->id;
            }
            
            // Copy elements
            $elementMap = [];
            foreach ($oldStructure->elements as $element) {
                $newElement = $this->networkStructure->elements()->create([
                    'anp_cluster_id' => isset($clusterMap[$element->anp_cluster_id]) ? 
                        $clusterMap[$element->anp_cluster_id] : null,
                    'name' => $element->name,
                    'description' => $element->description
                ]);
                $elementMap[$element->id] = $newElement->id;
            }
            
            // Copy dependencies
            foreach ($oldStructure->dependencies as $dep) {
                $sourceId = $dep->sourceable_type == AnpCluster::class ? 
                    ($clusterMap[$dep->sourceable_id] ?? null) : 
                    ($elementMap[$dep->sourceable_id] ?? null);
                    
                $targetId = $dep->targetable_type == AnpCluster::class ? 
                    ($clusterMap[$dep->targetable_id] ?? null) : 
                    ($elementMap[$dep->targetable_id] ?? null);
                    
                if ($sourceId && $targetId) {
                    $this->networkStructure->dependencies()->create([
                        'sourceable_type' => $dep->sourceable_type,
                        'sourceable_id' => $sourceId,
                        'targetable_type' => $dep->targetable_type,
                        'targetable_id' => $targetId,
                        'description' => $dep->description
                    ]);
                }
            }
        });
        
        // Update analysis
        $this->analysis->anp_network_structure_id = $this->networkStructure->id;
        $this->analysis->save();
        
        Log::info('Created isolated structure from shared one', [
            'analysis_id' => $this->analysis->id,
            'old_structure_id' => $oldStructure->id,
            'new_structure_id' => $this->networkStructure->id
        ]);
    }

    private function verifyNetworkIsolation()
    {
        $shareCount = AnpAnalysis::where('anp_network_structure_id', $this->networkStructure->id)
            ->count();
            
        if ($shareCount > 1) {
            throw new \Exception("Network isolation failed! Structure #{$this->networkStructure->id} is used by {$shareCount} analyses");
        }
    }

    // Update atau comment out copyTemplateStructure
    private function copyFromTemplate()
    {
        $template = AnpNetworkStructure::where('is_template', true)
            ->orderBy('created_at', 'desc')
            ->first();
            
        if (!$template) {
            Log::info('No template found to copy from');
            return;
        }
        
        // Copy CONTENT not structure reference
        DB::transaction(function () use ($template) {
            foreach ($template->clusters as $cluster) {
                $newCluster = $this->networkStructure->clusters()->create([
                    'name' => $cluster->name,
                    'description' => $cluster->description
                ]);
                
                foreach ($cluster->elements as $element) {
                    $newCluster->elements()->create([
                        'anp_network_structure_id' => $this->networkStructure->id,
                        'name' => $element->name,
                        'description' => $element->description
                    ]);
                }
            }
        });
        
        Log::info('Copied content from template', [
            'template_id' => $template->id,
            'to_structure_id' => $this->networkStructure->id
        ]);
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