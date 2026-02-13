<?php

namespace App\Livewire;


use App\Models\ItemKit;
use App\Services\ItemKitService;
use App\Services\ItemService;
use App\Services\UnitService;
use Livewire\Component;
use Livewire\WithPagination;

use function PHPUnit\Framework\isEmpty;

class ItemKitManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public array $items = [];
    public string $searchItem = '';
    public array $searchItems = [];
    public ?int $kitId = null;
    public string $name = '';
    public ?string $description = '';
    public string $selling_price = '';
    public bool $selling_price_includes_tax = false;
    public array $selectedIds = [];

    public bool $showKitModal = false;
    public bool $showViewKitModal = false;
    public bool $showDeleteModal = false;
    public bool $showBulkDeleteModal = false;
    protected ItemService $itemService;
    protected ItemKitService $itemKitService;
    protected UnitService $unitService;

    protected $listeners = [
        'view' => 'view',
        'edit' => 'edit',
        'confirmDelete' => 'confirmDelete',
        'confirmBulkDelete' => 'confirmBulkDelete'
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSearchItem(): void
    {
        $this->searchItems = $this->itemService->search($this->searchItem);
        //dd($this->searchItems);
    }

    public function updatedShowKitModal(): void
    {
        if($this->showKitModal == false){
            $this->resetForm();
        }
    }
    public function updatedShowViewKitModal(): void {
        if($this->showViewKitModal == false){
            $this->resetForm();
        }
    }

    public function boot(ItemService $itemService,ItemKitService $itemKitService, UnitService $unitService): void
    {
        $this->itemService = $itemService;
        $this->itemKitService = $itemKitService;
        $this->unitService = $unitService;
    }

    public function resetForm(): void
    {
        $this->reset(['kitId','name','description','selling_price','selling_price_includes_tax','items','searchItem','searchItems']);
        $this->resetValidation();
    }
    /**
     * Adds new item to the list of items in the itemkit.
     * The item is stored in the items array
     */
    public function addItem(int $itemId): void
    {
        $item = $this->itemService->getById($itemId);
        $units = $this->itemService->getUnits($itemId);
        //if item is already in the kit, update the quantity
        $found = false;
        foreach($this->items as $index => $item){
            if($item['item_id'] == $itemId){
                $this->items[$index]['quantity'] += 1;
                $found = true;
                break;
            }
        }
        if(!$found){
        $this->items[] = [
            'item_id' => $item->id,
            'name' => $item->name,
            'units' => $units,
            'quantity' => 1,
            'selected_unit_id' => $units[0]['id'],
            ];
        }
        $this->searchItem = '';
        $this->searchItems = [];
    }
    public function removeItem(int $itemId): void
    {
        //dd($this->items);
        foreach($this->items as $item){
            $index = 0;
            //dd($item);
            if($item['item_id'] == $itemId){
                unset($this->items[$index]);
                break;
            }
            $index++;
        }
    }

    /**
    * Prepare the form for creating a new Item Kit.
    *
    * Resets all form fields to their default state and
    * opens the Item Kit modal for user input.
    *
    * @return void
    */
    public function create(): void
    {
        $this->resetForm();
        $this->showKitModal = true;
    }

    public function view(int $id): void{
        $k = ItemKit::findOrFail($id);
        $this->kitId = $k->id;
        $this->name = $k->name;
        $this->description = (string)($k->description ?? '');
        $this->selling_price = (string)number_format($k->selling_price,2);
        $this->selling_price_includes_tax = (bool)$k->selling_price_includes_tax;
        $this->items = $this->itemKitService->getItems($this->kitId);
        foreach($this->items as $index=>$item){
            $this->items[$index]['unit'] = $this->unitService->getNameById($item['selected_unit_id']);
        }
        //dd($this->items);
        $this->showViewKitModal = true;
    }

    public function edit(int $id): void
    {
        $k = ItemKit::findOrFail($id);
        $this->kitId = $k->id;
        $this->name = $k->name;
        $this->description = (string)($k->description ?? '');
        $this->selling_price = (string)$k->selling_price;
        $this->selling_price_includes_tax = (bool)$k->selling_price_includes_tax;
        $this->items = $this->itemKitService->getItems($this->kitId);
        foreach($this->items as $index=>$item){
            $this->items[$index]['units'] = $this->itemService->getUnits($item['item_id']);
        }
        //dd($this->items);
        $this->showKitModal = true;
    }

    public function save(): void
    {
        $data = $this->validate($this->itemKitService->rules($this->kitId));
        $this->itemKitService->save($this->kitId,$data);
        $this->showKitModal = false;
        $this->resetForm();
        session()->flash('success', 'Item kit saved.');
    }

    public function confirmDelete(int $id): void
    {
        $this->kitId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->kitId) {
            ItemKit::where('id', $this->kitId)->delete();
        }
        $this->showDeleteModal = false;
        $this->resetForm();
        session()->flash('success', 'Item kit deleted.');
    }

    public function confirmBulkDelete(array $ids){
        //dd($ids);
        $this->selectedIds = $ids;
        if(count($ids) == 0){
            session()->flash('error','No item kit selected');
            $this->dispatch('flash');
        }else{
            $this->showBulkDeleteModal = true;
        }
    }

    public function bulkDelete(){
        try {
            $this->itemKitService->bulkDelete($this->selectedIds);
            $this->showBulkDeleteModal = false;
            $this->selectedIds = [];
            session()->flash('success', 'Item Kits deleted successfully.');
            $this->dispatch('flash');
            $this->refreshTable();
        } catch (\Exception $e) {
            logger()->error('Bulk delete failed: '.$e->getMessage());
            session()->flash('error', 'Unable to delete selected items.');
            $this->dispatch('flash');
        }
    }

    public function render()
    {
        $kits = ItemKit::query()
            ->when($this->search !== '', function ($q) {
                $q->where('name','like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.item-kit-manager', compact('kits'));
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

}


