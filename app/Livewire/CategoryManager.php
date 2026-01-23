<?php

namespace App\Livewire;

use App\Services\CategoryService;
use App\Services\DepartmentService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

use function PHPUnit\Framework\isEmpty;

class CategoryManager extends Component
{
    public ?int $categoryId = null;
    public string $name = '';
    public ?string $description = '';
    public ?array $items = null;
    public array $bulkIds = [];
    public ?array $departments = null;
    public ?int $departmentId = null;
    public string $department = '';

    public bool $showCategoryModal = false;
    public bool $showDeleteModal = false;
    public bool $showBulkDeleteModal = false;
    public bool $showViewModal = false;

    protected $listeners = [
        'bulkDelete.Categories' => 'bulkDeleteConfirm',
        'bulkDeleteConfirmWithIds' => 'bulkDeleteConfirm',
        'edit' => 'edit',
        'create' => 'create',
        'confirmDelete' => 'confirmDelete',
        'view' => 'view',
    ];

    protected CategoryService $service;
    protected DepartmentService $dptService;

    public function boot(CategoryService $service): void
    {
        $this->dptService = new DepartmentService();
        $this->service = $service;
        $this->departments = $this->dptService->getAll();
    }

    public function resetForm(): void
    {
        $this->reset(['categoryId', 'departmentId','name', 'description']);
        $this->resetValidation();
    }

    public function create()
    {
        if (count($this->departments) === 0) {
            // redirect to departments page and open modal there (controller/view should handle openModal param)
            session()->flash('error', 'You must create a department before adding categories.');
            return redirect()->route('departments', ['showDepartmentModal' => true]);
        }
        $this->resetForm();
        $this->showCategoryModal = true;
    }

    public function view(int $id): void
    {
        $cat = $this->service->getById($id);
        $this->categoryId = $cat->id;
        $this->name = $cat->name;
        $this->department = $cat->department()->get('name');
        $this->description = (string)($cat->description ?? '');
        $this->items = $this->service->getItems($id);

        $this->showViewModal = true;
    }

    public function edit(int $id): void
    {
        $cat = $this->service->getById($id);
        $this->categoryId = $cat->id;
        $this->departmentId = $cat->department_id;
        $this->name = $cat->name;
        $this->description = (string)($cat->description ?? '');
        $this->showCategoryModal = true;
    }

    public function save(): void
    {
        try {
            $validated = $this->validate($this->service->rules($this->categoryId));
            $this->service->createOrUpdate($validated, $this->categoryId);
            $this->showCategoryModal = false;
            $this->resetForm();
            session()->flash('success', 'Category saved.');
            $this->dispatch('flash');
            $this->dispatch('refresh-table');
        } catch (ValidationException $ve) {
            session()->flash('error', $ve->getMessage());
            $this->dispatch('flash');
            // bubble validation errors to UI
            throw $ve;
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->categoryId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->categoryId) {
            $this->service->delete($this->categoryId);
        }
        $this->dispatch('hide-delete-modal');
        $this->resetForm();
        session()->flash('success', 'Category deleted.');
        $this->dispatch('flash');
        $this->dispatch('refresh-table');
    }

    public function bulkDeleteConfirm(array $ids): void
    {
        $this->bulkIds = $ids;
        if (count($this->bulkIds) === 0) {
            session()->flash('error', 'No categories selected.');
            $this->dispatch('flash');
            return;
        }

        $this->showBulkDeleteModal = true;
    }

    public function bulkDelete(): void
    {
        if (!empty($this->bulkIds)) {
            $this->service->bulkDelete($this->bulkIds);
            session()->flash('success', 'Selected categories deleted.');
            $this->dispatch('flash');
        }

        $this->showBulkDeleteModal = false;
        $this->bulkIds = [];
        $this->dispatch('refresh-table');
    }

    public function render()
    {
        return view('livewire.category-manager');
    }
}
