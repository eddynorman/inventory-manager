<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\RequisitionItem;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\SupplierOrderPayments;
use Illuminate\Support\Facades\DB;


class PurchaseService
{
    private RequisitionService $requisitionService;
    private ItemService $itemService;
    private UnitService $unitService;

    public function __construct(RequisitionService $service, ItemService $itemService, UnitService $unitService)
    {
        $this->requisitionService = $service;
        $this->itemService = $itemService;
        $this->unitService = $unitService;
    }


    public function getById(int $id, string $type="purchase"){
        if($type == "purchase"){
            $purchase = Purchase::with('items')->find($id)->toArray();
            $req = $this->requisitionService->getById($purchase['requisition_id']);
            $req_items = $req->items()->get();
            foreach($purchase['items'] as $key => $pItem){
                $item = $this->itemService->getById($pItem['item_id']);
                $units = $item->units->toArray();
                $unit_name = '';
                $req_item = null;
                foreach($req_items as $it){
                    if($it->item_id == $item->id && $it->requisition_id == $purchase['requisition_id']){
                        $req_item = $it;
                        break;
                    }
                }
                foreach($units as $index => $unit){
                    if($unit['id'] == $pItem['unit_id']){
                        $unit_name = $unit['name'];
                        break;
                    }
                }
                $purchase['items'][$key]['name'] = $item->name;
                $purchase['items'][$key]['unit_name'] = $unit_name;
                $purchase['items'][$key]['requisition_item_id'] = $req_item->id;
            }
                // attach purchaser name and created_at if available
                $purchaseModel = Purchase::with('purchaser')->find($id);
                $purchase['purchased_by_name'] = null;
                $purchase['created_at'] = null;
                if($purchaseModel){
                    if($purchaseModel->purchaser()->exists()){
                        $nameRow = $purchaseModel->purchaser()->get('name')->first();
                        $purchase['purchased_by_name'] = $nameRow? $nameRow->name : null;
                    }
                    $purchase['created_at'] = $purchaseModel->created_at ? $purchaseModel->created_at->toDateTimeString() : null;
                }
            return $purchase;
        }else{
            $order = SupplierOrder::with('items')->find($id)->toArray();
            $req = $this->requisitionService->getById($order['requisition_id']);
            $req_items = $req->items()->get();
            foreach($order['items'] as $key => $pItem){
                $item = $this->itemService->getById($pItem['item_id']);
                $units = $item->units->toArray();
                $unit_name = '';
                $req_item = null;
                foreach($req_items as $it){
                    if($it->item_id == $item->id && $it->requisition_id == $order['requisition_id']){
                        $req_item = $it;
                        break;
                    }
                }
                foreach($units as $index => $unit){
                    if($unit['id'] == $pItem['unit_id']){
                        $unit_name = $unit['name'];
                        break;
                    }
                }
                $order['items'][$key]['name'] = $item->name;
                $order['items'][$key]['unit_name'] = $unit_name;
                $order['items'][$key]['requisition_item_id'] = $req_item->id;
            }
                // attach supplier and creator names and created_at if available
                $orderModel = SupplierOrder::with('supplier','createdBy')->find($id);
                $order['supplier_name'] = null;
                $order['created_by_name'] = null;
                $order['created_at'] = null;
                if($orderModel){
                    if($orderModel->supplier()->exists()){
                        $s = $orderModel->supplier()->get('name')->first();
                        $order['supplier_name'] = $s? $s->name : null;
                    }
                    if($orderModel->createdBy()->exists()){
                        $c = $orderModel->createdBy()->get('name')->first();
                        $order['created_by_name'] = $c? $c->name : null;
                    }
                    $order['created_at'] = $orderModel->created_at ? $orderModel->created_at->toDateTimeString() : null;
                }
            return $order;
        }
    }

