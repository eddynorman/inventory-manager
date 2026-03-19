<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\ReceivingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReceivingManager extends Component
{
    protected $listeners = [
        'viewReceiving' => 'viewReceiving',
        'deleteReceiving' => 'confirmDelete',
    ];

    public ?int $selected_source_id = null;
    public ?int $location_id = null;
    public array $locations = [];
    public string $type = 'purchase';
    public array $source = [];
    public array $receiving = [];
    public ?int $receivingId = null;
    public array $orders = [];
    public array $purchases = [];
    public $r = null; //to hold viewing data


    public bool $showIndexPage = true;
    public bool $showViewReceivingPage = false;
    public bool $showCreateReceivingPage = false;
    public bool $showConfirmDelete = false;
    private ReceivingService $receivingService;
    private PurchaseService $purchaseService;


    public function boot(ReceivingService $receivingService, PurchaseService $purchaseService)
    {
        $this->receivingService = $receivingService;
        $this->purchaseService = $purchaseService;
        $this->locations = Location::all()->toArray();
    }

    public function updatedReceiving($value,$key){
        $index =  explode('.', $key)[1];
        $field =  explode('.', $key)[2];

        //dd("called uo", $key);
        if($field == 'received_quantity'){
            if($value == '' || $value == null){
                $this->receiving['items'][$index]['total'] = 0;
                $sum = 0;
                foreach( $this->receiving['items'] as $item){
                    $sum += $item['total'];
                }
                $this->receiving['grand_total'] = $sum;
                return;
            }
            $this->receiving['items'][$index]['total'] = $this->receiving['items'][$index]['unit_price'] * $this->receiving['items'][$index]['received_quantity'];
            $sum = 0;
            foreach( $this->receiving['items'] as $item){
                $sum += $item['total'];
            }
            $this->receiving['grand_total'] = $sum;

            $this->reduceItem($index);
        }
    }

    public function updatedSelectedSourceId()
    {
        if($this->selected_source_id != null){
            $this->source = $this->receivingService->loadSource($this->type,$this->selected_source_id);
            $this->receiving['grand_total'] = 0;
            $available = [];
            if($this->type == 'order'){
                foreach ($this->source['items'] as $key => $it) {
                    $this->source['items'][$key]['unit_price'] = $it['actual_unit_price'];
                    unset($this->source['items'][$key]['actual_unit_price']);
                }
            }else{
                foreach ($this->source['items'] as $key => $it) {
                    $this->source['items'][$key]['unit_price'] = $it['actual_unit_price'];
                    $this->source['items'][$key]['total'] = $it['actual_total'];
                    unset($this->source['items'][$key]['actual_unit_price']);
                    unset($this->source['items'][$key]['actual_total']);
                }
            }
            foreach($this->source['items'] as $item){
                if($item['quantity'] != $item['received_quantity']){
                    $available[] = $item;
                }
            }
            $this->source['available'] = $available;
            $offset = 0;
            foreach ($this->source['available'] as $key => $value) {
                $this->addReceivingItem($key-$offset);
                $offset++;
            }
        }else{
            $this->source = [];
        }
    }
    public function addReceivingItem(int $index){
        $item = $this->source['available'][$index];
        $exists = false;
        if(isset($this->receiving['items'])){
            foreach($this->receiving['items'] as $key => $value){
                if($value['item_id'] == $item['item_id']){
                    $exists = true;
                    $this->receiving['items'][$key]['received_quantity'] += $item['quantity'] - $item['received_quantity'];
                }
            }
        }else{
            $this->receiving['items'] = [];
        }
        if($exists == false){
            $item['received_quantity'] = $item['quantity'] - $item['received_quantity'];
            $this->receiving['items'][] = $item;
        }
        array_splice($this->source['available'],$index,1);
        $sum = 0;
        foreach( $this->receiving['items'] as $item){
            $sum += $item['total'];
        }
        $this->receiving['grand_total'] = $sum;
    }

    public function reduceItem(int $index){
        $item = $this->receiving['items'][$index];
        $exists = false;
        foreach($this->source['available'] as $key => $value){
            if($value['item_id'] == $item['item_id']){
                $exists = true;
                $this->source['available'][$key]['received_quantity'] = $item['received_quantity'];
            }
        }
        if($exists == false){
            $this->source['available'][] = $item;
        }

        $offset = 0;
        foreach($this->source['available'] as $key => $value){
            if($value['quantity'] - $value['received_quantity'] == 0){
                array_splice($this->source['available'],$key - $offset,1);
                $offset++;
            }
        }
    }

    public function removeItem(int $index){
        $item = $this->receiving['items'][$index];
        $exists = false;
        foreach($this->source['available'] as $key => $value){
            if($value['item_id'] == $item['item_id']){
                $exists = true;
                $this->source['available'][$key]['received_quantity'] = 0;
            }
        }
        if($exists == false){
            $this->source['available'][] = $item;
        }
        array_splice($this->receiving['items'],$index,1);
        $sum = 0;
        foreach( $this->receiving['items'] as $item){
            $sum += $item['total'];
        }
        $this->receiving['grand_total'] = $sum;
    }

    public function updatedType()
    {
        // dd("called type");
        // dd("updated to ", $this->type);
        if($this->type == 'purchase'){
            $this->purchases = $this->receivingService->loadUnreceivedPurchases()->toArray();
            foreach ($this->purchases as $i => $p) {
                $this->purchases[$i]['department_name'] = $this->receivingService->loadDepartmentName($p['id'],$this->type);
            }
        }else if($this->type == 'order'){
            $this->orders = $this->receivingService->loadUnreceivedOrders()->toArray();
            foreach ($this->orders as $i => $o) {
                $this->orders[$i]['department_name'] = $this->receivingService->loadDepartmentName($o['id'],$this->type);
            }
        }

        $this->receiving = [];
        $this->source = [];
    }

    public function resetForm(): void
    {
        $this->reset([
            'selected_source_id',
            'location_id',
            'locations',
            'type',
            'source',
            'receiving',
            'receivingId',
            'orders',
            'purchases',
            'r',
        ]);
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->purchases = $this->receivingService->loadUnreceivedPurchases()->toArray();
        foreach ($this->purchases as $i => $p) {
            $this->purchases[$i]['department_name'] = $this->receivingService->loadDepartmentName($p['id'],$this->type);
        }
        $this->showIndexPage = false;
        $this->showCreateReceivingPage = true;
    }

    public function edit(int $id): void
    {
        // editing is not allowed once created/received
        session()->flash('error','Receiving records cannot be edited. Create a new receiving if needed.');
        $this->dispatch('flash');
    }

    public function save(): void
    {
        $data = $this->validate($this->receivingService->rules($this->type));
        try {
            $this->receivingService->saveReceiving($data, Auth::id());
            $this->showCreateReceivingPage = false;
            $this->showIndexPage = true;
            $this->resetForm();
            session()->flash('success', 'Receiving recorded.');
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            dd($th);
            session()->flash('error', 'Fatal error while recording receiving.');
            $this->dispatch('flash');
        }
    }

    public function updatedShowCreateReceivingPage(){
        if($this->showCreateReceivingPage == false){
            $this->showIndexPage = true;
            $this->resetForm();
        }
    }

    public function updatedShowViewReceivingPage(){
        if($this->showViewReceivingPage == false){
            $this->showIndexPage = true;
            $this->resetForm();
        }
    }

    public function viewReceiving(int $id)
    {
        $this->receivingId = $id;
        $this->r = $this->receivingService->getById($id);
        $this->showViewReceivingPage = true;
        $this->showIndexPage = false;
    }

    public function confirmDelete(int $id): void
    {
        session()->flash('error','You can not delete receivings.');
        $this->dispatch('flash');
        // $this->receivingId = $id;
        // $this->showConfirmDelete = true;
    }

    public function delete(): void
    {
        if ($this->receivingId) {
            // only admins allowed to delete
            if (!Auth::user()->hasAnyRole(['super','admin'])){
                session()->flash('error','Only admins can delete receivings.');
                $this->dispatch('flash');
                return;
            }
            $this->receivingService->deleteReceiving($this->receivingId);
        }
        $this->showConfirmDelete = false;
        $this->resetForm();
        session()->flash('success', 'Receiving deleted.');
        $this->dispatch('flash');
    }

    public function render()
    {
        return view('livewire.receiving-manager', [
            'purchases' => $this->receivingService->loadUnreceivedPurchases(),
            'receivings' => $this->receivingService->loadUnreceivedOrders(),
            'users' => User::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
        ]);
    }
}


