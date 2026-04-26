<?php

namespace App\Services;

use App\Enums\StockBatchType;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemLocation;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ItemService
{
    protected UnitService $unitService;
    protected StockBatchService $batchService;
    protected StockMovementService $movementService;

    public function __construct(UnitService $unitService, StockBatchService $batchService, StockMovementService $movementService)
    {
        $this->unitService = $unitService;
        $this->batchService = $batchService;
        $this->movementService = $movementService;
    }

    /**
     * Validation rules (keys match Livewire properties)
     */
    public function rules(?int $itemId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('items')->ignore($itemId)],
            'barcode' => ['nullable', 'string', 'max:100'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'supplierId' => ['nullable', 'integer', 'exists:suppliers,id'],
            'locationId' => ['nullable','integer','exists:locations,id'],
            'newLocationId' => ['required','integer','exists:locations,id'],
            'initialStock' => ['required', 'integer', 'min:0'],
            'reorderLevel' => ['required', 'integer', 'min:0'],
            'smallestUnitId' => ['nullable','integer','exists:units,id'],
            'smallestUnit' => ['required', 'string', 'max:255'],
            'buyingPrice' => ['required', 'numeric', 'min:0'],
            'sellingPrice' => ['required', 'numeric', 'gt:buyingPrice'],
            'buyingPriceIncludesTax' => ['boolean'],
            'sellingPriceIncludesTax' => ['boolean'],
            'isSaleItem' => ['required','boolean'],
            'isStockItem' => ['required','boolean'],
            'isAutoTracked' => ['required','boolean'],
            'isActive' => ['required','boolean'],
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
            //ensure non stock items are not autotracked

            if($data['isStockItem'] == false){
                $data['isAutoTracked'] = false;
            }

            $item = Item::updateOrCreate(
                ['id' => $itemId],
                [
                    'name' => $data['name'],
                    'barcode' => $data['barcode']?? null,
                    'category_id' => $data['categoryId'],
                    'supplier_id' => $data['supplierId'] ?? null,
                    'initial_stock' => $data['initialStock'],
                    // keep current_stock untouched on update; on create set to 0 as stock will be handled by stock batch
                    'current_stock' => $itemId ? Item::find($itemId)->current_stock ?? 0 : 0,
                    'reorder_level' => $data['reorderLevel'],
                    'is_sale_item' => $data['isSaleItem'] ?? true,
                    'is_stock_item' => $data['isStockItem'] ?? true,
                    'is_auto_tracked' => $data['isAutoTracked'] ?? true,
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
            ],$data['smallestUnitId']);

           if (empty($data['locationId'])) {
                // create new pivot record
                $item->locations()->attach($data['newLocationId'], [
                    'quantity' => 0,
                ]);
            } else {
                // update existing pivot record
                $item->locations()->updateExistingPivot($data['locationId'], [
                    'quantity' => $data['initialStock'],
                    'location_id' => $data['newLocationId']
                ]);

            }
            if($itemId == null){
                $this->batchService->createBatch($item->id,$data['newLocationId'],$data['initialStock'],$data['buyingPrice'],'new_item',$item->id);
                $this->movementService->createMovement($item->id,$data['newLocationId'],null,$data['initialStock'],'new_item',StockBatchType::NEW_ITEM,$item->id,Auth::id());
            }
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
        return Item::with('units')->findOrFail($id);
    }

    public function getUnits(int $itemId): array
    {
        return Unit::where('item_id', $itemId)->get()->toArray();
    }
    public function getSmallestUnit(int $itemId): Unit
    {
        return Unit::where('item_id', $itemId)->where('is_smallest_unit', true)->first();
    }

    public function search(string $query): array
    {
        return Item::where('name', 'like', '%'.$query.'%')->get()->toArray();
    }

    /**
     * Increase stock for multiple items.
     *
     * @param array<int, array{item_id:int, quantity:float|int}> $items
     * @return void
     */
    public function increaseStock(array $items): void
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $data) {

                $item = Item::lockForUpdate()->findOrFail($data['item_id']);

                $item->increment('current_stock', $data['quantity']);
            }
        });
    }

    /**
     * Decrease stock for multiple items.
     *
     * Ensures stock never goes below zero.
     *
     * @param array<int, array{item_id:int, quantity:float|int}> $items
     * @throws ValidationException
     * @return void
     */
    public function decreaseStock(array $items): void
    {
        DB::transaction(function () use ($items) {

            foreach ($items as $data) {

                $item = Item::lockForUpdate()->findOrFail($data['item_id']);

                if ($data['quantity'] > $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for {$item->name}."
                    ]);
                }

                $item->decrement('quantity', $data['quantity']);
            }
        });
    }
}
