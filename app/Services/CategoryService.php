<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Validation\ValidationException;

class CategoryService
{
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
        // Validate unique name
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Category::updateOrCreate(
            ['id' => $id],
            $validator->validated()
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
