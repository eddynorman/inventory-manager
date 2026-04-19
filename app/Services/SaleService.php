<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Item;
use App\Models\Unit;
use App\Models\ItemKit;
use App\Models\ItemKitItem;
use App\Models\ItemLocation;
use App\Models\LocationItem;
use App\Models\SalePayment;
use App\Models\StockMovement;
use App\Enums\StockBatchType;
use App\Models\PaymentMethod;
use App\Models\SaleItemKitItem;
use App\Models\SaleItemKit;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class SaleService
{

    /**
     * Create a new class instance.
     */
    public function __construct(StockBatchService $batchService)
    {
       //
    }

    public function rules(){
        return [
            'locationIds' => ['required','array','min:1'],
            'payments' => ['nullable','array'],
            'sale' => ['required','array','min:2'],
            'sale.id' => ['nullable','integer','exists:sales,id'],
            'sale.served_by' => ['required','array','min:1'],
            'sale.items' => ['required','array','min:1'],
            'sale.items.*.item_id' => ['nullable','integer','exists:items,id'],
            'sale.items.*.type' => ['required','string'],
            'sale.items.*.kit_id' => ['nullable','integer','exists:item_kits,id'],
            'sale.items.*.quantity' => ['required','numeric','gt:0'],
            'sale.items.*.selected_unit_id' => ['required','integer'],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | SALE CREATION
    |--------------------------------------------------------------------------
    */

    public function createSale(int $userId, array $locationIds, array $servedByIds,string $type="upfront"): Sale
    {
        return DB::transaction(function () use($userId,$locationIds,$servedByIds,$type) {
            $sale = Sale::create([
                'created_by'   => $userId,
                'status'       => 'pending',
                'sale_type'    => $type,
                'total_amount' => 0,
                'total_paid'   => 0,
                'balance'      => 0,
            ]);

            $sale->servedBy()->sync($servedByIds);
            $sale->locations()->sync($locationIds);
            return $sale;
        });
    }

    public function editServedBy(int $saleId, array $servedByIds){
        DB::transaction(function () use ($saleId,$servedByIds){
            $sale = Sale::findOrFail($saleId);
            $sale->servedBy()->sync($servedByIds);
        });
    }

    public function getSale(int $saleId): Sale
    {
        return Sale::with(['items', 'payments', 'servedBy','locations'])->findOrFail($saleId);
    }

    public function getUnits(int $itemId){
        return Unit::where('item_id',$itemId)->where('is_active',true)->select('id','name','smallest_units_number','selling_price')->get()->toArray();
    }

    public function savePaymentMethod(string $name, string $reference_number, ?int $id){
        $method = PaymentMethod::UpdateOrCreate(['id' => $id],[
            'name' => $name,
            'reference_number' => $reference_number,
        ]);

        return $method;
    }

    /*
    |--------------------------------------------------------------------------
    | ITEM MANAGEMENT
    |--------------------------------------------------------------------------
    */

    public function saveItems(array $saleItems, int $saleId, array $locationIds){
        DB::transaction(function () use ($saleItems, $saleId, $locationIds){
            $sale = Sale::findOrFail($saleId);
            $batchService = new StockBatchService();
            $stockMovementService = new StockMovementService();
            $movementData = [];
            foreach ($saleItems as $saleItem) {
                $existing = $sale->items()->where('item_id', $saleItem['item_id'])->first();
                $unit = Unit::findOrFail($saleItem['selected_unit_id']);
                $qty = $saleItem['quantity'] * $unit->smallest_units_number;

                if ($existing) {
                    if($existing->quantity != $qty){
                        $usages = $batchService->getBatchUsageForReverse($existing);
                        $batchService->reverseConsumption($usages);
                        $returned = $batchService->consumeBatches($saleItem['item_id'],$locationIds,$qty,StockBatchType::ITEM_SALE,$existing->id);

                        foreach($returned['qtys'] as $qty){
                            $movementData[] = $qty;
                        }

                        $existing->quantity = $saleItem['quantity'];
                        $existing->unit_id = $saleItem['selected_unit_id'];
                        $existing->unit_price = $unit->selling_price;
                        $existing->total = $saleItem['quantity'] * $unit->selling_price;
                        $existing->cost_at_sale = $returned['total_cost'];
                        $existing->save();
                    }
                } else {
                    $price = $unit->selling_price;

                    $newSaleItem = SaleItem::create([
                        'sale_id'        => $saleId,
                        'item_id'        => $saleItem['item_id'],
                        'unit_id'        => $unit->id,
                        'quantity'       => $saleItem['quantity'],
                        'unit_price'     => $price,
                        'cost_at_sale'   => 0,
                        'total'          => $price * $saleItem['quantity'],
                        'number_of_items' => $qty,
                    ]);

                    $batchData  = $batchService->consumeBatches($saleItem['item_id'],$locationIds,$qty,StockBatchType::ITEM_SALE,$newSaleItem->id);

                    foreach($batchData['qtys'] as $qty){
                        $movementData[] = $qty;
                    }
                    $newSaleItem->cost_at_sale = $batchData['total_cost'];
                    $newSaleItem->save();
                }

            }
            $stockMovementService->saleItemsSync($movementData,$saleId,Auth::id(),StockBatchType::ITEM_SALE);
            $this->syncRemovedItem($saleId,$saleItems);
        });
    }

    public function syncRemovedItem(int $saleId,array $saleItems){
        $batchService = new StockBatchService();
        $stockMovementService = new StockMovementService();
        $currentItems = SaleItem::where('sale_id',$saleId)->get();
        foreach($currentItems as $currentItem){
            $found = false;
            foreach($saleItems as $saleItem){
                if($currentItem->item_id == $saleItem['item_id']){
                    $found = true;
                    break;
                }
            }
            if($found == false){
                $batchService = new StockBatchService();
                $stockMovementService = new StockMovementService();
                $usages = $batchService->getBatchUsageForReverse($currentItem);
                $batchService->reverseConsumption($usages);
                $stockMovementService->deleteSaleMovement($saleId,$currentItem->item_id,StockBatchType::ITEM_SALE);
                $currentItem->delete();
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ITEM KITS
    |--------------------------------------------------------------------------
    */

    public function saveKIts(array $kits, int $saleId, array $locationIds){
        DB::transaction(function () use ($kits, $saleId, $locationIds){
            $sale = Sale::findOrFail($saleId);
            $batchService = new StockBatchService();
            $stockMovementService = new StockMovementService();
            $movementData = [];
            foreach ($kits as $kitt) {
                $existing = $sale->kits()->where('item_kit_id', $kitt['kit_id'])->first();
                $kit = ItemKit::find($kitt['kit_id']);
                if ($existing) {
                    if($existing->quantity != $kitt['quantity']){
                        $kit_items = $kit->kitItems()->get();
                        $sold_kit_items = SaleItemKitItem::where('sale_item_kit_id',$existing->id);
                        foreach($kit_items as $k){
                            $saleItemKitItemId = null;
                            $unit = Unit::find($k->unit_id);
                            $qty = $k->quantity * $unit->smallest_units_number * $kitt['quantity'];
                            $usages = null;
                            foreach($sold_kit_items as $ski){
                                if($ski->item_id == $k->item_id){
                                    $ski->quantity = $qty;
                                    $ski->save();
                                    $saleItemKitItemId = $ski->id;
                                    $usages = $batchService->getBatchUsageForReverse($ski);
                                    $batchService->reverseConsumption($usages);
                                    $returned = $batchService->consumeBatches($k->item_id,$locationIds,$qty,StockBatchType::KIT_SALE,$saleItemKitItemId,$kit->name);
                                    foreach($returned['qtys'] as $rq){
                                        $movementData[] = $rq;
                                    }
                                }
                            }
                        }

                        $existing->quantity = $kitt['quantity'];
                        $existing->unit_price = $unit->unit_price;
                        $existing->total = $kitt['quantity'] * $kit->selling_price;
                        $existing->save();
                    }
                } else {
                    $kit_items = $kit->kitItems()->get();
                    foreach($kit_items as $k){
                        $saleItemKitItemId = null;
                        $unit = Unit::find($k->unit_id);
                        $qty = $k->quantity * $unit->smallest_units_number * $kitt['quantity'];
                        $price = $unit->selling_price;

                        $saleKit = SaleItemKit::create([
                            'sale_id' => $saleId,
                            'item_kit_id'=> $kit->id,
                            'quantity' => $qty,
                            'cost_at_sale' => 0,
                            'selling_price' => $kit->selling_price,
                            'total' => $kit->selling_price * $kitt['quantity'],
                        ]);

                        $saleItemKitItem = SaleItemKitItem::create([
                            'sale_id'        => $saleId,
                            'sale_item_kit_id' => $saleKit->id,
                            'item_id'        => $k->item_id,
                            'unit_id'        => $unit->id,
                            'quantity'       => $k->quantity * $kitt['quantity'],
                            'unit_price'     => $price,
                            'cost_at_sale'   => 0,
                        ]);
                        $returned = $batchService->consumeBatches($k->item_id,$locationIds,$qty,StockBatchType::KIT_SALE,$saleItemKitItem->id,$kit->name);
                        foreach($returned['qtys'] as $rq){
                            $movementData[] = $rq;
                        }
                        $saleItemKitItem->cost_at_sale = $returned['total_cost'];
                        $saleItemKitItem->save();
                    }
                }

            }
            $stockMovementService->saleItemsSync($movementData,$saleId,Auth::id(), StockBatchType::KIT_SALE);
            $this->syncRemovedKit($saleId,$kits);
        });
    }

    public function syncRemovedKit(int $saleId,array $saleKits){
        $currentKits = SaleItemKit::where('sale_id',$saleId)->get();
        foreach($currentKits as $currentKit){
            $found = false;
            foreach($saleKits as $saleKit){
                if($saleKit['id'] == $currentKit->id){
                    $found = true;
                    break;
                }
            }
            if($found == false){
                $batchService = new StockBatchService();
                $stockMovementService = new StockMovementService();
                $sold_kit_items = SaleItemKitItem::where('sale_item_kit_id',$currentKit->id);
                foreach($sold_kit_items as $ski){
                    $usages = $batchService->getBatchUsageForReverse($ski);
                    $batchService->reverseConsumption($usages);
                    $stockMovementService->deleteSaleMovement($saleId,$ski->item_id,StockBatchType::KIT_SALE);
                    $ski->delete();
                }
                $currentKit->delete();
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TOTALS
    |--------------------------------------------------------------------------
    */

    public function recalculateTotals(int $saleId): void
    {
        $sale = Sale::with('items','kits')->findOrFail($saleId);

        $total = $sale->items->sum('total') + $sale->kits->sum('total');

        $sale->total_amount = $total;
        $sale->balance = $total - $sale->total_paid;
        $sale->save();
    }

    /*
    |--------------------------------------------------------------------------
    | PAYMENTS
    |--------------------------------------------------------------------------
    */

    public function addPayment(int $saleId, float $amount, int $method_id, int $userId): void
    {
        DB::transaction(function () use ($saleId, $amount, $method_id, $userId) {

            $sale = Sale::findOrFail($saleId);

            SalePayment::create([
                'sale_id'        => $saleId,
                'amount'         => $amount,
                'payment_method' => $method_id,
                'received_by'    => $userId,
            ]);

            $sale->total_paid += $amount;
            $sale->balance = $sale->total_amount - $sale->total_paid;
            //$sale->status = $sale->balance <= 0 ? 'completed' : 'pending';
            $sale->payment_status = $sale->balance <= 0 ? 'paid' : 'partial';
            $sale->save();
            if($sale->balance <= 0){
                $this->completeSale($sale->id);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | COMPLETE SALE
    |--------------------------------------------------------------------------
    */

    public function completeSale(int $saleId): void
    {
        DB::transaction(function () use ($saleId) {

            $sale = Sale::with('items','kits')->findOrFail($saleId);

            if ($sale->total_paid < $sale->total_amount) {
                throw new Exception("Cannot complete sale: payment incomplete.");
            }

            $sale->status = 'completed';
            $sale->completed_at = now();
            $sale->save();
        });
    }

    public function getPrintSaleData(int $saleId):array{
        $saleData = [];
        $sale = Sale::with(['items.item','kits.kit', 'payments.method', 'servedBy','createdBy'])->findOrFail($saleId);
        $saleData['created_at'] = $sale->created_at;
        $saleData['total_amount'] = $sale->total_amount;
        $saleData['pending'] = $sale->balance;
        $saleData['id'] = $sale->id;

        foreach($sale->items as $saleItem){
            $i = [
                'name' => $saleItem->item->name,
                'selling_price' => $saleItem->unit_price,
                'quantity' => $saleItem->quantity,
            ];
            $saleData['items'][] = $i;
        }
        foreach($sale->kits as $saleKit){
            $i = [
                'name' => $saleKit->kit->name,
                'selling_price' => $saleKit->selling_price,
                'quantity' => $saleKit->quantity,
            ];
            $saleData['items'][] = $i;
        }
        $saleData['servedBy'] = $sale->servedBy->pluck('name')->join(', ');

        $saleData['recordedBy'] = $sale->createdBy->name;
        $saleData['payments'] = [];
        foreach($sale->payments as $payment){
            $saleData['payments'][] = ['amount' => $payment->amount, 'method' => $payment->method->toArray()];
        }

        return $saleData;
    }

    /*
    |--------------------------------------------------------------------------
    | STOCK VALIDATION
    |--------------------------------------------------------------------------
    */

    private function checkItemStock(int $itemId, int $qty, array $locationIds): void
    {
        $total = ItemLocation::where('item_id', $itemId)
            ->whereIn('location_id', $locationIds)
            ->sum('stock');

        if ($total < $qty) {
            throw new Exception("Insufficient stock for item ID {$itemId}");
        }
    }

    private function checkKitStock(int $kitId, int $qty, array $locationIds): void
    {
        $kit = ItemKit::with('kitItems.unit')->find($kitId);
        $kitsPerItem = [];
        $kitItems = $kit->kitItems()->get();
        foreach($kitItems as $kitItem){
            $unit = $kitItem->unit;
            $number_per_kit = $unit->smallest_unit_number * $kitItem->quantity;

            $available = ItemLocation::where('item_id', $kitItem->item_id)
            ->whereIn('location_id', $locationIds)
            ->sum('stock');

            $kitsPerItem[]= (int)($available/$number_per_kit);

        }

        $maximum_kits_available = min($kitsPerItem);

        if ($maximum_kits_available < $qty) {
            throw new Exception("Insufficient stock for kit ID {$kitId}");
        }
    }

    private function getItemStock(int $itemId, array $locationIds): int
    {
        return ItemLocation::where('item_id', $itemId)
            ->whereIn('location_id', $locationIds)
            ->sum('quantity');
    }

    private function getKitAvailableQty(int $kitId, array $locationIds): int
    {
        $kit = ItemKit::with('kitItems')->find($kitId);

        $kitsPerItem = [];

        foreach ($kit->kitItems as $kitItem) {
            $unit = Unit::find($kitItem->unit_id);

            $numberPerKit = $unit->smallest_units_number * $kitItem->quantity;

            $available = ItemLocation::where('item_id', $kitItem->item_id)
                ->whereIn('location_id', $locationIds)
                ->sum('quantity');

            $kitsPerItem[] = (int) ($available / $numberPerKit);
        }

        return count($kitsPerItem) ? min($kitsPerItem) : 0;
    }


    public function getAll(array $locationIds)
    {
        // ITEMS
        $items = Item::with('category')
            ->get()
            ->map(function ($item) use ($locationIds) {
                $stock = $this->getItemStock($item->id, $locationIds);

                return [
                    'type' => 'item',
                    'item_id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category?->name,
                    'stock' => $stock,
                    'is_available' => $stock > 0,
                ];
            })
            ->filter(function ($item) {
                return $item['stock'] > 0;
            });

        // KITS
        $kits = ItemKit::with('kitItems')
            ->get()
            ->map(function ($kit) use ($locationIds) {
                $availableQty = $this->getKitAvailableQty($kit->id, $locationIds);

                return [
                    'type' => 'kit',
                    'kit_id' => $kit->id,
                    'name' => $kit->name,
                    'category' => 'Kit',
                    'stock' => $availableQty,
                    'is_available' => $availableQty > 0,
                ];
            })
            ->filter(function ($kit) {
                return $kit['stock'] > 0;
            });

        return $items->merge($kits)->values();
    }

    public function search(string $search, array $locationIds)
    {
        // ITEMS
        $items = Item::with('category')
            ->where('name', 'like', "%{$search}%")
            ->get()
            ->map(function ($item) use ($locationIds) {
                $stock = $this->getItemStock($item->id, $locationIds);

                return [
                    'type' => 'item',
                    'item_id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category?->name,
                    'stock' => $stock,
                    'is_available' => $stock > 0,
                ];
            })
            ->filter(function ($item) {
                return $item['stock'] > 0;
            });

        // KITS
        $kits = ItemKit::with('kitItems')
            ->where('name', 'like', "%{$search}%")
            ->get()
            ->map(function ($kit) use ($locationIds) {
                $availableQty = $this->getKitAvailableQty($kit->id, $locationIds);

                return [
                    'type' => 'kit',
                    'kit_id' => $kit->id,
                    'name' => $kit->name,
                    'category' => 'Kit',
                    'stock' => $availableQty,
                    'selling_price' => $kit->selling_price,
                    'is_available' => $availableQty > 0,
                ];
            })
            ->filter(function ($kit) {
                return $kit['stock'] > 0;
            });

        return $items->merge($kits)->values();
    }

    public function save(array $saleData,?int $saleId,string $type = "upfront"){
        return DB::transaction(function () use ($saleData, $saleId, $type){
            if($saleId != null){
                $this->editServedBy($saleId,$saleData['sale']['served_by']);
            }else{
                $sale = $this->createSale(Auth::id(),$saleData['locationIds'],$saleData['sale']['served_by'],$type);
                $saleId = $sale->id;
            }
            $items = [];
            $kits = [];
            foreach($saleData['sale']['items'] as $item){
                if($item['type'] == 'item'){
                    $items [] = $item;
                }else if($item['type'] == 'kit'){
                    $kits [] = $item;
                }
            }

            $this->saveItems($items,$saleId,$saleData['locationIds']);
            $this->saveKIts($kits,$saleId,$saleData['locationIds']);
            $this->recalculateTotals($saleId);

            //payments
            if(count($saleData['payments']) > 0){
                foreach($saleData['payments'] as $payment){
                    if(!isset($payment['id'])){
                        $this->addPayment($saleId,$payment['amount'],$payment['method']['id'],Auth::id());
                    }
                }
            }
            return $saleId;
        });
    }

}
