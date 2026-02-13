<?php

namespace App\Services;

use App\Models\ItemKit;
use App\Models\ItemKitItem;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Validation\Rule;

class ItemKitService
{
    public function rules(?int $kitId = null ): array
    {
        return [
            'name' => ['required','string','max:255',Rule::unique('item_kits')->ignore($kitId)],
            'description' => ['nullable','string'],
            'selling_price' => ['required','numeric','min:0'],
            'selling_price_includes_tax' => ['boolean'],
            'items' => ['required','array'],
            'items.*.id' => ['nullable','exists:item_kit_items,id'],
            'items.*.item_id' => ['required','exists:items,id'],
            'items.*.quantity' => ['required','numeric','min:0.1'],
            'items.*.selected_unit_id' => ['required','exists:units,id'],
        ];
    }

    public function save(?int $kitId,array $data): void
    {
        foreach ($data['items'] as $index => $item) {
            $data['items'][$index]['unit_id'] = $item['selected_unit_id'];
            unset($data['items'][$index]['selected_unit_id']);
        }
        FacadesDB::transaction(function () use( $data, $kitId){
            $kitData = collect($data)->except('items')->toArray();
            $kit = ItemKit::updateOrCreate(['id' => $kitId], $kitData);
            $keepIds = collect($data['items'])
                ->pluck('id')
                ->filter()      // removes nulls (new rows)
                ->values();

            ItemKitItem::where('item_kit_id', $kit->id)
                ->whereNotIn('id', $keepIds)
                ->delete();

            foreach($data['items'] as $item){

                ItemKitItem::updateOrCreate(
                    ['id' => $item['id'] ?? null],
                    [
                        'item_id'     => $item['item_id'],
                        'quantity'    => $item['quantity'],
                        'unit_id'     => $item['unit_id'],
                        'item_kit_id' => $kit->id,
                    ]
                );

            }
        });
    }
    public function delete(int $id): void
    {
        ItemKit::where('id', $id)->delete();
    }

    public function bulkDelete(array $ids){
        ItemKit::whereIn('id',$ids)->delete();
    }
    public function getById(int $id): ItemKit
    {
        return ItemKit::findOrFail($id);
    }
    public function getItems(int $kitId): array
    {
        return ItemKit::findOrFail($kitId)
        ->kitItems()
        ->with(['item','unit'])
        ->get()
        ->map(function ($kitItem) {

            return [
                'id' => $kitItem->id,
                'item_id' => $kitItem->item_id,
                'name' => $kitItem->item->name,
                'quantity' => $kitItem->quantity,
                'selected_unit_id' => $kitItem->unit_id,
            ];

        })
        ->toArray();
    }
}
