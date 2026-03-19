<?php

namespace App\Livewire;

use App\Models\Item;
use App\Services\StockAdjustmentService;
use Livewire\Component;

class StockAdjustmentManager extends Component
{
    public ?int $location_id = null;
    public array $locations = [];
    public $viewAdjustmentData = null;
    public string $search = '';
    public array $item_list = [];
    public array $adjustment_items = [];
    public array $validated_data = [];
    public string $description = '';

    public bool $showIndexPage = true;
    public bool $showAdjustmentCreationPage = false;
    public bool $showConfirmSave = false;
    public bool $showViewAdjustment = false;

    private StockAdjustmentService $service;

    protected $listeners = [
        'viewAdjustment' => 'view',
    ];

    public function boot(StockAdjustmentService $service){
        $this->service = $service;
        $this->locations = $this->service->loadLocations()->toArray();
    }

    public function resetForm(){
        $this->reset(['location_id','search']);
    }

    public function updatedSearch(){
        $this->search = trim($this->search);
        $this->item_list = [];
        if($this->search != '' && $this->search != null ){
            $temp = $this->searchItems()->toArray();
            foreach ($temp as $temp_i) {
                $the_item = [];
                $the_item['item_id'] = $temp_i['id'];
                $the_item['name'] = $temp_i['name'];
                $the_item['quantity'] = $temp_i['location_items'][0]['quantity'];
                $the_item['adjustment_qty'] = 0;
                $the_item['id'] = $temp_i['location_items'][0]['id'];
                $the_item['reason'] = "";
                $this->item_list[] = $the_item;
            }
            //dd($this->item_list);
        }else{
            $this->item_list = [];
        }
    }

    public function searchItems()
    {
        if (!$this->location_id || empty($this->search)) {
            return collect();
        }

        return Item::query()
            ->select('id', 'name',)
            ->where('name', 'like', "%{$this->search}%")
            ->whereHas('locationItems', function ($q) {
                $q->where('location_id', $this->location_id);
            })
            ->with(['locationItems' => function ($q) {
                $q->select('id', 'item_id','location_id','quantity')->where('location_id', $this->location_id);
            }])
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $item->current_stock = optional($item->locationItems->first())->quantity ?? 0;
                return $item;
            });
    }

    public function create(){
        $this->showAdjustmentCreationPage = true;
        $this->showIndexPage = false;
    }

    public function addItem(int $index){
        $item = $this->item_list[$index];

        foreach ($this->adjustment_items as $existing) {
            if ($existing['id'] == $item['id']) {
                return;
            }
        }
        $this->adjustment_items[] = $item;
        $this->search = '';
        $this->item_list = [];
    }

    public function removeItem(int $index){
        array_splice($this->adjustment_items,$index,1);
        $this->search = '';
        $this->item_list = [];
    }

    public function validateAdjustments(){
        foreach ($this->adjustment_items as $key => $item) {
            $newStock = $item['quantity'] + $item['adjustment_qty'];

            if ($newStock < 0) {
                $this->addError(
                    "adjustment_items.$key.adjustment_qty",
                    "Adjustment exceeds available stock. Cannot go below zero."
                );
            }
        }

    }

    public function confirmSave(){
        $this->resetErrorBag();
        $this->validateAdjustments();
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }
        $this->validated_data = $this->validate($this->service->rules());
        $this->showConfirmSave = true;
    }

    public function refreshTable(){
        $this->dispatch('pg:eventRefresh-stock-adjustment-table-qovu0z-table');
    }

    public function save(){
        try {
            $this->service->save($this->validated_data);
            $this->reset();
            $this->refreshTable();
            $this->showAdjustmentCreationPage = false;
            $this->showIndexPage = true;
            session()->flash('success','Adjustment saved Successfully!');
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            dd($th);
            session()->flash('error','Fatal Error Occured!');
            $this->dispatch('flash');
        }
    }

    public function view(int $id){
        $this->viewAdjustmentData = $this->service->getById($id);
        $this->showIndexPage = false;
        $this->showViewAdjustment = true;
    }

    public function updatedShowViewAdjustment(){
        if($this->showViewAdjustment == false){
            $this->showIndexPage = true;
        }
    }
    public function updatedShowAdjustmentCreationPage(){
        if($this->showAdjustmentCreationPage == false){
            $this->showIndexPage = true;
            $this->reset();
        }
    }
    public function render()
    {
        return view('livewire.stock-adjustment-manager');
    }
}
