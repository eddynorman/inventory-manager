<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Location;
use App\Models\Supplier;
use App\Services\ItemService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Validation\ValidationException;

class ItemManager extends Component
{
    use WithPagination;

    public ?int $itemId = null;
    public string $name = '';
    public string $barcode = '';
    public ?int $categoryId = null;
    public ?int $supplierId = null;
    public ?int $locationId = null;
    public int $initialStock = 0;
    public int $reorderLevel = 0;

    public ?int $bulkCategoryId = null;
    public ?int $bulkSupplierId = null;
    public ?int $bulkLocationId = null;

    public string $smallestUnit = '';
    public ?float $buyingPrice = null;
    public ?float $sellingPrice = null;
    public bool $buyingPriceIncludesTax = false;
    public bool $sellingPriceIncludesTax = false;

    public bool $isActive = true;
    public bool $isSaleItem = true;

    public array $categories = [];
    public array $suppliers = [];
    public array $locations = [];

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showBulkDeleteModal = false;
    public bool $showAssignCategoryModal = false;
    public bool $showAssignSupplierModal = false;
    public array $selectedIds = [];

    protected ItemService $service;

    protected $listeners = [
        'edit' => 'edit',
        'confirmDelete' => 'confirmDelete',
        'refresh-table' => 'refreshTable',
        'bulk-delete-items' => 'confirmBulkDelete',
        'bulk-toggle-active-items' => 'confirmBulkToggleActive',
        'bulk-assign-category-items' => 'assignCategory',
        'bulk-assign-supplier-items' => 'assignSupplier',
        'bulk-assign-location-items' => 'assignLocation',
    ];

    public function boot(ItemService $service): void
    {
        $this->service = $service;
        $this->categories = Category::orderBy('name')->get()->toArray();
        $this->suppliers = Supplier::orderBy('name')->get()->toArray();
        $this->locations = Location::orderBy('name')->get()->toArray();
    }

    public function refreshTable(): void
    {
        // Dispatch browser event the PowerGrid table expects to trigger refresh
        $this->dispatch('pg:eventRefresh-item-table-effbnx-table');
        // also dispatch flash handler so UI hides flash messages when necessary
        $this->dispatch('flash');
    }

    public function resetForm(): void
    {
        $this->reset([
            'itemId','name','barcode','categoryId','supplierId','locationId',
            'initialStock','reorderLevel','smallestUnit','buyingPrice','sellingPrice',
            'buyingPriceIncludesTax','sellingPriceIncludesTax',
            'isActive','isSaleItem',
            'bulkCategoryId','bulkSupplierId','bulkLocationId','selectedIds'
        ]);
        $this->resetValidation();
    }

    public function create()
    {
        if (count($this->categories) === 0) {
            // redirect to categories page and open modal there (controller/view should handle openModal param)
            session()->flash('error', 'You must create a category before adding items.');
            return redirect()->route('categories', ['openModal' => 1]);
        }
        if (count($this->locations) === 0) {
            // redirect to locations page and open modal there (controller/view should handle openModal param)
            session()->flash('error', 'You must create a location before adding items.');
            return redirect()->route('locations', ['openModal' => 1]);
        }

        $this->resetForm();
        $this->showModal = true;
        $this->dispatch('show-item-modal'); // optional: consistent with older JS
    }

    public function edit(int $id): void
    {
        $item = $this->service->getById($id);
        $this->itemId = $item->id;
        $this->name = $item->name;
        $this->barcode = $item->barcode;
        $this->categoryId = $item->category_id;
        $this->locationId = $item->locations->first();
        $this->supplierId = $item->supplier_id;
        $this->initialStock = $item->initial_stock;
        $this->reorderLevel = $item->reorder_level;

        if ($item->units->isNotEmpty()) {
            $unit = $item->units->first();
            $this->smallestUnit = $unit->name;
            $this->buyingPrice = $unit->buying_price;
            $this->sellingPrice = $unit->selling_price;
            $this->buyingPriceIncludesTax = (bool)$unit->buying_price_includes_tax;
            $this->sellingPriceIncludesTax = (bool)$unit->selling_price_includes_tax;
        }

        $this->isActive = (bool)$item->is_active;
        $this->isSaleItem = (bool)$item->is_sale_item;

        $this->showModal = true;
        $this->dispatch('show-item-modal');
    }

