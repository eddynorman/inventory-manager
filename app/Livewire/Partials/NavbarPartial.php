<?php

namespace App\Livewire\Partilas;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NavbarPartial extends Component
{
    public $user;

    public function mount()
    {
        $this->user = Auth::user();
    }


    public function render()
    {
        return view('livewire.partials.navbar-partial');
    }
}
