<?php

namespace App\Services;

use App\Enums\StockBatchType;
use App\Models\ClosingStockSession;
use App\Models\ItemLocation;
use App\Models\UsedItems;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClosingStockService
{
    protected StockBatchService $batchService;
    protected StockMovementService $movementService;
    /**
     * Create a new class instance.
     */
    public function __construct(StockBatchService $batchService, StockMovementService $movementService)
    {
        $this->batchService = $batchService;
        $this->movementService = $movementService;
    }
    public function process(array $items, int $locationId, int $userId): void
    {
        DB::transaction(function () use ($items, $locationId, $userId) {

            $today = Carbon::today();

            // 🚨 Check if already closed
            $exists = ClosingStockSession::where('location_id', $locationId)
                ->whereDate('date', $today)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'locationId' => 'Stock already closed for this location today'
                ]);
            }

            // ✅ Create session
            $session = ClosingStockSession::create([
                'location_id' => $locationId,
                'recorded_by' => $userId,
                'date' => $today,
            ]);

            foreach ($items as $index => $item) {

                $opening = (float) $item['opening_stock'];
                $closing = (float) $item['closing_stock'];

                if ($closing < 0) {
                    throw ValidationException::withMessages([
                        "items.$index.closing_stock" => "Closing cannot be negative"
                    ]);
                }

                if ($closing > $opening) {
                    throw ValidationException::withMessages([
                        "items.$index.closing_stock" => "Closing cannot exceed opening"
                    ]);
                }

                $used = $opening - $closing;

                if ($used <= 0) continue;

                // Update stock
                $locationItem = ItemLocation::where([
                    'item_id' => $item['item_id'],
                    'location_id' => $locationId
                ])->get()->first();

                if (!$locationItem || $locationItem->quantity < $used) {
                    throw ValidationException::withMessages([
                        "items.$index.closing_stock" => "Insufficient stock"
                    ]);
                }
                // Save used items
                $usedItem = UsedItems::create([
                    'item_id' => $item['item_id'],
                    'location_id' => $locationId,
                    'recorded_by' => $userId,
                    'quantity' => $used,
                    'closing_stock_session_id' => $session->id,
                ]);

                // Log movement
                $batchData = $this->batchService->consumeBatches($item['item_id'],[$locationId],abs($used),StockBatchType::CLOSING_STOCK,$usedItem->id);
                $this->movementService->createMovement($item['item_id'],$locationId,null,-$used,'closing-stock',StockBatchType::CLOSING_STOCK,$usedItem->id,Auth::id());

                $usedItem->total_cost = $batchData['total_cost'];
                $usedItem->save();
            }
        });
    }
}
