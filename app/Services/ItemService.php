<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemLocation;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ItemService
{
    protected UnitService $unitService;

    public function __construct(UnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    /**
     * Validation rules (keys match Livewire properties)
     */
    public function rules(?int $itemId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('items')->ignore($itemId)],
            'barcode' => ['required', 'string', 'max:100'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'supplierId' => ['nullable', 'integer', 'exists:suppliers,id'],
            'locationId' =>['required','integer','exists:locations,id'],
            'initialStock' => ['required', 'integer', 'min:0'],
            'reorderLevel' => ['required', 'integer', 'min:0'],
            'smallestUnit' => ['required', 'string', 'max:255'],
            'buyingPrice' => ['required', 'numeric', 'min:0'],
            'sellingPrice' => ['required', 'numeric', 'gt:buyingPrice'],
            'buyingPriceIncludesTax' => ['boolean'],
            'sellingPriceIncludesTax' => ['boolean'],
            'isSaleItem' => ['boolean'],
            'isActive' => ['boolean'],
        ];
    }

    /**
     * Save or update item + smallest unit inside a transaction.
     * Throws ValidationException if category missing or other validation fails.
     */
    public function save(?int $itemId, array $data): Item
    {
        return DB::transaction(function () use ($itemId, $data) {

            // Ensure category exists (double-check)
            $category = Category::find($data['categoryId'] ?? null);
            if (!$category) {
                // The component should redirect, but in case service is used elsewhere throw a validation exception
                throw ValidationException::withMessages([
                    'categoryId' => 'Category does not exist. Please create a category first.',
                ]);
            }

            $item = Item::updateOrCreate(
                ['id' => $itemId],
                [
                    'name' => $data['name'],
                    'barcode' => $data['barcode'],
                    'category_id' => $data['categoryId'],
                    'supplier_id' => $data['supplierId'] ?? null,
                    'initial_stock' => $data['initialStock'],
                    // keep current_stock untouched on update; on create set to initialStock
                    'current_stock' => $itemId ? Item::find($itemId)->current_stock ?? $data['initialStock'] : $data['initialStock'],
                    'reorder_level' => $data['reorderLevel'],
                    'is_sale_item' => $data['isSaleItem'] ?? true,
                    'is_active' => $data['isActive'] ?? true,
                ]
            );

            // create/update smallest unit for this item
            $this->unitService->createSmallest([
                'name' => $data['smallestUnit'],
                'selectedItemId' => $item->id,
                'buyingPrice' => $data['buyingPrice'],
                'sellingPrice' => $data['sellingPrice'],
                'buyingPriceIncludesTax' => $data['buyingPriceIncludesTax'] ?? false,
                'sellingPriceIncludesTax' => $data['sellingPriceIncludesTax'] ?? false,
                'isActive' => $data['isActive'] ?? true,
            ]);

            $itemLocation = new ItemLocation();
            $itemLocation->item_id = $item->id;
            $itemLocation->location_id = $data['locationId'];
            $itemLocation->quantity = $data['initialStock'];
            $itemLocation->save();

            return $item;
        });
    }

    public function delete(int $id): void
    {
        // remove units first to keep DB consistent

        // delete units for this item then item
        DB::transaction(function () use ($id) {
            Item::where('id', $id)->delete();
            ItemLocation::where('item_id', $id)->delete();
        });
    }

    public function bulkDelete(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            Unit::whereIn('item_id', $ids)->delete();
            Item::whereIn('id', $ids)->delete();
            ItemLocation::whereIn('item_id', $ids)->delete();
        });
    }

    public function bulkToggleActive(array $ids, bool $status): void
    {
        Item::whereIn('id', $ids)->update(['is_active' => $status]);
    }

    public function bulkAssignCategory(array $ids, int $categoryId): void
    {
        Item::whereIn('id', $ids)->update(['category_id' => $categoryId]);
    }

    public function bulkAssignSupplier(array $ids, int $supplierId): void
    {
        Item::whereIn('id', $ids)->update(['supplier_id' => $supplierId]);
    }
    public function bulkAssignLocation(array $ids, int $locationId): void
    {
        ItemLocation::whereIn('item_id', $ids)->delete();
        ItemLocation::whereIn('item_id', $ids)->create(['item_id' => $ids, 'location_id' => $locationId]);
    }

    public function getById(int $id): Item
    {
        return Item::with(['units' => fn($q) => $q->where('is_smallest_unit', true)])->findOrFail($id);
    }
}
