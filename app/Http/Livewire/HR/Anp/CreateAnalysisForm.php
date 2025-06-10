<?php

namespace App\Http\Livewire\HR\Anp;

use App\Models\AnpAnalysis;
use App\Models\JobPosition;
use App\Models\User;
use App\Models\AnpNetworkStructure;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateAnalysisForm extends Component
{
    public $name;
    public $description;
    public $job_position_id;
    public $selected_candidates = [];

    public $jobPositions = [];
    public $availableCandidates = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'job_position_id' => 'required|exists:job_positions,id',
        'selected_candidates' => 'required|array|min:2',
        'selected_candidates.*' => 'exists:users,id',
        'description' => 'nullable|string',
    ];

    protected $messages = [
        'name.required' => 'Nama analisis wajib diisi.',
        'job_position_id.required' => 'Posisi jabatan wajib dipilih.',
        'selected_candidates.required' => 'Harap pilih setidaknya dua kandidat untuk dibandingkan.',
        'selected_candidates.min' => 'Harap pilih setidaknya dua kandidat untuk dibandingkan.',
    ];

    public function mount()
    {
        $this->jobPositions = JobPosition::orderBy('name')->get();
        $this->availableCandidates = User::where('role', User::ROLE_CANDIDATE)->orderBy('name')->get();
    }

    public function saveAnalysis()
    {
        $this->validate();

        try {
            $defaultStructure = AnpNetworkStructure::first();

            if (!$defaultStructure) {
                session()->flash('error', 'Kritis: Tidak ditemukan struktur jaringan default di database. Harap jalankan `php artisan db:seed`.');
                return;
            }

            $analysis = AnpAnalysis::create([
                'name' => $this->name,
                'job_position_id' => $this->job_position_id,
                'anp_network_structure_id' => $defaultStructure->id,
                'hr_user_id' => Auth::id(),
                'status' => 'network_pending', 
                'description' => $this->description,
            ]);

            $analysis->candidates()->sync($this->selected_candidates);

            session()->put('current_anp_analysis_id', $analysis->id);

            session()->flash('message', 'Analisis ANP baru berhasil dibuat. Silakan lanjutkan ke definisi jaringan.');

            return redirect()->route('hr.anp.analysis.network.define', $analysis->id);

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat analisis ANP: Terjadi kesalahan pada server. ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.hr.anp.create-analysis-form');
    }
}