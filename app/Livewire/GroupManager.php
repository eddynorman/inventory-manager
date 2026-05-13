<?php

namespace App\Livewire;

use App\Models\Group;
use App\Models\Permission;
use App\Services\GroupService;
use Livewire\Component;

class GroupManager extends Component
{
    protected GroupService $service;

    public function boot(GroupService $service)
    {
        $this->service = $service;
    }

    public ?int $groupId = null;

    public string $name = '';

    public string $description = '';

    public array $selectedPermissions = [];

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public string $search = '';

    public function create(): void
    {
        $this->resetForm();

        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $group = Group::with('permissions')->findOrFail($id);

        $this->groupId = $group->id;

        $this->name = $group->name;

        $this->description = $group->description ?? '';

        $this->selectedPermissions = $group
            ->permissions
            ->pluck('id')
            ->toArray();

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate(
            $this->service->rules($this->groupId)
        );

        $this->service->save($this->groupId, $data);

        session()->flash('success', 'Group saved successfully.');

        $this->resetForm();

        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        $this->groupId = $id;

        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $this->service->delete($this->groupId);

        session()->flash('success', 'Group deleted successfully.');

        $this->resetForm();

        $this->showDeleteModal = false;
    }

    public function resetForm(): void
    {
        $this->reset([
            'groupId',
            'name',
            'description',
            'selectedPermissions',
        ]);

        $this->resetValidation();
    }

    public function toggleCategory(string $category): void
    {
        $ids = Permission::where('category', $category)
            ->pluck('id')
            ->toArray();

        $allSelected = collect($ids)
            ->every(fn($id) => in_array($id, $this->selectedPermissions));

        if ($allSelected) {

            $this->selectedPermissions = array_values(
                array_diff($this->selectedPermissions, $ids)
            );

            return;
        }

        $this->selectedPermissions = array_unique([
            ...$this->selectedPermissions,
            ...$ids
        ]);
    }

    public function render()
    {
        $permissions = Permission::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('category')
            ->get()
            ->groupBy('category');

        return view('livewire.group-manager', [

            'groups' => Group::withCount([
                'users',
                'permissions'
            ])->latest()->get(),

            'permissions' => $permissions,
        ]);
    }
}
