<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header">Profile Information</div>
                <div class="card-body">
                    @if(auth()->user()->hasAnyRole(['super','admin']))
                        <livewire:profile.update-profile-information-form />
                    @else
                        <div class="mb-2"><strong>Name:</strong> {{ auth()->user()->name }}</div>
                        <div class="mb-2"><strong>Email:</strong> {{ auth()->user()->email }}</div>
                        <div class="text-muted small">Only administrators can edit profile information.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header">Update Password</div>
                <div class="card-body">
                    <livewire:profile.update-password-form />
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-danger shadow-sm">
                <div class="card-header bg-danger text-white">Danger Zone</div>
                <div class="card-body">
                    @if(auth()->user()->hasAnyRole(['super','admin']))
                        <livewire:profile.delete-user-form />
                    @else
                        <div class="text-muted small">Only administrators can delete accounts.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
