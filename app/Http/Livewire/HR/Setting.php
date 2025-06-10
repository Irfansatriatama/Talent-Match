<?php

namespace App\Http\Livewire\HR;

use App\Models\JobPosition;
use Livewire\Component;
use Livewire\WithPagination;

class Setting extends Component
{
    use WithPagination;

    public $jobPositionId;
    public $name;
    public $description;
    public $ideal_riasec_profile; 
    public $ideal_mbti_profile;   

    public bool $isModalOpen = false;
    public bool $isEditMode = false;
    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'ideal_riasec_profile' => 'nullable|string',
        'ideal_mbti_profile' => 'nullable|string',  
    ];

    public function render()
    {
        return view('livewire.hr.setting', [
            'jobPositions' => JobPosition::paginate(10),
            'pageTitle' => 'Pengaturan Posisi Jabatan'
        ]);
    }
    
    public function create()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $jobPosition = JobPosition::findOrFail($id);
        $this->jobPositionId = $id;
        $this->name = $jobPosition->name;
        $this->description = $jobPosition->description;
        $this->ideal_riasec_profile = is_array($jobPosition->ideal_riasec_profile) ? implode(',', $jobPosition->ideal_riasec_profile) : '';
        $this->ideal_mbti_profile = is_array($jobPosition->ideal_mbti_profile) ? implode(',', $jobPosition->ideal_mbti_profile) : '';

        $this->isEditMode = true;
        $this->isModalOpen = true;
    }

    public function store()
    {
        $this->validate();

        $riasecArray = $this->ideal_riasec_profile ? array_map('trim', explode(',', strtoupper($this->ideal_riasec_profile))) : null;
        $mbtiArray = $this->ideal_mbti_profile ? array_map('trim', explode(',', strtoupper($this->ideal_mbti_profile))) : null;

        JobPosition::updateOrCreate(['id' => $this->jobPositionId], [
            'name' => $this->name,
            'description' => $this->description,
            'ideal_riasec_profile' => $riasecArray,
            'ideal_mbti_profile' => $mbtiArray,
        ]);

        session()->flash('message', $this->jobPositionId ? 'Posisi Jabatan Berhasil Diperbarui.' : 'Posisi Jabatan Berhasil Ditambahkan.');
        
        $this->closeModal();
    }
    
    public function delete($id)
    {
        try {
            JobPosition::find($id)->delete();
            session()->flash('message', 'Posisi Jabatan Berhasil Dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            session()->flash('error', 'Gagal menghapus. Posisi ini mungkin sedang digunakan dalam sebuah analisis.');
        }
    }
    
    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->jobPositionId = null;
        $this->name = '';
        $this->description = '';
        $this->ideal_riasec_profile = '';
        $this->ideal_mbti_profile = '';
    }
}