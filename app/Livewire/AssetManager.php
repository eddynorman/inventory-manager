<?php

namespace App\Livewire;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssetItemTemplateExport;
use App\Imports\AssetImport;
use App\Models\AssetInventoryItems;
use App\Models\Department;
use App\Services\AssetService;
use Livewire\Component;
use Livewire\WithFileUploads;

class AssetManager extends Component
{
    use WithFileUploads;
    public array $departments = [];

    public $showCreateModal = false;
    public $showPurchaseModal = false;
    public $showDamageModal = false;
    public $showImportModal = false;
    public $showViewModal = false;
    public $viewItem = null;
    public ?int $itemId = null;
    public $grandTotal = 0;

    public $searchItem = '';
    public $searchResults = [];

    public $form = ['department_id' => null];
    public $purchaseItems = [];
    public $damage = [];

    public $importFile;
    public $importErrors = [];

    protected AssetService $service;
    protected $listeners = [
        'openDamageModal' => 'openDamageModal',
        'viewItem' => 'viewItem'
        ];


    public function boot(AssetService $service): void
    {
        $this->service = $service;
        $this->departments = Department::orderBy('name')->get()->toArray();
    }
    public function refreshTable(): void
    {
        // Dispatch browser event the PowerGrid table expects to trigger refresh
        $this->dispatch('pg:eventRefresh-asset-table-z40ldt-table');
    }

    public function updatedShowCreateModal(){
        if($this->showCreateModal == false){
            $this->reset('form', 'showCreateModal');
            $this->resetErrorBag();
        }
    }
    public function updatedShowPurchaseModal(){
        if($this->showPurchaseModal == false){
            $this->reset('purchaseItems', 'showPurchaseModal','searchItem','searchResults');
            $this->resetErrorBag();
        }
    }
    public function updatedShowDamageModal(){
        if($this->showDamageModal == false){
            $this->reset('damage', 'showDamageModal');
            $this->resetErrorBag();
        }
    }

    public function updatedSearchItem()
    {
        if($this->searchItem != ""){
            $this->searchResults = $this->service->searchItem($this->searchItem);
        }else{
            $this->searchResults = [];
        }

    }

    public function addItem(int $itemId)
    {
        $item = AssetInventoryItems::findOrFail($itemId);

        // 🔥 Check if item already exists
        foreach ($this->purchaseItems as $index => $existing) {

            if ($existing['item_id'] == $itemId) {

                // increment quantity
                $currentQty = $this->purchaseItems[$index]['quantity'] ?? 0;
                $this->purchaseItems[$index]['quantity'] = $currentQty + 1;

                $this->calculateTotals();
                $this->reset('searchItem', 'searchResults');

                return;
            }
        }

        // add new item
        $this->purchaseItems[] = [
            'item_id' => $item->id,
            'name' => $item->name,
            'quantity' => 1,
            'unit_cost' => $item->average_unit_cost,
            'row_total' => $item->average_unit_cost, // initial
        ];

        $this->calculateTotals();
        $this->reset('searchItem', 'searchResults');
    }

    public function updatedPurchaseItems()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $grand = 0;

        foreach ($this->purchaseItems as $index => $item) {

            $qty = is_numeric($item['quantity']) ? (float)$item['quantity'] : 0;
            $cost = is_numeric($item['unit_cost']) ? (float)$item['unit_cost'] : 0;

            // 🔥 handle "", null → 0
            $qty = $qty ?: 0;
            $cost = $cost ?: 0;

            $rowTotal = $qty * $cost;

            // update row total
            $this->purchaseItems[$index]['row_total'] = $rowTotal;

            $grand += $rowTotal;
        }

        $this->grandTotal = $grand;
    }
    public function removeItem(int $index)
    {
        unset($this->purchaseItems[$index]);
        $this->purchaseItems = array_values($this->purchaseItems);

        $this->calculateTotals();
    }

    public function createItem()
    {
        $data  = $this->validate($this->service->rules(),$this->messages());
        try {
            $this->service->createItem($data);
            $this->reset('form', 'showCreateModal');
            $this->refreshTable();
            session()->flash('success','Asset saved successfully.');
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            session()->flash('error',$th->getMessage());
            $this->dispatch('flash');
        }
    }

    public function viewItem(int $id)
    {
        $this->viewItem = AssetInventoryItems::with([
            'department',
            'purchaseItems.purchase',
            'damagedItems'
        ])->findOrFail($id);

        $this->showViewModal = true;
    }
    public function recordPurchase(AssetService $service)
    {
        $data  = $this->validate($service->purchaseRules(),$this->purchaseMessages());
        try {
            $service->recordPurchase($data);
            $this->reset('purchaseItems', 'showPurchaseModal');
            $this->refreshTable();
            session()->flash('success','Purchase saved successfully.');
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            session()->flash('error',$th->getMessage());
            $this->dispatch('flash');
        }

    }

    public function openDamageModal(int $id)
    {
        $item = AssetInventoryItems::findOrFail($id);
        $this->damage = [
            'item_id' => $id,
            'item_name' => $item->name,
            'quantity' => null,
            'notes' => null,
        ];

        $this->showDamageModal = true;
    }

    public function recordDamage(AssetService $service)
    {
        $data  = $this->validate($service->damageRules(),$service->damageMessages());
        try {
            $service->recordDamage($data);
            $this->reset('damage', 'showDamageModal');
            $this->refreshTable();
            session()->flash('success','Damage recorded successfully.');
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            session()->flash('error',$th->getMessage());
            $this->dispatch('flash');
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new AssetItemTemplateExport,
            'asset_template.xlsx'
        );
    }

    public function importAssets(AssetService $service)
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,csv'
        ]);

        $import = new AssetImport($service);

        Excel::import($import, $this->importFile);

        $this->importErrors = $import->errors;

        if (empty($this->importErrors)) {
            session()->flash('success', 'Assets imported successfully.');
            $this->dispatch('flash');
            $this->reset(['importFile','showImportModal']);
            $this->refreshTable();
        }
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
            'form.initial_purchase_date.date' => 'This must be a date.',
            'form.initial_purchase_date.before_or_equal' => 'Date cannot be in the future.',

            'form.initial_quantity.required' => 'Initial quantity is required.',
            'form.initial_quantity.numeric' => 'Quantity must be a number.',
            'form.initial_quantity.min' => 'Quantity cannot be negative.',

            'form.initial_unit_cost.required' => 'Initial cost is required.',
            'form.initial_unit_cost.numeric' => 'Cost must be numeric.',
            'form.initial_unit_cost.min' => 'Cost cannot be negative.',
        ];
    }

    public function purchaseMessages()
    {
        return [
            'purchaseItems.required' => 'Add at least one item.',
            'purchaseItems.*.item_id.exists' => 'Invalid item selected.',
            'purchaseItems.*.quantity.min' => 'Quantity must be at least 1.',
            'purchaseItems.*.unit_cost.min' => 'Cost cannot be negative.',
        ];
    }

    public function render()
    {
        return view('livewire.asset-manager');
    }
}
