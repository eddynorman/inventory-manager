<?php

namespace App\Services;

use App\Models\Location;
use App\Models\ItemLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LocationService
{
    public function rules(?int $locationId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Location::class, 'name')->ignore($locationId)],
            'locationType' => ['required', 'in:warehouse,store,office'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'staffResponsible' => ['nullable', 'integer', 'exists:users,id'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function save(?int $id, array $data): Location
    {
        $data['location_type'] = $data['locationType'];
        $data['staff_responsible'] = $data['staffResponsible'];
        
        return Location::updateOrCreate(['id' => $id], $data);
    }

    public function delete(int $id): void
    {
        Location::where('id', $id)->delete();
    }

    public function getWithItems(int $id): Location
    {
        return Location::with(['items'])->findOrFail($id);
    }

    public function moveItem(int $itemLocationId, int $targetLocationId, int $quantity): void
    {
        $itemLocation = ItemLocation::findOrFail($itemLocationId);

        if ($quantity > $itemLocation->stock) {
            throw new \InvalidArgumentException("Quantity exceeds available stock.");
        }

        // Deduct from source
        $itemLocation->decrement('stock', $quantity);

        // Add to target
        ItemLocation::updateOrCreate(
            ['item_id' => $itemLocation->item_id, 'location_id' => $targetLocationId],
            ['stock' => DB::raw("stock + $quantity")]
        );
    }
}
