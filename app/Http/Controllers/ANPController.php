<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ANPCalculation;
use App\Services\ANPService;
use Illuminate\Http\Request;

class ANPController extends Controller
{
    protected $anpService;
    
    public function __construct(ANPService $anpService)
    {
        $this->anpService = $anpService;
    }
    
    public function index()
    {
        $calculations = ANPCalculation::with('calculator')
                                    ->latest('calculation_date')
                                    ->paginate(10);
        
        return view('anp.index', compact('calculations'));
    }
    
    public function create()
    {
        $candidates = User::where('role', 'candidate')
                        ->whereHas('testProgress', function($q) {
                            $q->where('status', 'completed')
                              ->groupBy('user_id')
                              ->havingRaw('COUNT(DISTINCT test_id) = 3');
                        })
                        ->with(['testProgress', 'mbtiScores'])
                        ->get();
        
        return view('anp.create', compact('candidates'));
    }
    
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'candidate_ids' => 'required|array|min:2',
            'candidate_ids.*' => 'exists:users,id',
            'job_position' => 'required|string|max:255',
            'weights' => 'required|array',
            'weights.programming' => 'required|numeric|between:0,1',
            'weights.riasec' => 'required|numeric|between:0,1',
            'weights.mbti' => 'required|numeric|between:0,1',
            'notes' => 'nullable|string'
        ]);
        
        $weightSum = array_sum($validated['weights']);
        if (abs($weightSum - 1) > 0.01) {
            return back()->withErrors(['weights' => 'Weights must sum to 1']);
        }
        
        $candidates = User::whereIn('id', $validated['candidate_ids'])
                        ->with(['testProgress', 'mbtiScores'])
                        ->get();
        
        $results = $this->anpService->calculate($candidates, $validated['weights']);
        
        $calculation = ANPCalculation::create([
            'calculated_by' => auth()->id(),
            'calculation_date' => now(),
            'job_position' => $validated['job_position'],
            'input_data' => $candidates->map(fn($c) => [
                'user_id' => $c->id,
                'name' => $c->name,
                'email' => $c->email,
                'test_scores' => $c->testProgress->mapWithKeys(fn($tp) => [
                    $tp->test->test_name => [
                        'score' => $tp->score,
                        'summary' => $tp->result_summary
                    ]
                ])
            ]),
            'anp_weights' => $validated['weights'],
            'final_rankings' => $results,
            'notes' => $validated['notes']
        ]);
        
        return redirect()->route('anp.show', $calculation->id);
    }
    
    public function show($id)
    {
        $calculation = ANPCalculation::with('calculator')->findOrFail($id);
        
        return view('anp.show', compact('calculation'));
    }
    
    public function export($id)
    {
        $calculation = ANPCalculation::findOrFail($id);
        
        return response()->json($calculation);
    }
}