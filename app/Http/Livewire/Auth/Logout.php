<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;

class Logout extends Component
{

    public function destroy()
    {
        auth()->logout();

        return redirect('/login');
    }

    
    public function render()
    {
        return view('livewire.auth.logout');
    }
}
