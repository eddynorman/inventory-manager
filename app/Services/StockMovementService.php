<?php

namespace App\Services;

use App\Enums\StockBatchType;
use App\Models\ItemKit;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class StockMovementService
{

    public function createMovement(int $itemId,int $fromLocation, ?int $toLocation,float $quantity, string $type, StockBatchType $reference_type,int $reference_id, int $userId){
        return StockMovement::create([
            'item_id'          => $itemId,
            'from_location' => $fromLocation,
            'to_location' => $toLocation,
            'quantity'         => $quantity, //negative for stock reduction
            'type'             => $type,
            'reference_type'   => $reference_type->value,
            'reference_id'     => $reference_id,
            'created_by'       => $userId,
        ]);
    }

    public function saleItemsSync(array $saleItems, int $saleId, int $userId, StockBatchType $type){
        return DB::transaction(function () use ($saleItems,$saleId, $userId,$type){
            foreach($saleItems as $item){
                $movement = StockMovement::where('reference_id',$saleId)->where('reference_type',$type->value)->where('item_id',$item['item_id'])->get()->first();
                if($movement != null && $movement->quantity != -$item['quantity']){
                    $movement->quantity = -$item['quantity'];
                    $movement->save();
                }else{
                    StockMovement::create([
                        'item_id'          => $item['item_id'],
                        'from_location' => $item['location_id'],
                        'quantity'         => -$item['quantity'],
                        'type'             => "Item Sale",
                        'reference_type'   => $type->value,
                        'reference_id'     => $saleId,
                        'created_by'       => $userId,
                    ]);
                }
            }
        });
    }

    public function deleteSaleMovement(int $saleId,int $itemId,StockBatchType $type){
        StockMovement::where('reference_id',$saleId)->where('reference_type',$type->value)->where('item_id',$itemId)->delete();
    }
}
