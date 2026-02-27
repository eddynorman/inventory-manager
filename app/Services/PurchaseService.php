<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\RequisitionItem;
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

    public function loadRequisitionData($requisition_id):array{
        $req = $this->requisitionService->getById($requisition_id);
        $req_items = $req->items();
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
            $items[] = [
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
            ];
        }
        $requisition[] = [
            "requisition_id" => $requisition_id,
            "items" => $items,
        ];

        return $requisition;
    }

    public function loadUnpurchasedRequisitions(){
        return $this->requisitionService->getUnpurchased()->toArray();
    }

    public function saveCashPurchase(array $purchase_data,int $user_id){
        return DB::transaction(function () use ($purchase_data, $user_id) {

            $purchase = null;

            if ($purchase_data['items']->count()) {

                $purchase = Purchase::create([
                    'requisition_id' => $purchase_data['requisition_id'],
                    'created_by' => $user_id,
                    'total_amount' => 0,
                ]);

                $total = 0;

                foreach ($purchase_data['items'] as $item) {
                    $total += $item['total'];

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'item_id' => $item['item_id'],
                        'unit_id' => $item['unit_id'],
                        'requested_quantity' => $item['requested_quantity'],
                        'quantity' => $item['quantity'],
                        'requested_unit_price' => $item['requested_unit_price'],
                        'actual_unit_price' => $item['actual_price'],
                        'actual_total' => $item['total'],
                    ]);

                    if($item['requested_unit_price'] != $item['actual_unit_price']){
                        $unit = $this->unitService->getById($item['unit_id']);
                        $unit->update([
                            'buying_price' => $item['actual_unit_price']
                        ]);
                    }

                    $requisition_item = RequisitionItem::find($item['requisition_item_id']);
                    $requisition_item->update([
                        'in_purchase_or_order' => true,
                    ]);
                }

                $purchase->update([
                    'total_amount' => $total
                ]);

            }
            return $purchase;
        });
    }

    public function saveSupplierOrder(array $order_data, int $user_id){
        return DB::transaction(function () use ($order_data, $user_id) {

            $order = null;

            if ($order_data['items']->count()) {

                $order = SupplierOrder::create([
                    'requisition_id' => $order_data['requisition_id'],
                    'created_by' => $user_id,
                    'total_amount' => 0,
                ]);

                $total = 0;

                foreach ($order_data['items'] as $item) {
                    $total += $item['total'];

                    SupplierOrderItem::create([
                        'supplier_order_id' => $order->id,
                        'item_id' => $item['item_id'],
                        'unit_id' => $item['unit_id'],
                        'requested_quantity' => $item['requested_quantity'],
                        'quantity' => $item['quantity'],
                        'requested_unit_price' => $item['requested_unit_price'],
                        'actual_unit_price' => $item['actual_price'],
                        'total' => $item['total'],
                    ]);

                    if($item['requested_unit_price'] != $item['actual_unit_price']){
                        $unit = $this->unitService->getById($item['unit_id']);
                        $unit->update([
                            'buying_price' => $item['actual_unit_price']
                        ]);
                    }

                    $requisition_item = RequisitionItem::find($item['requisition_item_id']);
                    $requisition_item->update([
                        'in_purchase_or_order' => true,
                    ]);
                }

                $order->update([
                    'total_amount' => $total
                ]);

            }
            return $order;
        });
    }
    /**
     *Remove item from saved requisition or order
     */
    public function removeItemFromOrderOrPurchase(
        string $type = 'purchase',int $purchase_or_order_id,int $purchase_or_order_item_id,
        int $requisition_item_id)
    {
        $requisition_item = RequisitionItem::find($requisition_item_id);
        $requisition_item->update([
            'in_purchase_or_order' => false,
        ]);

        if($type == 'purchase'){
            $purchase = Purchase::find($purchase_or_order_id);
            $purchase_item = PurchaseItem::find($purchase_or_order_item_id);
            $new_total = $purchase->total_amount - $purchase_item->actual_total;
            $purchase->update([
                'total_amount' => $new_total
            ]);
            $purchase_item->delete();
        }else if($type == 'order'){
            $order = SupplierOrder::find($purchase_or_order_id);
            $order_item = SupplierOrderItem::find($purchase_or_order_item_id);
            $new_total = $order->total_amount - $order_item->total;
            $order->update([
                'total_amount' => $new_total
            ]);
            $order_item->delete();
        }
    }
    /**
     * Method to add item to saved order or purchase
     */
    public function addItemToOrderOrPurchase(
        string $type = 'purchase',int $purchase_or_order_id, int $requisition_item_id, array $item_data)
    {
        $requisition_item = RequisitionItem::find($requisition_item_id);
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
}