    public function getInstanceById(int $id, string $type="purchase"){
        if($type == "purchase"){
            $purchase = Purchase::with('items')->find($id);
            return $purchase;
        }else{
            $order = SupplierOrder::with('items')->find($id);
            return $order;
        }
    }

    public function orderRules():array{
        return [
            'selected_supplier_id' => ['required','integer','exists:suppliers,id'],
            'requisition' => ['required','array','min:2'],
            'requisition.requisition_id' => ['required','integer','exists:requisitions,id'],
            'order' => ['required','array','min:1'],
            'order.id' => ['nullable','integer','exists:supplier_orders,id'],
            'order.items' => ['required','array','min:1'],
            'order.items.*.id' => ['nullable','integer','exists:supplier_order_items,id'],
            'order.items.*.requisition_item_id' => ['required','integer','exists:requisition_items,id'],
            'order.items.*.item_id' => ['required','integer','exists:items,id'],
            'order.items.*.unit_id' => ['required','integer','exists:units,id'],
            'order.items.*.requested_quantity' => ['required','numeric','gt:0'],
            'order.items.*.quantity' => ['required','numeric','gt:0'],
            'order.items.*.requested_unit_price' => ['required','numeric','gt:0'],
            'order.items.*.unit_price' => ['required','numeric','gt:0'],
            'order.items.*.total' => ['required','numeric','gt:0'],
        ];

    }
    public function purchaseRules():array{
        return [
            'requisition' => ['required','array','min:2'],
            'requisition.requisition_id' => ['required','integer','exists:requisitions,id'],
            'purchase' => ['required','array','min:1'],
            'purchase.id' => ['nullable','integer','exists:purchases,id'],
            'purchase.items' => ['required','array','min:1'],
            'purchase.items.*.id' => ['nullable','integer','exists:purchase_items,id'],
            'purchase.items.*.requisition_item_id' => ['required','integer','exists:requisition_items,id'],
            'purchase.items.*.item_id' => ['required','integer','exists:items,id'],
            'purchase.items.*.unit_id' => ['required','integer','exists:units,id'],
            'purchase.items.*.requested_quantity' => ['required','numeric','gt:0'],
            'purchase.items.*.quantity' => ['required','numeric','gt:0'],
            'purchase.items.*.requested_unit_price' => ['required','numeric','gt:0'],
            'purchase.items.*.unit_price' => ['required','numeric','gt:0'],
            'purchase.items.*.total' => ['required','numeric','gt:0'],
        ];

    }

    public function loadRequisitionData($requisition_id):array{
        $req = $this->requisitionService->getById($requisition_id);
        $req_items = $req->items()->get();
        $items = [];
        foreach($req_items as $req_item){
            $item = $this->itemService->getById($req_item->item_id);
            $units = $item->units->toArray();
            $unit_name = '';
            foreach($units as $index => $unit){
                if($unit['id'] == $req_item->unit_id){
                    $unit_name = $unit['name'];
                    break;
                }
            }
            $supplier_id = 0;
            if(count($item->supplier()->get('id')) > 0){
                $supplier_id = $item->supplier()->get('id')[0]->id;
            }
            $items[] = [
            'supplier_id' => $supplier_id,
            'requisition_item_id' => $req_item->id,
            'item_id' => $req_item->item_id,
            'name' => $item->name,
            'unit_id' => $req_item->unit_id,
            'unit_name' => $unit_name,
            'requested_quantity' => $req_item->quantity,
            'quantity' => $req_item->quantity,
            'requested_unit_price' => $req_item->unit_price,
            'unit_price' => $req_item->unit_price,
            'total' => $req_item->total,
            'used' => $req_item->in_purchase_or_order,
            ];
        }
        $requisition = [
            "requisition_id" => $requisition_id,
            "items" => $items,
        ];

        return $requisition;
    }

