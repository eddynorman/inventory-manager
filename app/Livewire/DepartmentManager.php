<?php

namespace App\Livewire;

use App\Services\DepartmentService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class DepartmentManager extends Component
{
    public ?int $departmentId = null;
    public string $name = '';
    public ?string $description = '';
    public ?array $items = null;
    public ?array $categories = null;
    public array $bulkIds = [];

    protected DepartmentService $service;

    public bool $showDepartmentModal = false;
    public bool $showDeleteModal = false;
    public bool $showBulkDeleteModal = false;
    public bool $showViewModal = false;

    protected $listeners = [
        'bulkDelete.Departments' => 'bulkDeleteConfirm',
        'bulkDeleteConfirmWithIds' => 'bulkDeleteConfirm',
        'edit' => 'edit',
        'create' => 'create',
        'confirmDelete' => 'confirmDelete',
        'view' => 'view',
    ];

    public function boot(DepartmentService $service): void
    {
        $this->service = $service;
        //close flash messages after 3s
        $this->dispatch('flash');
    }

    public function resetForm(): void
    {
        $this->reset(['departmentId', 'name', 'description']);
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showDepartmentModal = true;
    }
    public function view(int $id): void
    {
        $dept = $this->service->getById($id);
        $this->departmentId = $dept->id;
        $this->name = $dept->name;
        $this->description = (string)($dept->description ?? '');
        $this->categories = $this->service->getCategories($this->departmentId);
        $this->items = $this->service->getItems($this->departmentId);

        $this->showViewModal = true;
    }

    public function edit(int $id): void
    {
        $dept = $this->service->getById($id);
        $this->departmentId = $dept->id;
        $this->name = $dept->name;
        $this->description = (string)($dept->description ?? '');
        $this->showDepartmentModal = true;
    }

    public function save(): void
    {
        try {
            $validated = $this->validate($this->service->rules($this->departmentId));
            $this->service->createOrUpdate($validated, $this->departmentId);
            $this->showDepartmentModal = false;
            $this->resetForm();
            session()->flash('success', 'Department saved.');
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
        $this->departmentId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->departmentId) {
            $this->service->delete($this->departmentId);
        }
        $this->showDeleteModal = false;
        $this->resetForm();
        session()->flash('success', 'Department deleted.');
        $this->dispatch('flash');
        $this->dispatch('refresh-table');
    }

    public function bulkDeleteConfirm(array $ids): void
    {
        $this->bulkIds = $ids;
        if (count($this->bulkIds) === 0) {
            session()->flash('error', 'No departments selected.');
            $this->dispatch('flash');
            return;
        }

        $this->showBulkDeleteModal = true;
    }

    public function bulkDelete(): void
    {
        if (!empty($this->bulkIds)) {
            $this->service->bulkDelete($this->bulkIds);
            session()->flash('success', 'Selected departments deleted.');
            $this->dispatch('flash');
        }

        $this->showBulkDeleteModal = false;
        $this->bulkIds = [];
        $this->dispatch('refresh-table');
    }

    public function render()
    {
        return view('livewire.department-manager');
    }
}
