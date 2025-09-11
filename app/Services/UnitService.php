<?php

namespace App\Services;

use App\Models\Unit;
use Illuminate\Validation\Rule;

class UnitService
{
    /**
     * Validation rules for creating/updating a unit
     */
    public function rules(?int $unitId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Unit::class, 'name')->ignore($unitId)],
            'buyingPrice' => ['required', 'numeric', 'min:0'],
            'sellingPrice' => ['nullable', 'numeric', 'min:0'],
            'smallestUnitsNumber' => ['required', 'integer', 'min:1'],
            'buyingPriceIncludesTax' => ['boolean'],
            'sellingPriceIncludesTax' => ['boolean'],
            'isActive' => ['boolean'],
            'selectedItemId' => ['required', 'exists:items,id'],
        ];
    }

    /**
     * Save or update a unit
     */
    public function save(?int $id, array $data): Unit
    {
        return Unit::updateOrCreate(
            ['id' => $id],
            [
                'name' => $data['name'],
                'buying_price' => $data['buyingPrice'],
                'selling_price' => $data['sellingPrice'] ?? null,
                'smallest_units_number' => $data['smallestUnitsNumber'],
                'buying_price_includes_tax' => $data['buyingPriceIncludesTax'],
                'selling_price_includes_tax' => $data['sellingPriceIncludesTax'],
                'is_active' => $data['isActive'],
                'item_id' => $data['selectedItemId'],
            ]
        );
    }

    /**
     * Create a smallest unit for an item
     * This sets is_smallest_unit = true and smallest_units_number = 1
     */
    public function createSmallest(array $data): Unit
    {
        $data['isSmallestUnit'] = true;
        $data['smallestUnitsNumber'] = 1;

        return $this->save(null, $data);
    }

    /**
     * Delete a unit by ID
     */
    public function delete(int $id): void
    {
        Unit::where('id', $id)->delete();
    }

    /**
     * Bulk delete units by IDs
     */
    public function bulkDelete(array $ids): void
    {
        Unit::whereIn('id', $ids)->delete();
    }

    /**
     * Retrieve a unit by ID with its item
     */
    public function getById(int $id): Unit
    {
        return Unit::with('item')->findOrFail($id);
    }
}
