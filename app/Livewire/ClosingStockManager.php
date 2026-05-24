<?php

namespace App\Livewire;

use App\Models\ClosingStockSession;
use App\Models\Item;
use App\Models\Location;
use App\Services\ClosingStockService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClosingStockManager extends Component
{
    public $locationId;
    public $items = [];
    public $locations;

    public function boot()
    {
        $this->locations = Location::all();
    }

    public function updatedLocationId()
    {
        if (!$this->locationId) {
            $this->items = [];
            return;
        }

        if ($this->getIsClosedTodayProperty()) {
            session()->flash('error', "Stock closed for this location");
            $this->dispatch('flash');
            $this->items = [];
            return;
        }

        $this->loadItems();
    }

    public function loadItems()
    {
        $this->items = Item::where('is_auto_tracked', false)
            ->where('is_stock_item', true)
            ->whereHas('locationItems', function ($q) {
                $q->where('location_id', $this->locationId);
            })
            ->with(['locationItems' => function ($q) {
                $q->where('location_id', $this->locationId);
            }])
            ->get()
            ->map(function ($item) {
                $stock = (float)$item->locationItems->first()->quantity;
                return [
                    'item_id'       => $item->id,
                    'name'          => $item->name,
                    'opening_stock' => $stock,
                    'closing_stock' => $stock,
                    'used'          => 0,
                ];
            })->toArray();

        if (count($this->items) === 0) {
            session()->flash('warning', 'No manual stock items found!');
            $this->dispatch('flash');
        }

        // Send a clean event down to Alpine to update its internal state instantly
        $this->dispatch('items-loaded', items: $this->items);
    }

    public function getIsClosedTodayProperty()
    {
        return ClosingStockSession::where('location_id', $this->locationId)
            ->whereDate('date', now())
            ->exists();
    }

    public function save($clientItems)
    {
        // $clientItems is passed up from Alpine securely via wire:click
        $this->items = $clientItems;

        // Perform standard backend validation before saving
        $this->validate([
            'locationId' => 'required|exists:locations,id',
            'items.*.closing_stock' => 'required|numeric|min:0',
        ], [
            'locationId.required' => 'Location is Required',
            'items.*.closing_stock.required' => 'Enter closing stock',
            'items.*.closing_stock.numeric' => 'Must be a number',
        ]);

        // Cross-verify max stock restrictions on server for safety
        foreach ($this->items as $item) {
            if ((float)$item['closing_stock'] > (float)$item['opening_stock']) {
                $this->addError('locationId', "Closing stock cannot exceed opening stock for " . $item['name']);
                return;
            }
        }

        if (count($this->items) === 0) {
            session()->flash('error', 'No items found!');
            $this->dispatch('flash');
            return;
        }

        app(ClosingStockService::class)->process(
            $this->items,
            $this->locationId,
            Auth::id()
        );

        session()->flash('success', 'Closing stock saved successfully');
        $this->dispatch('flash');

        $this->reset(['items', 'locationId']);
        $this->dispatch('items-loaded', items: []);
    }

    public function render()
    {
        return view('livewire.closing-stock-manager');
    }
}
