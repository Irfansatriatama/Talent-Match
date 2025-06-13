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
            'user.profile_summary' => 'nullable|string|max:10000',
            'user.job_position_id' => 'nullable|exists:job_positions,id',
        ];
    }

    public function mount()
    {
        $this->user = auth()->user();
        $this->jobPositions = JobPosition::orderBy('name')->get();
        $this->jobPositions = JobPosition::all();
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function update()
    {
        $this->validate();

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