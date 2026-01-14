<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Unit;
use App\Services\UnitService;
use Livewire\Component;
use Livewire\WithPagination;

class UnitManager extends Component
{
    use WithPagination;

    public string $search = '';
    public ?array $items = null;

    public ?int $selectedItemId = null;
    public string $selectedItemName = '';

    public ?int $unitId = null;
    public string $name = '';
    public ?float $buyingPrice = null;
    public ?float $sellingPrice = null;
    public int $smallestUnitsNumber = 1;
    public bool $buyingPriceIncludesTax = false;
    public bool $sellingPriceIncludesTax = false;
    public bool $isActive = true;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showBulkDeleteModal = false;

    public array $selectedUnits = [];

    protected UnitService $unitService;

    protected $listeners = [
        'edit-unit' => 'edit',
        'delete-unit' => 'confirmDelete',
        'bulk-delete-units' => 'confirmBulkDelete',
        'refresh-table' => 'refreshTable',
    ];

    /**
     * Boot method for injecting the service
     */
    public function boot(UnitService $unitService): void
    {
        $this->unitService = $unitService;
        $this->items = Item::orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function refreshTable(): void
    {
        $this->dispatch('pg:eventRefresh-units-table-p5xboo-table');
    }

    public function updatedSearch(): void
    {
        if (trim($this->search) !== '') {
            $this->items = Item::query()
                ->where('name', 'like', '%' . $this->search . '%')
                ->orderBy('name')
                ->limit(5)
                ->get(['id', 'name'])
                ->toArray();
        } else {
            $this->items = null;
        }
    }

    public function selectItem(int $id, string $name): void
    {
        $this->selectedItemId = $id;
        $this->selectedItemName = $name;
        $this->items = null;
        $this->search = $name;
    }

    public function resetForm(): void
    {
        $this->reset([
            'unitId', 'name', 'buyingPrice', 'sellingPrice', 'smallestUnitsNumber',
            'buyingPriceIncludesTax', 'sellingPriceIncludesTax', 'isActive',
            'selectedItemId', 'selectedItemName', 'search',
        ]);
        $this->smallestUnitsNumber = 1;
        $this->resetValidation();
    }

    public function create()
    {
        if (count($this->items) === 0) {
            session()->flash('error', 'You must create an item before adding units.');
            return redirect()->route('items', ['openModal' => 1]);
        }
        $this->resetForm();
        $this->showModal = true;
        $this->dispatch('focus-unit-name');
    }

    public function edit(int $id): void
    {
        $unit = $this->unitService->getById($id);

        $this->unitId = $unit->id;
        $this->name = $unit->name;
        $this->buyingPrice = $unit->buying_price;
        $this->sellingPrice = $unit->selling_price;
        $this->smallestUnitsNumber = (int)$unit->smallest_units_number;
        $this->buyingPriceIncludesTax = (bool)$unit->buying_price_includes_tax;
        $this->sellingPriceIncludesTax = (bool)$unit->selling_price_includes_tax;
        $this->isActive = (bool)$unit->is_active;

        if ($unit->item) {
            $this->selectedItemId = $unit->item_id;
            $this->selectedItemName = $unit->item->name;
            $this->search = $unit->item->name;
        }

        $this->showModal = true;
        $this->dispatch('focus-unit-name');
    }

    public function save(): void
    {
        $data = $this->validate($this->unitService->rules($this->unitId), [
            'selectedItemId.required' => 'Please select an item.',
        ]);

        $this->unitService->save($this->unitId, $data);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Unit saved successfully.');
        $this->dispatch('flash');
        $this->refreshTable();
    }

    public function confirmDelete(int $id): void
    {
        $this->unitId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->unitId) {
            $this->unitService->delete($this->unitId);
        }
        $this->showDeleteModal = false;
        $this->resetForm();
        session()->flash('success', 'Unit deleted.');
        $this->dispatch('flash');
        $this->refreshTable();
    }

    public function confirmBulkDelete(array $ids): void
    {
        //remove ids belonging to smallest items
        $newIds = array_filter($ids, function ($id) {
            return !Unit::where('id', $id)->where('is_smallest_unit', true)->exists();
        });
        $this->selectedUnits = $newIds;
        $this->showBulkDeleteModal = true;
    }

    public function bulkDelete(): void
    {
        if (!empty($this->selectedUnits)) {
            $this->unitService->bulkDelete($this->selectedUnits);
        }
        $this->showBulkDeleteModal = false;
        $this->selectedUnits = [];
        session()->flash('success', 'Selected units deleted.');
        $this->dispatch('flash');
        $this->refreshTable();
    }

    public function render()
    {
        return view('livewire.unit-manager');
    }
}
