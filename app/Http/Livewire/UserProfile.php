<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\JobPosition; 
use Livewire\Component;
use Illuminate\Validation\Rule;

class UserProfile extends Component
{
    public User $user;
    public $jobPositions; 

    protected function rules()
    {
        return [
            'user.name' => 'required',
            'user.email' => 'required|email|unique:users,email,' . $this->user->id,
            'user.phone' => 'nullable|max:15',
            'user.job_position' => [
                'nullable',
                Rule::exists('job_positions', 'name'), 
            ],
            'user.profile_summary' => 'nullable|string|max:1000',
        ];
    }

    public function mount()
    {
        $this->user = auth()->user();
        $this->jobPositions = JobPosition::orderBy('name')->get();
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function update()
    {
        $this->validate();

        if (empty($this->user->job_position)) {
            $this->user->job_position = null;
        }

        $this->user->save();
        
        session()->flash('status', 'Informasi profil berhasil diperbarui.');
        return redirect()->route('profile');
    }

    public function render()
    {
        return view('livewire.user-profile', [
            'pageTitle' => 'Profil Pengguna',
        ]);
    }
}