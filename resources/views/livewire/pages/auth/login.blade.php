<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
};
?>

<div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div class="card shadow-sm p-4" style="max-width: 40rem; width: 100%;">
        <!-- Title -->
        <div class="text-center mb-4">
            <i class="bi bi-person-circle text-primary" style="font-size: 4rem"></i>
            <h2 class="fw-bold mt-2">Inventory Manager</h2>
            <p class="text-muted mb-0">Log in to your account</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="alert alert-success small">
                {{ session('status') }}
            </div>
        @endif

        <!-- Login Form -->
        <form wire:submit="login">
            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input wire:model="form.email" type="email" class="form-control" id="email" name="email" required autofocus>
                @error('form.email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input wire:model="form.password" type="password" class="form-control" id="password" name="password" required>
                @error('form.password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="form-check mb-3">
                <input wire:model="form.remember" class="form-check-input" type="checkbox" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between align-items-center">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="small text-decoration-none">
                        Forgot your password?
                    </a>
                @endif

                <button type="submit" class="btn btn-primary">Log in</button>
            </div>
        </form>
    </div>
</div>
