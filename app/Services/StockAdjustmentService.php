<?php

namespace App\Services;

use App\Enums\StockBatchType;
use App\Models\ItemLocation;
use App\Models\Location;
use App\Models\StockAdjustment;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    private ItemService $itemService;
    protected StockBatchService $batchService;
    protected StockMovementService $movementService;
    /**
     * Create a new class instance.
     */
    public function __construct(ItemService $itemService, StockBatchService $batchService, StockMovementService $movementService)
    {
        $this->itemService = $itemService;
        $this->batchService = $batchService;
        $this->movementService = $movementService;
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

                // 3. Update location stock using batch service and stock movement
                $unit = Unit::where('item_id',$item['item_id'])->where('is_smallest_unit',true)->get()->first();
                if($adjustmentQty >= 0){
                    $this->batchService->createBatch($item['item_id'],$data['location_id'],$adjustmentQty,$unit->buying_price,'adjustment',$adjustment->id);
                    $this->movementService->createMovement($item['item_id'],$data['location_id'],null,$adjustmentQty,'adjustment',StockBatchType::ADJUSTMENT,$adjustment->id,Auth::id());
                }else{
                    $this->batchService->consumeBatches($item['item_id'],[$data['location_id']],abs($adjustmentQty),StockBatchType::ADJUSTMENT_NEGATIVE,$adjustmentItem->id);
                    $this->movementService->createMovement($item['item_id'],$data['location_id'],null,$adjustmentQty,'adjustment',StockBatchType::ADJUSTMENT_NEGATIVE,$adjustment->id,Auth::id());
                }
            }
        });
    }
}
