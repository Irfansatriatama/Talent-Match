<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\JobPosition;
use App\Models\CandidateFile;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserProfile extends Component
{
    use WithFileUploads;

    public User $user;
    public $jobPositions;

    public $cv;
    public $portfolios = [];
    public $existingCv;
    public $existingPortfolios;

    protected function rules()
    {
        return [
            'user.name' => 'required',
            'user.email' => 'required|email|unique:users,email,' . $this->user->id,
            'user.phone' => 'nullable|max:15',
            'user.job_position_id' => 'nullable|exists:job_positions,id',
            'cv' => 'nullable|file|mimes:pdf|max:10240', 
            'portfolios.*' => 'nullable|file|mimes:pdf|max:10240', 
        ];
    }

    public function mount()
    {
        $this->user = auth()->user();
        $this->jobPositions = JobPosition::orderBy('name')->get();
        $this->loadExistingFiles();
    }

    public function loadExistingFiles()
    {
        $this->existingCv = $this->user->cv;
        $this->existingPortfolios = $this->user->portfolios;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function update()
    {
        $this->validate();

        $this->user->save();

        if ($this->cv) {
            $this->handleCvUpload();
        }

        if (!empty($this->portfolios)) {
            $this->handlePortfolioUploads();
        }
        
        session()->flash('status', 'Informasi profil berhasil diperbarui.');
        return redirect()->route('profile');
    }

    private function handleCvUpload()
    {
        if ($this->existingCv) {
            Storage::delete($this->existingCv->file_path);
            $this->existingCv->delete();
        }

        $path = $this->cv->store('cvs/' . $this->user->id, 'public');
        
        CandidateFile::create([
            'user_id' => $this->user->id,
            'file_path' => $path,
            'original_name' => $this->cv->getClientOriginalName(),
            'file_type' => 'cv',
        ]);
    }

    private function handlePortfolioUploads()
    {
        foreach ($this->portfolios as $portfolio) {
            $path = $portfolio->store('portfolios/' . $this->user->id, 'public');
            
            CandidateFile::create([
                'user_id' => $this->user->id,
                'file_path' => $path,
                'original_name' => $portfolio->getClientOriginalName(),
                'file_type' => 'portfolio',
            ]);
        }
    }

    public function deleteFile($fileId)
    {
        $file = CandidateFile::where('user_id', $this->user->id)
                            ->where('id', $fileId)
                            ->first();
        
        if ($file) {
            Storage::delete($file->file_path);
            $file->delete();
            $this->loadExistingFiles();
            session()->flash('status', 'File berhasil dihapus.');
        }
    }

    public function render()
    {
        return view('livewire.user-profile', [
            'pageTitle' => 'Profil Pengguna',
        ]);
    }
}