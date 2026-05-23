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
    public $totals = [
        'opening' => 0,
        'used' => 0,
        'closing' => 0,
    ];

    public function boot(){
        $this->locations = Location::all();
    }

    protected function rules()
    {
        return [
            'locationId' => 'required|exists:locations,id',
            'items.*.closing_stock' => 'required|numeric|min:0',
        ];
    }

    protected function messages()
    {
        return [
            'locationId.required' => 'Location is Required',
            'items.*.closing_stock.required' => 'Enter closing stock',
            'items.*.closing_stock.numeric' => 'Must be a number',
        ];
    }

    public function updatedLocationId()
    {
        $closed = $this->getIsClosedTodayProperty();
        if(!$closed){
            $this->loadItems();
        }else{
            session()->flash('error',"Stock closed for this location");
            $this->dispatch('flash');
        }

    }

    public function loadItems()
    {
        $this->items = [];
        $this->items = Item::where('is_auto_tracked', false)
            ->where('is_stock_item',true)
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
                    'item_id' => $item->id,
                    'name' => $item->name,
                    'opening_stock' => $stock,
                    'closing_stock' => $stock,
                    'used' => 0,
                ];
            })->toArray();

            $this->totals = [
                'opening' => collect($this->items)->sum('opening_stock'),
                'used' => 0,
                'closing' => collect($this->items)->sum('closing_stock'),
            ];

            if(count($this->items) == 0){
                session()->flash('warning', 'No manual stock items found!');
                $this->dispatch('flash');
            }
    }

    public function updatedItems($value, $key)
    {
        [$index, $field] = explode('.', $key);

        if ($field !== 'closing_stock') {
            return;
        }

        $opening = (float) $this->items[$index]['opening_stock'];

        $oldClosing = (float) ($this->items[$index]['closing_stock'] ?? 0);

        $closing = (float) ($value ?: 0);

        if ($closing < 0) {

            $closing = 0;

            $this->items[$index]['closing_stock'] = 0;
        }

        if ($closing > $opening) {

            $this->addError(
                "items.$index.closing_stock",
                "Cannot exceed opening stock"
            );

            return;
        }

        $this->resetErrorBag("items.$index.closing_stock");

        $used = max(0, $opening - $closing);

        /*
        |--------------------------------------------------------------------------
        | UPDATE ROW
        |--------------------------------------------------------------------------
        */

        $previousUsed = (float) $this->items[$index]['used'];

        $this->items[$index]['used'] = $used;

        /*
        |--------------------------------------------------------------------------
        | UPDATE TOTALS INCREMENTALLY
        |--------------------------------------------------------------------------
        */

        $this->totals['closing']
            += ($closing - $oldClosing);

        $this->totals['used']
            += ($used - $previousUsed);
    }

    public function getIsClosedTodayProperty()
    {
        return ClosingStockSession::where('location_id', $this->locationId)
            ->whereDate('date', now())
            ->exists();
    }


    public function save()
    {
        $this->validate($this->rules(),$this->messages());
        if(count($this->items) == 0){
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

        $this->reset(['items','locationId','totals']); // reset fresh
    }

    public function render()
    {
        return view('livewire.closing-stock-manager');
    }
}
