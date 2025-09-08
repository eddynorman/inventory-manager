<?php

namespace App\Livewire;

use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class CategoryManager extends Component
{
    public ?int $categoryId = null;
    public string $name = '';
    public ?string $description = '';
    public $items = null;

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

    // Listen for the 'view' event from the table
    #[On('view')]
    public function view(int $id): void
    {
        $cat = Category::findOrFail($id);

        $items = $cat->items()
            ->get(['name', 'current_stock'])
            ->toArray();
        $this->categoryId = $cat->id;
        $this->name = $cat->name;
        $this->items = $items;
        $this->description = (string)($cat->description ?? '');
        $this->dispatch('show-view-modal');
    }

    // Listen for the 'edit' event from the table
    #[On('edit')]
    public function edit(int $id): void
    {
        $cat = Category::findOrFail($id);
        $this->categoryId = $cat->id;
        $this->name = $cat->name;
        $this->description = (string)($cat->description ?? '');
        $this->dispatch('show-category-modal');
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required','string','max:255', Rule::unique(Category::class, 'name')->ignore($this->categoryId)],
            'description' => ['nullable','string'],
        ]);

        Category::updateOrCreate(
            ['id' => $this->categoryId],
            $data
        );

        $this->dispatch('hide-category-modal');
        $this->resetForm();
        session()->flash('success', 'Category saved.');

        // Dispatch an event to refresh the PowerGrid table
        $this->dispatch('refresh-table');
    }

    // Listen for the 'confirmDelete' event from the table
    #[On('confirmDelete')]
    public function confirmDelete(int $id): void
    {
        $this->categoryId = $id;
        $this->dispatch('show-delete-modal');
    }

    public function delete(): void
    {
        if ($this->categoryId) {
            Category::where('id', $this->categoryId)->delete();
        }
        $this->dispatch('hide-delete-modal');
        $this->resetForm();
        session()->flash('success', 'Category deleted.');

        // Dispatch an event to refresh the PowerGrid table
        $this->dispatch('refresh-table');
    }

    public function render()
    {
        
        return view('livewire.category-manager')->layout('layouts.app');
    }
}
