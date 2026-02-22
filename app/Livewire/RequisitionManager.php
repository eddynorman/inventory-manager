<?php

namespace App\Livewire;

use App\Models\Requisition;
use App\Services\DepartmentService;
use App\Services\ItemService;
use App\Services\RequisitionService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
class RequisitionManager extends Component
{
    public ?int $reqId = null;
    public ?int $department_id = null;
    public string $cost = '';
    public string $status = 'pending';
    public ?string $description = '';
    public ?string $date_requested = '';
    public ?string $reviewed_on = '';
    public ?string $date_approved = '';
    public ?string $funded_on = '';
    public ?string $rejected_at = '';
    public ?int $requested_by_id = null;
    public ?int $reviewed_by = null;
    public ?int $approved_by_id = null;
    public ?int $funded_by = null;
    public ?int $rejected_by = null;
    public float $fund_amount = 0;

    public array $items = [];
    public array $searchItems = [];
    public array $departments = [];
    public string $search = '';

    public bool $showListTable = true;
    public bool $showCreateEditPage = false;
    public bool $showviewPage = false;
    public bool $showDeleteModal = false;
    public bool $showBulkDeleteModal = false;

    protected DepartmentService $departmentService;
    protected RequisitionService $requisitionService;
    protected ItemService $itemService;

    protected $listeners = [
        'edit'=> 'edit',
        'view' => 'view',
        'delete' => 'confirmDelete'
    ];

    public function boot(DepartmentService $departmentService, RequisitionService $requisitionService, ItemService $itemService){
        $this->departmentService = $departmentService;
        $this->requisitionService = $requisitionService;
        $this->itemService = $itemService;
        $this->departments = $this->departmentService->getAll();
    }

    public function resetForm(): void
    {
        $this->reset(['reqId','cost','status','description','date_requested','date_approved','requested_by_id','reviewed_by','approved_by_id','funded_by','reviewed_on','funded_on','rejected_at','search','items','department_id']);
        $this->status = 'pending';
        $this->resetValidation();
    }

    public function refreshTable(): void
    {
        $this->dispatch('refresh-requisitions-table');
    }

    public function updatedShowCreateEditPage(){
        if($this->showCreateEditPage == false){
            $this->showListTable = true;
            $this->resetForm();
        }
    }

    public function updatingSearch(){
        if($this->department_id == null){
            session()->flash('error','Select Department First');
            $this->dispatch('flash');
        }else{
            $this->searchItems = $this->departmentService->searchItems($this->department_id,$this->search);
        }

    }

    public function updatedDepartmentId(){
        if($this->department_id !== null){
            $lowStockItems = $this->departmentService->getLowStockItems($this->department_id);
            foreach($lowStockItems as $item){
                $this->addItem($item['id']);
            }
        }else{
            $this->resetForm();
        }
    }

    public function updatedItems($value, $key)
    {
        $index = explode('.', $key)[0];
        $field = explode('.',$key)[1];

        if($field == 'selected_unit_id'){
            $unit = collect($this->items[$index]['units'])->firstWhere('id', $this->items[$index]['selected_unit_id']);
            $buyingPrice = $unit['buying_price'] ?? null;
            $this->items[$index]['unit_price'] = $buyingPrice;
        }

        if($field == 'unit_price'){
            if($value == null || $value == ''){
                $value = 0.00;
            }
            $this->items[$index]['units'];
            foreach($this->items[$index]['units'] as $index2 => $unit){
                if($unit['id'] == $this->items[$index]['selected_unit_id']){
                    $this->items[$index]['units'][$index2]['buying_price'] = $value;
                }
            }
        }

        if($field == 'quantity'){
            if($value == null || $value == ''){
                $this->items[$index]['quantity'] = 1;
            }
        }

        if($field !== 'total'){
            $this->calculateSubTotal($index);
        }


    }


