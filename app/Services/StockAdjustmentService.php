<?php

namespace App\Services;

use App\Models\ItemLocation;
use App\Models\Location;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    private ItemService $itemService;
    /**
     * Create a new class instance.
     */
    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function rules(){
        return[
            'location_id' => ['required','integer','exists:locations,id'],
            'description' => ['nullable','string'],
            'adjustment_items' => ['required','array', 'min:1'],
            'adjustment_items.*.item_id' => ['required','integer','exists:items,id'],
            'adjustment_items.*.adjustment_qty' => ['required','numeric'],
            'adjustment_items.*.quantity' => ['required','numeric'],
            'adjustment_items.*.reason' => ['required','string'],

        ];
    }

    public function getById(int $id){
        return StockAdjustment::with([
            'location:id,name',
            'createdBy:id,name',
            'items' => function ($q) {
                $q->select(
                    'id',
                    'stock_adjustment_id',
                    'item_id',
                    'current_stock',
                    'quantity',
                    'new_stock',
                    'reason'
                )->with('item:id,name');
            }
        ])->findOrFail($id);
    }

    public function loadLocations(){
        return Location::all('id','name');
    }

    public function save(array $data)
    {
        DB::transaction(function () use ($data) {
            //dd($data);
            $adjustment = StockAdjustment::create([
                'location_id' => $data['location_id'],
                'description' => $data['description'] ?? null,
                'user_id' => Auth::id(),
            ]);

            foreach ($data['adjustment_items'] as $item) {

                // 1. Get current stock
                $locationItem = ItemLocation::where([
                    'item_id' => $item['item_id'],
                    'location_id' => $data['location_id']
                ])->firstOrFail();

                $currentStock = $locationItem->quantity;
                $adjustmentQty = $item['adjustment_qty'];
                $newStock = $currentStock + $adjustmentQty;

                // 🚨 Optional safety
                if ($newStock < 0) {
                    throw new \Exception("Stock cannot go negative");
                }

                // 2. Save adjustment item
                $adjustmentItem = $adjustment->items()->create([
                    'item_id' => $item['item_id'],
                    'current_stock' => $currentStock,
                    'quantity' => $adjustmentQty,
                    'new_stock' => $newStock,
                    'reason' => $item['reason'],
                ]);

                // 3. Update location stock
                $locationItem->quantity = $adjustmentItem->new_stock;
                $locationItem->save();

                // 4. Update General stock
                $item['quantity'] = $adjustmentQty;
                $this->itemService->increaseStock([$item]);
            }
        });
    }
}
