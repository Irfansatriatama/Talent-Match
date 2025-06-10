<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\UserTestProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    public function index()
    {
        $tests = Test::orderBy('test_order')->get();
        $user = Auth::user();
        
        $testStatus = [];
        foreach ($tests as $test) {
            $progress = UserTestProgress::where('user_id', $user->id)
                                      ->where('test_id', $test->test_id)
                                      ->first();
            
            $testStatus[$test->test_id] = [
                'progress' => $progress,
                'can_start' => $this->canStartTest($test->test_id, $user),
                'status' => $progress ? $progress->status : 'not_started'
            ];
        }
        
        return view('assessment.index', compact('tests', 'testStatus'));
    }
    
    private function canStartTest($testId, $user)
    {
        if ($testId == 1) {
            return true;
        }
        
        $previousTestId = $testId - 1;
        $previousProgress = UserTestProgress::where('user_id', $user->id)
                                          ->where('test_id', $previousTestId)
                                          ->where('status', 'completed')
                                          ->first();
        
        return $previousProgress !== null;
    }
}