    public function loadUnpurchasedRequisitions(){
        $requisitions = $this->requisitionService->getUnpurchased()->toArray();
        foreach($requisitions as $index=>$req){
            $req = $this->requisitionService->getById($req['id']);
            $department = $req->department()->get('name')[0]->name;
            $requested_by = $req->requestedBy()->get('name')[0]->name;
            $requisitions[$index]['department_name'] = $department;
            $requisitions[$index]['requested_by_name'] = $requested_by;
        }
        return $requisitions;
    }

    public function markAsPurchased(int $requisition_id){
        $requisition = $this->requisitionService->getById($requisition_id);
        $ispurchased = true;
        $items = $requisition->items()->get();
        foreach($items as $item){
            if($item->in_purchase_or_order == false){
                $ispurchased = false;
                break;
            }
        }
        $requisition->update([
            'is_purchased' =>$ispurchased,
        ]);
    }

    public function saveCashPurchase(array $purchase_data,int $user_id){
        return DB::transaction(function () use ($purchase_data, $user_id) {

            $purchase = null;
            if($purchase_data['purchase']['id'] != null){
                $purchase = Purchase::find($purchase_data['purchase']['id']);
                if($purchase->is_received == true){
                    return $purchase;
                }
            }

            if (count($purchase_data['purchase']['items']) > 0) {

                $purchase = Purchase::updateOrCreate(['id' => $purchase_data['purchase']['id'] ?? null],[
                    'requisition_id' => $purchase_data['requisition']['requisition_id'],
                    'purchased_by_id' => $user_id,
                    'total_amount' => 0,
                ]);

                $total = 0;
                //remove items that are no longer in the purchase
                if($purchase_data['purchase']['id'] != null){
                    $keepIds = collect($purchase_data['purchase']['items'])
                        ->pluck('id')
                        ->filter()
                        ->values();

                    if($keepIds->count() > 0){
                        $toRemove = PurchaseItem::where('purchase_id', $purchase->id)
                        ->whereNotIn('id', $keepIds)
                        ->get();

                        $req = $this->requisitionService->getById($purchase_data['requisition']['requisition_id']);
                        $req_items = $req->items()->get();
                        foreach($toRemove as $toRemoveItem){
                            foreach ($req_items as $req_item) {
                                if($req_item->item_id == $toRemoveItem->item_id){
                                    $this->removeItemFromOrderOrPurchase($purchase->id,$toRemoveItem->id,$req_item->id,'purchase');
                                    break;
                                }
                            }
                        }
                    }
                }

                foreach ($purchase_data['purchase']['items'] as $item) {
                    $total += $item['total'];
                    PurchaseItem::updateOrCreate(['id' => $item['id'] ?? null],[
                        'purchase_id' => $purchase->id,
                        'item_id' => $item['item_id'],
                        'unit_id' => $item['unit_id'],
                        'requested_quantity' => $item['requested_quantity'],
                        'quantity' => $item['quantity'],
                        'requested_unit_price' => $item['requested_unit_price'],
                        'actual_unit_price' => $item['unit_price'],
                        'actual_total' => $item['total'],
                    ]);

                    if($item['requested_unit_price'] != $item['unit_price']){
                        $unit = $this->unitService->getById($item['unit_id']);
                        $unit->update([
                            'buying_price' => $item['unit_price']
                        ]);
                    }
                    //dd($item);
                    $requisition_item = RequisitionItem::find($item['requisition_item_id']);
                    //dd($requisition_item);
                    $requisition_item->update([
                        'in_purchase_or_order' => true,
                    ]);
                }

                $purchase->update([
                    'total_amount' => $total
                ]);

                $this->markAsPurchased($purchase_data['requisition']['requisition_id']);

            }
            return $purchase;
        });
    }

