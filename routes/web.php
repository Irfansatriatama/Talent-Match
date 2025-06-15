<?php

// auth
use App\Http\Livewire\Auth\ForgotPassword;
use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Auth\Register;
use App\Http\Livewire\Auth\ResetPassword;

// main 
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\UserProfile;

// assessment
use App\Http\Livewire\Candidate\Assessment\AssessmentTest;
use App\Http\Livewire\Candidate\Assessment\AssessmentProgramming;
use App\Http\Livewire\Candidate\Assessment\AssessmentRiasec;
use App\Http\Livewire\Candidate\Assessment\AssessmentMbti;

// hr components
use App\Http\Livewire\HR\Setting;
use App\Http\Livewire\HR\DashboardHr;
use App\Http\Livewire\HR\Candidate;
use App\Http\Livewire\HR\Setting as HRSetting;
use App\Http\Livewire\HR\DetailCandidate;

// ANP Livewire
use App\Http\Livewire\HR\Anp\AnalysisList;
use App\Http\Livewire\HR\Anp\CreateAnalysisForm;
use App\Http\Livewire\HR\Anp\NetworkBuilder;
use App\Http\Livewire\HR\Anp\PairwiseAlternativesMatrix;
use App\Http\Livewire\HR\Anp\PairwiseCriteriaMatrix;
use App\Http\Livewire\HR\Anp\PairwiseInterdependenciesMatrix;

use App\Http\Controllers\AnpAnalysisController;

use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::get('/', function(){
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', Login::class)->name('login');
    Route::get('register', Register::class)->name('register');
    Route::get('forgot-password', ForgotPassword::class)->name('password.forgot');
});

Route::get('reset-password/{id}', ResetPassword::class)->middleware('signed')->name('reset-password');

Route::middleware('auth')->group(function () {
    Route::middleware('role:' . User::ROLE_CANDIDATE)->group(function() {
        Route::get('dashboard', Dashboard::class)->name('dashboard');
        Route::get('profile', UserProfile::class)->name('profile');
    });

    Route::prefix('candidate')->name('candidate.')->middleware('role:' . User::ROLE_CANDIDATE)->group(function() {
        Route::prefix('assessment')->name('assessment.')->group(function () {
            Route::get('test', AssessmentTest::class)->name('test');
            Route::get('programming', AssessmentProgramming::class)->name('programming');
            Route::get('riasec', AssessmentRiasec::class)->name('riasec');
            Route::get('mbti', AssessmentMbti::class)->name('mbti');
        });
    });

    Route::prefix('hr')->name('HR.')->middleware('role:' . User::ROLE_HR)->group(function() {
        Route::get('dashboard', DashboardHr::class)->name('dashboard');
        Route::get('candidates', Candidate::class)->name('candidates');
        Route::get('settings', Setting::class)->name('settings');
        Route::get('candidates/{candidate}', DetailCandidate::class)->name('detail-candidate');
        

        Route::prefix('anp')->name('anp.')->group(function() {
            Route::prefix('analysis')->name('analysis.')->group(function() {
                Route::get('/', AnalysisList::class)->name('index');
                Route::get('/create', CreateAnalysisForm::class)->name('create');
                Route::get('/network-builder', NetworkBuilder::class)->name('network-builder');
                Route::get('/pairwise-criteria', PairwiseCriteriaMatrix::class)->name('pairwise-criteria');
                Route::get('/pairwise-alternatives', PairwiseAlternativesMatrix::class)->name('pairwise-alternatives');
                Route::get('/pairwise-interdependencies', PairwiseInterdependenciesMatrix::class)->name('pairwise-interdependencies');
                Route::get('/{anpAnalysis}/network-definition', [AnpAnalysisController::class, 'networkDefinition'])->name('network.define');
                Route::get('/{anpAnalysis}/result', [AnpAnalysisController::class, 'show'])->name('show');
                Route::get('/{anpAnalysis}/pairwise/interdependency/{anpDependency}', [AnpAnalysisController::class, 'interdependencyComparison'])->name('interdependency.pairwise.form');
                Route::get('/{anpAnalysis}/pairwise/alternative/{anpElement}', [AnpAnalysisController::class, 'alternativeComparison'])->name('alternative.pairwise.form');
                Route::post('/{anpAnalysis}/calculate', [AnpAnalysisController::class, 'calculate'])->name('calculate');
                Route::delete('/{anpAnalysis}', [AnpAnalysisController::class, 'destroy'])->name('destroy');

            });
        });
    });
});