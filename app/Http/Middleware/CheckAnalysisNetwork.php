<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AnpAnalysis;

class CheckAnalysisNetwork extends Middleware
{
    public function handle(Request $request, Closure $next)
    {
        $analysis = $request->route('analysis');
        
        if ($analysis instanceof AnpAnalysis) {
            if (!$analysis->hasNetworkStructure()) {
                return redirect()->route('h-r.anp.analysis.network.define', $analysis)
                    ->with('warning', 'Please define the network structure first.');
            }
        }
        
        return $next($request);
    }
}