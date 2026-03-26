<?php

namespace App\Services;

use App\Enums\StockBatchType;
use App\Models\ItemKit;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class StockMovementService
{

    public function createMovement(int $itemId,int $fromLocation, ?int $toLocation,float $quantity, string $type, StockBatchType $reference_type,int $saleId, int $userId){
        return StockMovement::create([
            'item_id'          => $itemId,
            'from_location_id' => $fromLocation,
            'to_location' => $toLocation,
            'quantity'         => $quantity, //negative for stock reduction
            'type'             => $type,
            'reference_type'   => $reference_type->value,
            'reference_id'     => $saleId,
            'created_by'       => $userId,
        ]);
    }

    public function saleItemsSync(array $saleItems, int $saleId, int $fromLocation, int $userId){
        return DB::transaction(function () use ($saleItems,$saleId,$fromLocation, $userId){
                foreach($saleItems as $item){
                $movement = StockMovement::where('reference_id',$saleId)->where('reference_type',StockBatchType::ITEM_SALE->value)->where('item_id',$item['id']);
                if(!$movement->isEmpty() && $movement->quantity != -$item['quantity']){
                    $movement->quantity = -$item['quantity'];
                    $movement->save();
                }else{
                    StockMovement::create([
                        'item_id'          => $item['item_id'],
                        'from_location_id' => $fromLocation,
                        'quantity'         => -$item['quantity'],
                        'type'             => "Item Sale",
                        'reference_type'   => StockBatchType::ITEM_SALE->value,
                        'reference_id'     => $saleId,
                        'created_by'       => $userId,
                    ]);
                }
            }
        });
    }

    public function kitItemsSync(array $saleKits,int $saleId, int $fromLocation, int $userId){
        return DB::transaction(function () use ($saleKits, $saleId, $fromLocation, $userId){
            foreach($saleKits as $kit){
                $kit = ItemKit::find($kit['kit_id']);
                $kit_items = $kit->kitItems()->get();
                foreach($kit_items as $k){
                    $unit = Unit::find($k->unit_id);
                    $qty = $k->quantity * $unit->smallest_units_number;
                    $movement = StockMovement::where('reference_id',$saleId)->where('reference_type',StockBatchType::KIT_SALE->value)->where('item_id',$k->item_id);
                    if(!$movement->isEmpty() && $movement->quantity != -$qty){
                        $movement->quantity = -$qty;
                        $movement->save();
                    }else{
                        StockMovement::create([
                            'item_id'          => $k->item_id,
                            'from_location_id' => $fromLocation,
                            'quantity'         => -$qty,
                            'type'             => "Kit Item Sale",
                            'reference_type'   => StockBatchType::KIT_SALE->value,
                            'reference_id'     => $saleId,
                            'created_by'       => $userId,
                        ]);
                    }
                }
            }
        });
    }
}
