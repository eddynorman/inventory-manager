<?php

namespace App\Services;

use App\Models\SaleItemBatch;
use App\Models\StockBatch;
use Exception;
use Illuminate\Support\Facades\DB;

use App\Enums\StockBatchType;

class StockBatchService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getByItem(int $itemId){
        return StockBatch::where('item_id', $itemId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at')
            ->get();
    }

    public function getByLocations(array $locationIds){
        return StockBatch::whereIn('location_id', $locationIds)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at')
            ->get();
    }

    public function getByItemAndLocation(int $itemId, array $locationIds){
        return StockBatch::where('item_id', $itemId)
            ->whereIn('location_id', $locationIds)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at')
            ->get();
    }

    public function getHistoryByItem(int $itemId){
        return StockBatch::where('item_id', $itemId)
            ->orderBy('created_at')
            ->get();
    }

    public function getHistoryByItemAndLocation(int $itemId, array $locationIds){
        return StockBatch::where('item_id', $itemId)
            ->whereIn('location_id', $locationIds)
            ->orderBy('created_at')
            ->get();
    }

    public function createBatch(int $item_id,int $location_id,float $remaining_quantity,float $unit_cost,string $reference_type,int $reference_id){
        $batch = StockBatch::create([
            'item_id' => $item_id,
            'location_id' => $location_id,
            'remaining_quantity' => $remaining_quantity,
            'unit_cost' => $unit_cost,
            'reference_type' => $reference_type,
            'reference_id' => $reference_id,
        ]);

        return $batch;
    }

    public function consumeBatches(int $itemId,array $locationIds,float $consumeQty = 1,StockBatchType $type,int $type_id){
        return DB::transaction(function () use ($itemId,$locationIds,$consumeQty,$type,$type_id){
            $batches = StockBatch::where('item_id', $itemId)
                ->whereIn('location_id', $locationIds)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('created_at')
                ->lockForUpdate()
                ->cursor();
            $remainingQty = $consumeQty;
            $totalCost = 0;
            $returnData = ['item_id' => $itemId,'qtys' =>[]];

            foreach ($batches as $batch) {

                if ($remainingQty <= 0) break;

                $takeQty = min($remainingQty, $batch->remaining_quantity);

                $totalCost += $takeQty * $batch->unit_cost;

                $batch->decrement('remaining_quantity', $takeQty);

                $remainingQty -= $takeQty;
                $returnData['qtys'][] = ['quantity' => $takeQty,'unit_cost' => $batch->unit_cost, 'batch_id' => $batch->id, 'location_id' => $batch->location_id,'item_id' => $itemId];
                $saleItemId = null;
                $saleItemKitItemId = null;
                if($type == StockBatchType::ITEM_SALE){
                    $saleItemId = $type_id;
                }else if($type == StockBatchType::KIT_SALE){
                    $saleItemKitItemId = $type_id;
                }
                SaleItemBatch::create([
                    'item_id' => $itemId,
                    'sale_item_id' => $saleItemId,
                    'sale_item_kit_item_id' => $saleItemKitItemId,
                    'stock_batch_id' => $batch->id,
                    'quantity' => $takeQty,
                    'unit_cost' => $batch->unit_cost,
                    'total' => $takeQty*$batch->unit_cost,
                    'type' => $type->value,
                    'type_id' => $type_id
                ]);
            }

            if ($remainingQty > 0) {
                throw new Exception("Insufficient stock");
            }
            $returnData['total_cost'] = $totalCost;
            return $returnData;
        });
    }

    public function getBatchUsageForReverse($saleItem): array
    {
        return $saleItem->batchUsages->map(function ($usage) {
            return [
                'id' => $usage->id,
                'batch_id' => $usage->stock_batch_id,
                'quantity' => $usage->quantity,
            ];
        })->toArray();
    }

    public function reverseConsumption(array $batchConsumptions): void
    {
        DB::transaction(function () use ($batchConsumptions) {

            foreach ($batchConsumptions as $consumption) {

                $batch = StockBatch::lockForUpdate()->find($consumption['batch_id']);

                if (!$batch) {
                    throw new \Exception("Batch not found: {$consumption['batch_id']}");
                }
                if ($consumption['quantity'] <= 0) {
                    throw new Exception("Invalid reverse quantity");
                }

                $batch->remaining_quantity += $consumption['quantity'];
                $batch->save();
            }
            // 🔥 Delete usage records AFTER reversing
            SaleItemBatch::whereIn('id', collect($batchConsumptions)->pluck('id'))
                ->delete();
        });
    }

    public function reverseBySaleItem(int $saleItemId): void
    {
        DB::transaction(function () use ($saleItemId) {

            $usages = SaleItemBatch::where('sale_item_id', $saleItemId)->get();

            foreach ($usages as $usage) {

                $batch = StockBatch::lockForUpdate()->find($usage->stock_batch_id);

                if (!$batch) {
                    throw new Exception("Batch not found: {$usage->stock_batch_id}");
                }
                if ($usage->quantity <= 0) {
                    throw new Exception("Invalid reverse quantity");
                }

                $batch->increment('remaining_quantity', $usage->quantity);
            }

            // delete after reverse
            SaleItemBatch::where('sale_item_id', $saleItemId)->delete();
        });
    }


}