    /**
     * Adds new item to the list of items in the itemkit.
     * The item is stored in the items array
     */
    public function addItem(int $itemId): void
    {
        $item = $this->itemService->getById($itemId);
        $units = $this->itemService->getUnits($itemId);
        //if item is already in the list, update the quantity
        $found = false;
        foreach($this->items as $index => $i){
            if($i['item_id'] == $itemId){
                $this->items[$index]['quantity'] += 1;
                $found = true;
                $this->calculateSubTotal($index);
                break;
            }
        }
        if(!$found){
            $this->items[] = [
            'item_id' => $itemId,
            'name' => $item->name,
            'units' => $units,
            'quantity' => 2*$item->reorder_level,
            'current_stock' => $item->current_stock,
            'selected_unit_id' => $units[0]['id'],
            'unit_price' => $units[0]['buying_price'],
            'total' => $units[0]['buying_price']*2*$item->reorder_level,
            ];
        }
        $this->search = '';
        $this->searchItems = [];
        $this->calculateCost();
    }
    public function removeItem(int $itemId): void
    {

        foreach($this->items as $index => $item){
            if($item['item_id'] == $itemId){
                unset($this->items[$index]);
                break;
            }
        }
        $this->calculateCost();
    }

    public function calculateSubTotal(int $index){
        $unit = collect($this->items[$index]['units'])->firstWhere('id', $this->items[$index]['selected_unit_id']);

        $buyingPrice = $unit['buying_price'] ?? null;

        $this->items[$index]['total'] = $buyingPrice * $this->items[$index]['quantity'];
        $this->calculateCost();
    }

    public function calculateCost(){
        $sum = 0 ;
        foreach ($this->items as $item) {
            $sum += $item['total'];
        }
        $this->cost = $sum;
    }

    public function create(): void
    {
        $this->resetForm();
        $this->date_requested = now();
        $this->requested_by_id = Auth::id();
        $this->showCreateEditPage = true;
        $this->showListTable = false;
    }

    public function edit(int $id): void
    {
        $r = $this->requisitionService->getById($id);
        if($r->status != 'funded')
        {
            $this->reqId = $r->id;
            $this->department_id = $r->department_id;
            $this->cost = (string)$r->cost;
            $this->status = $r->status;
            $this->description = (string)($r->description ?? '');
            $this->date_requested = $r->date_requested;
            $this->date_approved = $r->date_approved;
            $this->requested_by_id = $r->requested_by_id;

            $reqItems = $r->items;
            foreach($reqItems as $reqItem){
                $item = $this->itemService->getById($reqItem->item_id);
                $units = $item->units->toArray();
                foreach($units as $index2 => $unit){
                    if($unit['id'] == $reqItem->unit_id){
                        $units[$index2]['buying_price'] = $reqItem->unit_price;
                        break;
                    }
                }
                $this->items[] = [
                'item_id' => $reqItem->item_id,
                'name' => $item->name,
                'units' => $units,
                'quantity' => $reqItem->quantity,
                'current_stock' => $reqItem->current_stock,
                'selected_unit_id' => $reqItem->unit_id,
                'unit_price' => $reqItem->unit_price,
                'total' => $reqItem->total,
                ];
            }
            $this->showCreateEditPage = true;
            $this->showListTable = false;
        }else {
            session()->flash("error","You can not edit a funded requisition!");
            $this->dispatch('flash');
        }

    }

    public function save(): void
    {
        $data = $this->validate($this->requisitionService->rules($this->reqId));
        try {
            $this->requisitionService->save($this->reqId, $data);
            $this->showCreateEditPage = false;
            $this->updatedShowCreateEditPage();
            $this->resetForm();
            session()->flash('success', 'Requisition saved.');
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            session()->flash('error', 'Fatal Error occurred while saving');
            $this->dispatch('flash');
        }


    }

    public function confirmDelete(int $id): void
    {
        $this->reqId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->reqId) {
            $this->requisitionService->delete($this->reqId);
        }
        $this->showDeleteModal = false;
        $this->resetForm();
        $this->refreshTable();
        session()->flash('success', 'Requisition deleted.');
        $this->dispatch('flash');
    }

     protected function messages(): array
    {
        return [
            'items.required' => "At least one item is required!",
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.numeric'  => 'Quantity must be a number.',
            'items.*.quantity.min'      => 'Quantity must be at least 0.1.',

            'items.*.item_id.required' => 'Item is required.',
            'items.*.selected_unit_id.required' => 'Unit is required.',
        ];
    }

    public function render()
    {
        return view('livewire.requisition-manager');
    }
}


