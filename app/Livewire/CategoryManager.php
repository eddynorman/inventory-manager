<?php

namespace App\Livewire;

use App\Services\CategoryService;
use Livewire\Attributes\On;
use Livewire\Component;

class CategoryManager extends Component
{
    public ?int $categoryId = null;
    public string $name = '';
    public ?string $description = '';
    public $items = null;
    public $bulkIds = [];

    protected $listeners = [
        'bulkDeleteConfirmWithIds' => 'bulkDeleteConfirm'
    ];

    protected CategoryService $service;

    public function boot(CategoryService $service): void
    {
        $this->service = $service;
    }

    public function resetForm(): void
    {
        $this->reset(['categoryId', 'name', 'description']);
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('show-category-modal');
    }

    #[On('view')]
    public function view(int $id): void
    {
        $cat = $this->service->getById($id);
        $this->categoryId = $cat->id;
        $this->name = $cat->name;
        $this->description = (string)($cat->description ?? '');
        $this->items = $this->service->getItems($id);

        $this->dispatch('show-view-modal');
    }

    #[On('edit')]
    public function edit(int $id): void
    {
        $cat = $this->service->getById($id);
        $this->categoryId = $cat->id;
        $this->name = $cat->name;
        $this->description = (string)($cat->description ?? '');
        $this->dispatch('show-category-modal');
    }

    public function save(): void
    {
        $data = [
            'name' => $this->name,
            'description' => $this->description
        ];

        $this->service->createOrUpdate($data, $this->categoryId);

        $this->dispatch('hide-category-modal');
        $this->resetForm();
        session()->flash('success', 'Category saved.');
        $this->dispatch('refresh-table');
    }

    #[On('confirmDelete')]
    public function confirmDelete(int $id): void
    {
        $this->categoryId = $id;
        $this->dispatch('show-delete-modal');
    }

    public function delete(): void
    {
        if ($this->categoryId) {
            $this->service->delete($this->categoryId);
        }
        $this->dispatch('hide-delete-modal');
        $this->resetForm();
        session()->flash('success', 'Category deleted.');
        $this->dispatch('refresh-table');
    }

    public function bulkDeleteConfirm(array $ids): void
    {
        $this->bulkIds = $ids;

        if (empty($this->bulkIds)) {
            session()->flash('error', 'No categories selected.');
            return;
        }

        $this->dispatch('show-bulk-delete-modal');
    }

    public function bulkDelete(): void
    {
        if (!empty($this->bulkIds)) {
            $this->service->bulkDelete($this->bulkIds);
            session()->flash('success', 'Selected categories deleted.');
        }

        $this->dispatch('hide-bulk-delete-modal');
        $this->bulkIds = [];
        $this->dispatch('refresh-table');
    }

    public function render()
    {
        return view('livewire.category-manager')->layout('layouts.app');
    }
}