    public function save(): void
    {
        try {
            $data = $this->validate($this->service->rules($this->itemId), [
                'sellingPrice.gt' => 'Selling price must be greater than buying price.'
            ]);

            // Map Livewire keys to service expected keys (service expects camel-case keys already)
            //dd($data);
            $this->service->save($this->itemId, $data);

            $this->showModal = false;
            $this->resetForm();
            session()->flash('success', 'Item saved successfully.');

            // refresh table in browser
            $this->refreshTable();
        } catch (ValidationException $ve) {
            session()->flash('error', $ve->getMessage());
            $this->dispatch('flash');

            // bubble validation errors to UI
            throw $ve;

        } catch (\Exception $e) {
            // log server error and show friendly message
            //dd($e);
            //dd($data);
            logger()->error('Item save failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            session()->flash('error', 'An unexpected error occurred while saving the item.');
            $this->dispatch('flash');
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->itemId = $id;
        $this->showDeleteModal = true;
        $this->dispatch('show-delete-modal');
    }

    public function delete(): void
    {
        try {
            if ($this->itemId) {
                $this->service->delete($this->itemId);
            }
            $this->showDeleteModal = false;
            $this->resetForm();
            session()->flash('success', 'Item deleted successfully.');
            $this->refreshTable();
        } catch (\Exception $e) {
            logger()->error('Item delete failed: '.$e->getMessage());
            session()->flash('error', 'Could not delete item.');
            $this->dispatch('flash');
        }
    }

    // Bulk handlers (triggered by JS via Livewire.emit)
    public function confirmBulkDelete(array $ids): void
    {
        $this->selectedIds = $ids;
        $this->showBulkDeleteModal = true;
    }

    public function bulkDelete(): void
    {
        try {
            $this->service->bulkDelete($this->selectedIds);
            $this->showBulkDeleteModal = false;
            $this->selectedIds = [];
            session()->flash('success', 'Items deleted successfully.');
            $this->dispatch('flash');
            $this->refreshTable();
        } catch (\Exception $e) {
            logger()->error('Bulk delete failed: '.$e->getMessage());
            session()->flash('error', 'Unable to delete selected items.');
            $this->dispatch('flash');
        }
    }

    public function confirmBulkToggleActive(array $ids): void
    {
        $this->selectedIds = $ids;
        // toggle modal could ask user to choose active/inactive; we'll call toggle active directly for simplicity
        // Here we'll set them active if any are inactive (simple heuristic)
        $this->service->bulkToggleActive($ids, true);
        session()->flash('success', 'Selected items activated.');
        $this->dispatch('flash');
        $this->refreshTable();
    }

    public function assignCategory(array $ids): void
    {
        $this->selectedIds = $ids;
        $this->showAssignCategoryModal = true;
    }

    public function assignCategoryToItems(): void
    {
        if (!$this->bulkCategoryId) {
            session()->flash('error', 'Please select a category.');
            $this->dispatch('flash');
            return;
        }
        $this->service->bulkAssignCategory($this->selectedIds, $this->bulkCategoryId);
        $this->showAssignCategoryModal = false;
        $this->selectedIds = [];
        session()->flash('success', 'Category assigned to selected items.');
        $this->dispatch('flash');
        $this->refreshTable();
    }

    public function assignSupplier(array $ids): void
    {
        $this->selectedIds = $ids;
        $this->showAssignSupplierModal = true;
    }

    public function assignSupplierToItems(): void
    {
        if (!$this->bulkSupplierId) {
            session()->flash('error', 'Please select a supplier.');
            $this->dispatch('flash');
            return;
        }
        $this->service->bulkAssignSupplier($this->selectedIds, $this->bulkSupplierId);
        $this->showAssignSupplierModal = false;
        $this->selectedIds = [];
        session()->flash('success', 'Supplier assigned to selected items.');
        $this->dispatch('flash');
        $this->refreshTable();
    }

    public function render()
    {
        return view('livewire.item-manager', [
            'categories' => $this->categories,
            'suppliers' => $this->suppliers,
            'locations' => $this->locations,
        ]);
    }
}

