<?php
namespace App\Livewire;

use App\Models\Group;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    protected UserService $service;

    public function boot(UserService $service): void
    {
        $this->service = $service;
        $this->groups = Group::orderBy('name')->get()->toArray();
    }

    // Form State
    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $type = 'staff';
    public bool $isActive = true;
    public array $selectedGroups = [];
    public array $groups = [];

    // Bulk selection
    public array $selectedUsers = [];

    // Modals
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showBulkDeleteModal = false;

    public function refreshTable(): void
    {
        $this->dispatch('pg:eventRefresh-users-table');
    }
    public function resetForm(): void
    {
        $this->reset(['userId','name','email','password','type','isActive','selectedGroups','groups']);
        $this->type = 'staff';
        $this->isActive = true;
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->groups = Group::orderBy('name')->get()->toArray();
        $this->showModal = true;
    }

    #[On('edit')]
    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->type = $user->type;
        $this->isActive = $user->is_active;
        $this->password = '';
        $this->selectedGroups = $user
            ->groups
            ->pluck('id')
            ->toArray();
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate($this->service->rules($this->userId));
        $this->service->save($this->userId, $data);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'User saved successfully.');
        $this->dispatch('flash');
        $this->refreshTable();
    }
    #[On('confirmDelete')]
    public function confirmDelete(int $id): void
    {
        $this->userId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->userId) {
            $this->service->delete($this->userId);
        }
        $this->showDeleteModal = false;
        $this->resetForm();
        session()->flash('success', 'User deleted successfully.');
        $this->dispatch('flash');
        $this->refreshTable();
    }

    public function bulkDelete(): void
    {
        $this->service->bulkDelete($this->selectedUsers);
        $this->showBulkDeleteModal = false;
        $this->selectedUsers = [];
        session()->flash('success', 'Selected users deleted successfully.');
        $this->dispatch('flash');
        $this->refreshTable();
    }


    public function render()
    {
        return view('livewire.user-manager', [
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