    public function saveSupplierOrder(array $order_data, int $user_id){
        return DB::transaction(function () use ($order_data, $user_id) {

            $order = null;
            if($order_data['order']['id'] != null){
                $order = SupplierOrder::find($order_data['order']['id']);
                if($order->is_received == true){
                    return $order;
                }
            }

            if (count($order_data['order']['items'])) {

                $order = SupplierOrder::updateOrCreate(['id' => $order_data['order']['id'] ?? null],[
                    'requisition_id' => $order_data['requisition']['requisition_id'],
                    'supplier_id' => $order_data['selected_supplier_id'],
                    'created_by' => $user_id,
                    'total_amount' => 0,
                ]);

                $total = 0;

                //remove items that are no longer in the purchase
                if($order_data['order']['id'] != null){
                    $keepIds = collect($order_data['order']['items'])
                        ->pluck('id')
                        ->filter()
                        ->values();

                    if($keepIds->count() > 0){
                        $toRemove = SupplierOrderItem::where('supplier_order_id', $order->id)
                        ->whereNotIn('id', $keepIds)
                        ->get();

                        $req = $this->requisitionService->getById($order_data['requisition']['requisition_id']);
                        $req_items = $req->items()->get();
                        foreach($toRemove as $toRemoveItem){
                            foreach ($req_items as $req_item) {
                                if($req_item->item_id == $toRemoveItem->item_id){
                                    $this->removeItemFromOrderOrPurchase($order->id,$toRemoveItem->id,$req_item->id,'order');
                                    break;
                                }
                            }
                        }
                    }
                }

                foreach ($order_data['order']['items'] as $item) {
                    $total += $item['total'];

                    SupplierOrderItem::updateOrCreate(['id' => $item['id'] ?? null],[
                        'supplier_order_id' => $order->id,
                        'item_id' => $item['item_id'],
                        'unit_id' => $item['unit_id'],
                        'requested_quantity' => $item['requested_quantity'],
                        'quantity' => $item['quantity'],
                        'requested_unit_price' => $item['requested_unit_price'],
                        'actual_unit_price' => $item['unit_price'],
                        'total' => $item['total'],
                    ]);

                    if($item['requested_unit_price'] != $item['unit_price']){
                        $unit = $this->unitService->getById($item['unit_id']);
                        $unit->update([
                            'buying_price' => $item['unit_price']
                        ]);
                    }

                    $requisition_item = RequisitionItem::find($item['requisition_item_id']);
                    $requisition_item->update([
                        'in_purchase_or_order' => true,
                    ]);
                }

                $order->update([
                    'total_amount' => $total,
                    'amount_pending' => $total,
                ]);
                $this->markAsPurchased($order_data['requisition']['requisition_id']);
            }
            return $order;
        });
    }
    /**
     *Remove item from saved requisition or order
     */
    public function removeItemFromOrderOrPurchase(
        int $purchase_or_order_id,int $purchase_or_order_item_id,int $requisition_item_id, string $type = 'purchase')
    {
        $requisition_item = RequisitionItem::find($requisition_item_id);
        $requisition_id = $requisition_item->requisition_id;
        $requisition_item->update([
            'in_purchase_or_order' => false,
        ]);

        if($type == 'purchase'){
            $purchase = Purchase::find($purchase_or_order_id);
            $purchase_item = PurchaseItem::find($purchase_or_order_item_id);
            $new_total = $purchase->total_amount - $purchase_item->actual_total;
            $purchase->update([
                'total_amount' => $new_total,
            ]);
            $purchase_item->delete();
        }else if($type == 'order'){
            $order = SupplierOrder::find($purchase_or_order_id);
            $order_item = SupplierOrderItem::find($purchase_or_order_item_id);
            $new_total = $order->total_amount - $order_item->total;
            $order->update([
                'total_amount' => $new_total,
                'amount_pending' => $new_total,
            ]);
            $order_item->delete();
        }

        $this->markAsPurchased($requisition_id);
    }
    /**
     * Method to add item to saved order or purchase
     */
    public function addItemToOrderOrPurchase(
        int $purchase_or_order_id, int $requisition_item_id, array $item_data, string $type = 'purchase')
    {
        $requisition_item = RequisitionItem::find($requisition_item_id);
        $requisition_id = $requisition_item->requisition_id;
        $requisition_item->update([
            'in_purchase_or_order' => true,
        ]);

        if($type == 'purchase'){
            $purchase = Purchase::find($purchase_or_order_id);
            $purchase_item = PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'item_id' => $item_data['item_id'],
                'unit_id' => $item_data['unit_id'],
                'requested_quantity' => $item_data['requested_quantity'],
                'quantity' => $item_data['quantity'],
                'requested_unit_price' => $item_data['requested_unit_price'],
                'actual_unit_price' => $item_data['actual_price'],
                'actual_total' => $item_data['total'],
            ]);
            $new_total = $purchase->total_amount + $purchase_item->actual_total;
            $purchase->update([
                'total_amount' => $new_total
            ]);
        }else if($type == 'order'){
            $order = SupplierOrder::find($purchase_or_order_id);
            $order_item = SupplierOrderItem::create([
                'supplier_order_id' => $order->id,
                'item_id' => $item_data['item_id'],
                'unit_id' => $item_data['unit_id'],
                'requested_quantity' => $item_data['requested_quantity'],
                'quantity' => $item_data['quantity'],
                'requested_unit_price' => $item_data['requested_unit_price'],
                'actual_unit_price' => $item_data['actual_price'],
                'total' => $item_data['total']
            ]);
            $new_total = $order->total_amount + $order_item->total;
            $order->update([
                'total_amount' => $new_total
            ]);
        }
        $requisition_item = RequisitionItem::find($item_data['requisition_item_id']);
        $requisition_item->update([
            'in_purchase_or_order' => true,
        ]);
        $this->markAsPurchased($requisition_id);
    }

    /**
     * Method to add payment to supplier order
     */
    public function registerPayment($orderId, $amount, $userId, $reference)
    {
        return DB::transaction(function () use ($orderId, $amount, $userId, $reference) {

            $order = SupplierOrder::findOrFail($orderId);

            SupplierOrderPayments::create([
                'supplier_order_id' => $orderId,
                'amount' => $amount,
                'paid_by' => $userId,
                'reference' => $reference,
            ]);

            $order->amount_paid += $amount;
            $order->amount_pending = $order->total_amount - $order->amount_paid;

            if ($order->amount_pending <= 0) {
                $order->payment_status = 'paid';
                $order->amount_pending = 0;
            } elseif ($order->amount_paid > 0) {
                $order->payment_status = 'partial';
            }

            $order->save();

            return $order;
        });
    }

    public function loadSuppliers(){
        return Supplier::all('id','name')->toArray();
    }

    public function delete(int $id, string $type = "purchase"){
        if($type == "purchase"){
            $purchase = $this->getInstanceById($id,$type);
            $p_items = $purchase->items();
            $requisition = $this->requisitionService->getById($purchase->requisition_id);
            $req_items = $requisition->items();
            foreach($req_items as $req_item){
                foreach($p_items as $p_item){
                    if($p_item->is_received == true){
                        return;
                    }
                    if($p_item->item_id == $req_item->item_id){
                        $req_item->update(
                            ['in_purchase_or_order' => false]
                        );
                        break;
                    }
                }
            }
            $this->markAsPurchased($requisition->id);
            $purchase->delete();
        }else{
            $order = $this->getInstanceById($id,$type);
            $o_items = $order->items();
            $requisition = $this->requisitionService->getById($order->requisition_id);
            $req_items = $requisition->items();
            foreach($req_items as $req_item){
                foreach($o_items as $o_item){
                    if($o_item->is_received == true){
                        return;
                    }
                    if($o_item->item_id == $req_item->item_id){
                        $req_item->update(
                            ['in_purchase_or_order' => false]
                        );
                        break;
                    }
                }
            }
            $this->markAsPurchased($requisition->id);
            $order->items()->delete();
            $order->delete();
        }
    }
}
