<?php

namespace App\Services;

use App\Enums\StockBatchType;
use App\Models\Item;
use App\Models\ItemLocation;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\SupplierOrder;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\SupplierOrderItem;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceivingService
{
    private PurchaseService $purchaseService;
    protected StockBatchService $batchService;
    protected StockMovementService $movementService;

    public function __construct(PurchaseService $purchaseService, StockBatchService $batchService, StockMovementService $movementService)
    {
        $this->purchaseService = $purchaseService;
        $this->batchService = $batchService;
        $this->movementService = $movementService;
    }

    public function rules(string $type):array{
        if($type == 'order'){
            return [
                'selected_source_id' => ['required','integer','exists:supplier_orders,id'],
                'location_id' => ['required','integer','exists:locations,id'],
                'type' => ['required','string'],
                'source' => ['required','array','min:2'],
                'receiving' => ['required','array','min:2'],
                'receiving.items' => ['required','array','min:1'],
                'receiving.items.*.id' => ['required','integer','exists:supplier_order_items,id'],
                'receiving.items.*.item_id' => ['required','integer','exists:items,id'],
                'receiving.items.*.unit_id' => ['required','integer','exists:units,id'],
                'receiving.items.*.received_quantity' => ['required','numeric','gt:0'],
                'receiving.items.*.quantity' => ['required','numeric','gt:0'],
                'receiving.items.*.unit_price' => ['required','numeric','gt:0'],
                'receiving.items.*.total' => ['required','numeric','gt:0'],
            ];
        }else{
            return [
                'selected_source_id' => ['required','integer','exists:purchases,id'],
                'location_id' => ['required','integer','exists:locations,id'],
                'type' => ['required','string'],
                'source' => ['required','array','min:2'],
                'source.id' => ['required','integer','exists:purchases,id'],
                'receiving' => ['required','array','min:2'],
                'receiving.items' => ['required','array','min:1'],
                'receiving.items.*.id' => ['required','integer','exists:purchase_items,id'],
                'receiving.items.*.item_id' => ['required','integer','exists:items,id'],
                'receiving.items.*.unit_id' => ['required','integer','exists:units,id'],
                'receiving.items.*.received_quantity' => ['required','numeric','gt:0'],
                'receiving.items.*.quantity' => ['required','numeric','gt:0'],
                'receiving.items.*.unit_price' => ['required','numeric','gt:0'],
                'receiving.items.*.total' => ['required','numeric','gt:0'],
            ];
        }

    }

    public function loadUnreceivedPurchases()
    {
        $p = Purchase::with('purchaser')->where('is_received', false)->orderByDesc('id')->get(['id','requisition_id','purchased_by_id','total_amount']);
        return $p;
    }

    public function loadDepartmentName(int $source_id,string $type){
        $department = "";

        if($type == 'purchase'){
            $req = Purchase::find($source_id)->requisition()->with('department')->get()->first();
            $department = $req->department()->get()[0]->name;
        }else if($type == 'order'){
            $req = SupplierOrder::find($source_id)->requisition()->with('department')->get()[0];
            $department = $req->department()->get()[0]->name;
        }
        return $department;
    }

    public function loadUnreceivedOrders()
    {
        $order = SupplierOrder::with('createdBy')->where('is_received', false)->orderByDesc('id')->get(['id','requisition_id','created_by','supplier_id','total_amount','amount_pending']);
        return $order;
    }

    public function loadSource(string $type, int $id): array
    {
        return $this->purchaseService->getById($id,$type);
    }

    public function updateStock(array $items, int $location_id,$rcvId): void
    {
        DB::transaction(function () use ($items,$location_id,$rcvId) {
            $location_items = ItemLocation::where('location_id',$location_id)->get();
            foreach ($items as $key => $data) {
                foreach($location_items as $loc_i){
                    if($loc_i->item_id == $data['item_id']){
                        $unit = Unit::find($data['unit_id']);
                        $rcv_qty = $data['received_quantity']*$unit->smallest_units_number;
                        $this->batchService->createBatch($data['item_id'],$location_id,$rcv_qty,$unit->buying_price,'receiving',$rcvId);
                        $this->movementService->createMovement($data['item_id'],$location_id,null,$rcv_qty,'receiving',StockBatchType::RECEIVING,$rcvId,Auth::id());
                        $items[$key]['processed'] = true;

                        break;
                    }
                }

            }

            foreach ($items as $key => $data) {
                if(!isset($data['processed'])){
                    $unit = Unit::find($data['unit_id']);
                    $rcv_qty = $data['received_quantity']*$unit->smallest_units_number;
                    ItemLocation::create([
                        'item_id' => $data['item_id'],
                        'location_id' => $location_id,
                        'quantity' => 0,//quantity will be updated by stock batch service
                    ]);
                    $this->batchService->createBatch($data['item_id'],$location_id,$rcv_qty,$unit->buying_price,'receiving',$rcvId);
                    $this->movementService->createMovement($data['item_id'],$location_id,null,$rcv_qty,'receiving',StockBatchType::RECEIVING,$rcvId,Auth::id());
                }
            }

        });
    }

    public function getById(int $id){
        $r = Receiving::with(['purchase','supplierOrder','receiver','location','items.item','items.unit'])->find($id);
        return $r;
    }

    public function saveReceiving(array $data, int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $receiving = Receiving::create([
                'purchase_id' => $data['type'] === 'purchase' ? $data['source']['id'] : null,
                'supplier_order_id' => $data['type'] === 'order' ? $data['source']['id'] : null,
                'received_by_id' => $userId,
                'location_id' => $data['location_id'] ?? null,
            ]);

            foreach ($data['receiving']['items'] as $it) {
                $t = floatval($it['received_quantity']) * floatval($it['unit_price']);
                ReceivingItem::create([
                    'receiving_id' => $receiving->id,
                    'item_id' => $it['item_id'],
                    'unit_id' => $it['unit_id'],
                    'quantity' => $it['received_quantity'],
                    'unit_price' => $it['unit_price'],
                    'total' => $t,
                ]);
            }

            //mark individual items as received

            foreach($data['receiving']['items'] as $item){
                $source_item = null;
                if($data['type'] == "purchase"){
                    $source_item = PurchaseItem::find($item['id']);
                }else{
                    $source_item = SupplierOrderItem::find($item['id']);
                }
                $source_item->received_quantity += $item['received_quantity'];
                if($source_item->received_quantity == $item['quantity']){
                    $source_item->is_received = true;
                }
                $source_item->save();

            }

            $this->updateStock($data['receiving']['items'],$data['location_id'],$receiving->id);

            // mark source as received
            $source = null;
            if ($data['type'] == "purchase") {
                $source = Purchase::find($receiving->purchase_id);
            }else{
                $source = SupplierOrder::find($receiving->supplier_order_id);
            }
            if ($source) {
                $s_items = $source->items;
                $is_received = true;
                foreach($s_items as $s_item){
                    if($s_item->is_received == false){
                        $is_received = false;
                        break;
                    }
                }
                $source->is_received = $is_received;
                $source->save();
            }

            return $receiving;
        });
    }

    public function deleteReceiving(int $id)
    {
        $receiving = Receiving::findOrFail($id);
        $items = $receiving->items();
        // allow deletion only for admins is enforced by caller
        return DB::transaction(function () use ($receiving,$items) {
            if ($receiving->purchase_id) {
                $p = Purchase::find($receiving->purchase_id);
                if ($p) {
                    $p->is_received = false;
                    $p->save();

                    $p_items = $p->items;
                    foreach($p_items as $p_item){
                        foreach($items as $item){
                            if($p_item->item_id == $item->item_id){
                                $p_item->is_received = false;
                                $p_item->save;
                                break;
                            }
                        }
                    }
                }
            }
            if ($receiving->supplier_order_id) {
                $o = SupplierOrder::find($receiving->supplier_order_id);
                if ($o) {
                    $o->is_received = false;
                    $o->save();

                    $o_items = $o->items;
                    foreach($o_items as $o_item){
                        foreach($items as $item){
                            if($o_item->item_id == $item->item_id){
                                $o_item->is_received = false;
                                $o_item->save;
                                break;
                            }
                        }
                    }
                }
            }
            $receiving->items()->delete();
            $receiving->delete();
            return true;
        });
    }
}
