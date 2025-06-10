<?php

namespace App\Http\Controllers;

use App\Models\UserTestProgress;
use App\Models\UserMbtiScore;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $testProgress = UserTestProgress::with('test')
                                      ->where('user_id', $user->id)
                                      ->orderBy('test_id')
                                      ->get();
        
        $mbtiScore = UserMbtiScore::where('user_id', $user->id)
                                 ->latest('calculated_at')
                                 ->first();
        
        $completedAll = $testProgress->where('status', 'completed')->count() === 3;
        
        return view('results.index', compact('testProgress', 'mbtiScore', 'completedAll'));
    }
    
    public function detail($testId)
    {
        $user = Auth::user();
        
        $progress = UserTestProgress::where('user_id', $user->id)
                                  ->where('test_id', $testId)
                                  ->where('status', 'completed')
                                  ->firstOrFail();
        
        $additionalData = [];
        
        if ($testId == 3) { 
            $additionalData['mbtiScore'] = UserMbtiScore::where('user_id', $user->id)
                                                       ->latest('calculated_at')
                                                       ->first();
        }
        
        return view('results.detail', compact('progress', 'additionalData'));
    }
}