<?php

namespace App\Livewire;

use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public ?int $categoryId = null;
    public string $name = '';
    public ?string $description = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
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
    }

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
    }

    public function render()
    {
        $query = Category::query()
            ->when($this->search !== '', function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            })
            ->orderBy('name');

        return view('livewire.category-manager', [
            'categories' => $query->paginate($this->perPage),
        ])->with('title', 'Categories');
    }
}


