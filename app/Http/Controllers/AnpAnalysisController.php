<?php

namespace App\Http\Controllers;

use App\Models\AnpAnalysis;
use App\Models\AnpDependency;
use App\Models\AnpElement;
use App\Services\AnpCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AnpAnalysisController extends Controller
{
    /**
     * 
     * @var AnpCalculationService
     */
    protected AnpCalculationService $calculationService;

    
    public function __construct(AnpCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('hr.anp-analysis.index');
    }

    /**
    
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('hr.anp-analysis.create');
    }

    /**
     
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
     
        return redirect()->route('hr.analysis.index');
    }

    /**
     
     *
     * @param  \App\Models\AnpAnalysis  $anpAnalysis
     * @return \Illuminate\Contracts\View\View
     */
    public function show(AnpAnalysis $anpAnalysis)
    {
        // Tambahkan eager loading untuk semua relasi yang diperlukan
        $anpAnalysis->load([
            'jobPosition', 
            'candidates', 
            'results.candidate',
            'networkStructure.clusters.elements',
            'networkStructure.dependencies.sourceable', 
            'networkStructure.dependencies.targetable',
            'criteriaComparisons.controlCriterionable', 
            'criteriaComparisons.consistency',
            'interdependencyComparisons.dependency', 
        'interdependencyComparisons.consistency',
        'alternativeComparisons.element', 
        'alternativeComparisons.consistency'
    ]);
    
    // Tetap menggunakan view yang sudah ada
    return view('livewire.analysis-ranking.show', compact('anpAnalysis'));
}

    /**
     *
     * @param  \App\Models\AnpAnalysis  $anpAnalysis
     * @return \Illuminate\Contracts\View\View
     */
    public function networkDefinition(AnpAnalysis $anpAnalysis)
    {

        return view('livewire.analysis-ranking.network-definition', compact('anpAnalysis'));
}

    /**
     *
     * @param  \App\Models\AnpAnalysis  $anpAnalysis
     * @param  string 
     * @param  int|null
     * @return \Illuminate\Contracts\View\View
     */
    public function criteriaComparison(AnpAnalysis $anpAnalysis, $controlCriterionContextType = 'goal', $controlCriterionContextId = null)
    {
        return view('hr.anp-analysis.pairwise-criteria', [
            'anpAnalysis' => $anpAnalysis,
            'controlCriterionContextType' => $controlCriterionContextType,
            'controlCriterionContextId' => $controlCriterionContextId,
        ]);
    }

    /**
     *
     * @param  \App\Models\AnpAnalysis  $anpAnalysis
     * @param  \App\Models\AnpDependency  $anpDependency
     * @return \Illuminate\Contracts\View\View
     */
    public function interdependencyComparison(AnpAnalysis $anpAnalysis, AnpDependency $anpDependency)
    {
        if ($anpDependency->anp_network_structure_id !== $anpAnalysis->anp_network_structure_id) {
            abort(404);
        }
        
        return view('livewire.analysis-ranking.pairwise-interdependencies', compact('anpAnalysis', 'anpDependency'));
    }

    /**
     *
     * @param  \App\Models\AnpAnalysis  $anpAnalysis
     * @param  \App\Models\AnpElement  $anpElement
     * @return \Illuminate\Contracts\View\View
     */
    public function alternativeComparison(AnpAnalysis $anpAnalysis, AnpElement $anpElement)
    {
        if ($anpElement->anp_network_structure_id !== $anpAnalysis->anp_network_structure_id) {
            abort(404, "Elemen kriteria tidak ditemukan dalam analisis ini.");
        }

        return view('livewire.analysis-ranking.pairwise-alternatives', [
            'anpAnalysis' => $anpAnalysis, 
            'criterionElement' => $anpElement
        ]);
    }

    /**
     *
     * @param  \App\Models\AnpAnalysis  $anpAnalysis
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculate(AnpAnalysis $anpAnalysis)
    {
        try {
            $result = $this->calculationService->processAnalysis($anpAnalysis);

            return response()->json([
                'status' => 'success',
                'message' => 'Kalkulasi ANP berhasil diselesaikan.',
                'redirect_url' => route('hr.analysis.show', $anpAnalysis) 
            ]);

        } catch (Exception $e) {
            Log::error("Controller-level ANP calculation error for Analysis ID: {$anpAnalysis->id}", ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi error tak terduga saat melakukan kalkulasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     *
     * @param  \App\Models\AnpAnalysis  $anpAnalysis
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(AnpAnalysis $anpAnalysis)
    {
        try {
            DB::transaction(function () use ($anpAnalysis) {
                $anpAnalysis->results()->delete();
                foreach($anpAnalysis->criteriaComparisons as $c) { $c->consistency()->delete(); }
                foreach($anpAnalysis->interdependencyComparisons as $c) { $c->consistency()->delete(); }
                foreach($anpAnalysis->alternativeComparisons as $c) { $c->consistency()->delete(); }
                $anpAnalysis->alternativeComparisons()->delete();
                $anpAnalysis->interdependencyComparisons()->delete();
                $anpAnalysis->criteriaComparisons()->delete();
                $anpAnalysis->candidates()->detach();
                
                $anpAnalysis->delete();
            });
            
            return redirect()->route('hr.analysis.index')->with('success', 'Analisis berhasil dihapus.');

        } catch (Exception $e) {
            Log::error("Gagal menghapus Analisis ID: {$anpAnalysis->id}", ['error' => $e->getMessage()]);
            return redirect()->route('hr.analysis.index')->with('error', 'Gagal menghapus analisis.');
        }
    }
}
