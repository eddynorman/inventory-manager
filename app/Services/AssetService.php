<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\AssetInventoryDamagedItems;
use App\Models\AssetInventoryItems;
use App\Models\AssetInventoryPurchaseItems;
use App\Models\AssetInventoryPurchases;
use Illuminate\Validation\Rule;

class AssetService
{
    public function rules(?int $itemId = null){
        return [
            'form' => ['required','array','min:5'],
            'form.initial_purchase_date' => ['required','date','before_or_equal:today'],
            'form.department_id' => ['required','integer','exists:departments,id'],
            'form.name' => ['required', Rule::unique('asset_inventory_items','name')->ignore($itemId)],
            'form.initial_quantity' => 'required|numeric|min:0',
            'form.initial_unit_cost' => 'required|numeric|min:0',
        ];
    }
    public function messages()
    {
        return [
            'form.required' => 'The form data is required.',
            'form.array' => 'Invalid form submission.',
            'form.min' => 'Form is incomplete.',

            'form.department_id.required' => 'Please select a department.',
            'form.department_id.exists' => 'Selected department is invalid.',

            'form.name.required' => 'Asset name is required.',
            'form.name.unique' => 'This asset already exists.',

            'form.initial_purchase_date.required' => 'The date is required.',
            'form.initial_purchase_date.date' => 'Purchase date must be a date.',
            'form.initial_purchase_date.before_or_equal' => 'Date cannot be in the future.',

            'form.initial_quantity.required' => 'Initial quantity is required.',
            'form.initial_quantity.numeric' => 'Quantity must be a number.',
            'form.initial_quantity.min' => 'Quantity cannot be negative.',

            'form.initial_unit_cost.required' => 'Initial cost is required.',
            'form.initial_unit_cost.numeric' => 'Cost must be numeric.',
            'form.initial_unit_cost.min' => 'Cost cannot be negative.',
        ];
    }

    public function purchaseRules()
    {
        return [
            'purchaseItems' => ['required','array','min:1'],

            'purchaseItems.*.item_id' => [
                'required','exists:asset_inventory_items,id'
            ],

            'purchaseItems.*.quantity' => [
                'required','numeric','min:1'
            ],

            'purchaseItems.*.unit_cost' => [
                'required','numeric','min:0'
            ],
        ];
    }

    public function damageRules()
    {
        return [
            'damage.item_id' => ['required','exists:asset_inventory_items,id'],
            'damage.quantity' => ['required','numeric','min:1'],
            'damage.notes' => ['required','string','max:500'],
        ];
    }
    public function damageMessages()
    {
        return [
            'damage.quantity.min' => 'Damaged quantity must be at least 1.',
            'damage.item_id.exists' => 'Invalid asset item.',
        ];
    }
    /**
     * Create new asset item
     */
    public function createItem(array $data)
    {
        return AssetInventoryItems::create([
            ...$data['form'],
            'current_quantity' => $data['form']['initial_quantity'],
            'average_unit_cost' => $data['form']['initial_unit_cost'],
        ]);
    }

    /**
     * Record Purchase
     */
    public function recordPurchase(array $items)
    {
        return DB::transaction(function () use ($items) {

            $purchase = AssetInventoryPurchases::create([
                'total' => 0
            ]);

            $total = 0;

            foreach ($items as $itemData) {

                $item = AssetInventoryItems::findOrFail($itemData['item_id']);

                $qty = $itemData['quantity'];
                $cost = $itemData['unit_cost'];

                AssetInventoryPurchaseItems::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $item->id,
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                ]);

                // 🔥 Weighted Average Cost
                $newAvg = (
                    ($item->current_quantity * $item->average_unit_cost)
                    + ($qty * $cost)
                ) / ($item->current_quantity + $qty);

                $item->update([
                    'purchased_quantity' => $item->purchased_quantity + $qty,
                    'current_quantity' => $item->current_quantity + $qty,
                    'average_unit_cost' => $newAvg,
                ]);

                $total += $qty * $cost;
            }

            $purchase->update(['total' => $total]);

            return $purchase;
        });
    }

    /**
     * Record Damaged Items
     */
    public function recordDamage(array $data)
    {
        return DB::transaction(function () use ($data) {

            $item = AssetInventoryItems::findOrFail($data['damage']['item_id']);

            if ($item->current_quantity < $data['damage']['quantity']) {
                throw new \Exception('Insufficient asset quantity.');
            }

            AssetInventoryDamagedItems::create([
                'item_id' => $item->id,
                'quantity' => $data['damage']['quantity'],
                'average_unit_cost' => $item->average_unit_cost,
                'notes' => $data['damage']['notes'] ?? null,
            ]);

            $item->update([
                'current_quantity' => $item->current_quantity - $data['damage']['quantity'],
            ]);

            return true;
        });
    }

    /**
     * Get Items
     */
    public function getItems()
    {
        return AssetInventoryItems::with('department')->latest()->get();
    }

    public function searchItem(string $search)
    {
        return AssetInventoryItems::where('name', 'like', "%{$search}%")
            ->limit(5)
            ->get();
    }

}
