<?php

namespace App\Http\Livewire;

use App\Models\Test;
use App\Models\User; 
use App\Models\UserTestProgress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Dashboard extends Component
{
    public string $userName = "";
    public int $totalTestsCount = 0;
    public int $completedTestsCount = 0;
    public float $completionPercentage = 0.0;
    public string $progressMessage = '';
    public array $testsList = [];
    public bool $isProfileComplete = false; 
    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            $this->userName = "Guest";
            $this->progressMessage = "Silakan login untuk melihat progres asesmen Anda.";
            Log::warning('Dashboard.php - mount: User not authenticated.');
            return;
        }
        $this->userName = $user->name;

        $this->isProfileComplete = !empty($user->name) &&
                                   !empty($user->email) &&
                                   !empty($user->phone) &&
                                   !empty($user->job_position) && 
                                   !empty(trim($user->profile_summary ?? '')); 


        $allTests = Test::orderBy('test_order')->get();
        $this->totalTestsCount = $allTests->count();

        if ($this->totalTestsCount > 0) {
            $userProgressRecords = UserTestProgress::where('user_id', $user->id)
                                    ->whereIn('test_id', $allTests->pluck('test_id'))
                                    ->get()
                                    ->keyBy('test_id');

            $this->completedTestsCount = $userProgressRecords->where('status', 'completed')->count();
            $this->completionPercentage = ($this->totalTestsCount > 0) ? (($this->completedTestsCount / $this->totalTestsCount) * 100) : 0;
            $this->progressMessage = "Anda telah menyelesaikan {$this->completedTestsCount} dari {$this->totalTestsCount} tes yang dibutuhkan.";

            $tempTestDetails = [];
            foreach ($allTests as $index => $test) {
                $currentTestType = trim($test->test_type ?? '');
                if (empty($currentTestType) || !in_array($currentTestType, ['programming', 'riasec', 'mbti'])) {
                    Log::error("Dashboard.php - mount: Test ID {$test->test_id} ('{$test->test_name}') has invalid test_type value: '{$test->test_type}'. Skipping.");
                    $this->totalTestsCount = max(0, $this->totalTestsCount - 1); 
                    continue;
                }

                $progress = $userProgressRecords->get($test->test_id);
                $status = $progress ? $progress->status : 'not_started';

                $testItem = [
                    'id' => $test->test_id,
                    'name' => $test->test_name,
                    'description' => $test->description,
                    'time_limit_minutes' => $test->time_limit_minutes,
                    'order' => $test->test_order,
                    'status_internal' => $status,
                    'score' => ($progress && $currentTestType === 'programming') ? $progress->score : null,
                    'result_summary' => ($progress && in_array($currentTestType, ['riasec', 'mbti'])) ? $progress->result_summary : null,
                    'test_type' => $currentTestType,
                    'icon' => $this->getTestIcon($currentTestType),
                    'can_start' => false,
                    'display_status_text' => 'Inisialisasi...',
                    'show_action_button' => false,
                    'action_route' => '#',
                    'action_button_text' => '',
                    'action_button_class' => 'btn-secondary',
                    'prerequisite_message' => null,
                ];
                $tempTestDetails[$test->test_id] = $testItem;
            }

            $sortedTempTestDetails = collect($tempTestDetails)->sortBy('order')->all();
            $processedDetails = [];

            foreach ($sortedTempTestDetails as $testId => $testInfo) {
                if ($testInfo['status_internal'] === 'in_progress') {
                    $testInfo['can_start'] = true;
                } elseif ($testInfo['status_internal'] === 'not_started') {
                    if ($testInfo['order'] == 1) {
                        $testInfo['can_start'] = true;
                    } else {
                        $previousTestOrder = $testInfo['order'] - 1;
                        $previousTestProgress = null;
                        foreach($processedDetails as $processedItem){ 
                            if($processedItem['order'] == $previousTestOrder){
                                $previousTestProgress = $processedItem;
                                break;
                            }
                        }

                        if ($previousTestProgress && $previousTestProgress['status_internal'] === 'completed') {
                            $testInfo['can_start'] = true;
                        } elseif($previousTestProgress) {
                            $testInfo['prerequisite_message'] = 'Selesaikan "' . $previousTestProgress['name'] . '" dahulu.';
                        } else {
                             if($testInfo['order'] != 1) $testInfo['prerequisite_message'] = 'Tes prasyarat belum selesai atau tidak ditemukan.';
                        }
                    }
                }
                $this->prepareTestDisplayDetails($testInfo);
                $processedDetails[$testId] = $testInfo;
            }

            $this->testsList = array_values($processedDetails);
            
             if ($this->totalTestsCount > 0) {
                 $this->completedTestsCount = collect($this->testsList)->where('status_internal', 'completed')->count();
                 $this->completionPercentage = ($this->completedTestsCount / $this->totalTestsCount) * 100;
                 $this->progressMessage = "Anda telah menyelesaikan {$this->completedTestsCount} dari {$this->totalTestsCount} tes yang dibutuhkan.";
            } else {
                 $this->completionPercentage = 0;
                 if (Test::count() === 0) { 
                    $this->progressMessage = "Belum ada tes yang dikonfigurasi dalam sistem.";
                 } else { 
                    $this->progressMessage = "Tidak ada tes valid yang dapat ditampilkan saat ini.";
                 }
            }

        } else {
            $this->progressMessage = "Belum ada tes yang tersedia saat ini.";
        }
    }

    private function getTestIcon(string $testType): string
    {
        if (!in_array($testType, ['programming', 'riasec', 'mbti'])) {
            Log::warning("Dashboard.php - getTestIcon: Received unknown test_type '{$testType}'. Defaulting icon.");
            return 'assignment';
        }
        switch ($testType) {
            case 'programming':
                return 'code';
            case 'riasec':
                return 'psychology';
            case 'mbti':
                return 'theater_comedy';
            default:
                return 'assignment';
        }
    }

    private function prepareTestDisplayDetails(array &$testInfo): void
    {
        $routeBase = 'candidate.assessment.';
        $defaultListRouteName = $routeBase . 'test'; 

        $generatedUrlForAssessmentList = '#';
        try {
            if (route()->has($defaultListRouteName)) { 
                $generatedUrlForAssessmentList = route($defaultListRouteName); 
            } else {
                Log::critical("Dashboard.php - prepareTestDisplayDetails: DEFAULT ASSESSMENT LIST ROUTE '{$defaultListRouteName}' NOT FOUND. Check web.php routes.");
            }
        } catch (\Throwable $e) {
            Log::critical("Dashboard.php - prepareTestDisplayDetails: Exception generating URL for default assessment list route '{$defaultListRouteName}': " . $e->getMessage());
        }
        
        $testInfo['action_route'] = $generatedUrlForAssessmentList; 

        switch ($testInfo['status_internal']) {
            case 'completed':
                $testInfo['show_action_button'] = false;
                $statusText = "Selesai";
                if ($testInfo['test_type'] === 'programming' && !is_null($testInfo['score'])) {
                    $statusText .= " | Skor: " . number_format($testInfo['score'], 0);
                } elseif ($testInfo['test_type'] === 'riasec' && !is_null($testInfo['result_summary'])) {
                    $statusText .= " | Hasil: " . $testInfo['result_summary'];
                } elseif ($testInfo['test_type'] === 'mbti' && !is_null($testInfo['result_summary'])) {
                    $statusText .= " | Tipe: " . $testInfo['result_summary'];
                }
                $testInfo['display_status_text'] = $statusText;
                break;

            case 'in_progress':
                if ($testInfo['can_start']) {
                    $testInfo['show_action_button'] = true;
                    $testInfo['action_button_text'] = "Lanjutkan Tes";
                    $testInfo['action_button_class'] = 'btn-warning text-white';
                    $testInfo['display_status_text'] = "Sedang Dikerjakan";
                } else {
                    $testInfo['show_action_button'] = false;
                    $testInfo['display_status_text'] = "Terkunci (Error: Sedang dikerjakan tapi tidak bisa dimulai)";
                }
                break;

            case 'not_started':
                if ($testInfo['can_start']) {
                    $testInfo['show_action_button'] = true;
                    $testInfo['action_button_text'] = "Kerjakan Tes";
                    $testInfo['action_button_class'] = 'btn-info text-white';
                    $testInfo['display_status_text'] = "Belum Dikerjakan";
                } else {
                    $testInfo['show_action_button'] = false;
                    $testInfo['display_status_text'] = "Terkunci";
                }
                break;
            default:
                $testInfo['show_action_button'] = false;
                $testInfo['display_status_text'] = "Status Tidak Valid ({$testInfo['status_internal']})";
                Log::warning("Dashboard.php - prepareTestDisplayDetails: Unknown status_internal '{$testInfo['status_internal']}' for test '{$testInfo['name']}'.");
                break;
        }
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}