<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Validation\Rule;

class DepartmentService
{
    /**
     * Create a new class instance.
     */
    public function rules(?int $departmentId = null)
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($departmentId)],
            'description' => ['nullable', 'string'],
        ];
    }

    public function getById(int $id): Department
    {
        return Department::findOrFail($id);
    }

    public function getAll():array{
        return Department::all()->toArray();
    }

    public function getCategories(int $departmentId): array
    {
        $department = $this->getById($departmentId);
        return $department->categories->toArray();
    }

    public function getItems(int $departmentId): array
    {
        $department = $this->getById($departmentId);
        return $department->items()->get()->toArray();
    }

    public function createOrUpdate(array $data, ?int $id = null): Department
    {
        return Department::updateOrCreate(
            ['id' => $id],
            $data
        );
    }

    public function delete(int $id): bool
    {
        $department = $this->getById($id);
        return $department->delete();
    }

    public function bulkDelete(array $ids): bool
    {
        $departments = Department::whereIn('id', $ids)->get();
        foreach ($departments as $department) {
            $department->delete();
        }
        return true;
    }
}
