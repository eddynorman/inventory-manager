<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function rules(?int $categoryId = null): array
    {
        return [
            'departmentId' => ['required', 'integer', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
        ];
    }

    public function getById(int $id): Category
    {
        return Category::findOrFail($id);
    }

    public function getItems(int $categoryId): array
    {
        $category = $this->getById($categoryId);
        return $category->items()
                        ->get(['name', 'current_stock'])
                        ->toArray();
    }

    public function createOrUpdate(array $data, ?int $id = null): Category
    {
        $data['name'] = trim($data['name']);
        $data['department_id'] = $data['departmentId'];
        return Category::updateOrCreate(
            ['id' => $id],
            $data
        );
    }

    public function delete(int $id): void
    {
        $category = $this->getById($id);
        $category->delete();
    }

    public function bulkDelete(array $ids): void
    {
        Category::whereIn('id', $ids)->delete();
    }
}
