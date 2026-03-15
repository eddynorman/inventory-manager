<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\SupplierOrder;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use Illuminate\Support\Facades\DB;

class ReceivingService
{
    public function loadUnreceivedPurchases()
    {
        return Purchase::where('is_received', false)->orderByDesc('id')->get(['id','requisition_id','purchased_by_id','total_amount']);
    }

    public function loadUnreceivedOrders()
    {
        return SupplierOrder::where('is_received', false)->orderByDesc('id')->get(['id','requisition_id','supplier_id','total_amount','amount_pending']);
    }

    public function loadItemsFor(string $type, int $id): array
    {
        $items = [];
        if ($type === 'purchase') {
            $purchase = Purchase::with('items')->findOrFail($id);
            foreach ($purchase->items as $it) {
                $items[] = [
                    'item_id' => $it->item_id,
                    'unit_id' => $it->unit_id,
                    'quantity' => $it->quantity,
                    'unit_price' => $it->actual_unit_price ?? $it->requested_unit_price,
                    'total' => $it->actual_total ?? ($it->quantity * ($it->actual_unit_price ?? $it->requested_unit_price)),
                ];
            }
        } else {
            $order = SupplierOrder::with('items')->findOrFail($id);
            foreach ($order->items as $it) {
                $items[] = [
                    'item_id' => $it->item_id,
                    'unit_id' => $it->unit_id,
                    'quantity' => $it->quantity,
                    'unit_price' => $it->actual_unit_price ?? $it->requested_unit_price,
                    'total' => $it->total ?? ($it->quantity * ($it->actual_unit_price ?? $it->requested_unit_price)),
                ];
            }
        }
        return $items;
    }

    public function saveReceiving(array $data, int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $receiving = Receiving::create([
                'purchase_id' => $data['type'] === 'purchase' ? $data['source_id'] : null,
                'supplier_order_id' => $data['type'] === 'order' ? $data['source_id'] : null,
                'received_by_id' => $userId,
                'location_id' => $data['location_id'] ?? null,
            ]);

            $total = 0;
            foreach ($data['items'] as $it) {
                $t = floatval($it['quantity']) * floatval($it['unit_price']);
                ReceivingItem::create([
                    'receiving_id' => $receiving->id,
                    'item_id' => $it['item_id'],
                    'unit_id' => $it['unit_id'],
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'total' => $t,
                ]);
                $total += $t;
            }

            // mark source as received
            if ($receiving->purchase_id) {
                $p = Purchase::find($receiving->purchase_id);
                if ($p) {
                    $p->is_received = true;
                    $p->save();
                }
            }
            if ($receiving->supplier_order_id) {
                $o = SupplierOrder::find($receiving->supplier_order_id);
                if ($o) {
                    $o->is_received = true;
                    $o->save();
                }
            }

            return $receiving;
        });
    }

    public function deleteReceiving(int $id)
    {
        $receiving = Receiving::findOrFail($id);
        // allow deletion only for admins is enforced by caller
        return DB::transaction(function () use ($receiving) {
            $receiving->items()->delete();
            $receiving->delete();
            return true;
        });
    }
}
