<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Unit;
use Livewire\Component;
use Livewire\WithPagination;

class ItemManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public ?int $itemId = null;
    public string $name = '';
    public string $barcode = '';
    public ?int $category_id = null;
    public ?int $supplier_id = null;
    public int $initial_stock = 0;
    public int $reorder_level = 0;
    public ?int $smallest_unit_id = null;
    public bool $is_active = true;
    public bool $is_sale_item = true;

    public function resetForm(): void
    {
        $this->reset(['itemId','name','barcode','category_id','supplier_id','initial_stock','reorder_level','smallest_unit_id','is_active','is_sale_item']);
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('show-item-modal');
    }

    public function edit(int $id): void
    {
        $item = Item::findOrFail($id);
        $this->itemId = $item->id;
        $this->name = $item->name;
        $this->barcode = $item->barcode;
        $this->category_id = $item->category_id;
        $this->supplier_id = $item->supplier_id;
        $this->initial_stock = $item->initial_stock;
        $this->reorder_level = $item->reorder_level;
        $this->smallest_unit_id = $item->smallest_unit_id;
        $this->is_active = (bool)$item->is_active;
        $this->is_sale_item = (bool)$item->is_sale_item;
        $this->dispatch('show-item-modal');
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required','string','max:255'],
            'barcode' => ['required','string','max:100'],
            'category_id' => ['required','integer','exists:categories,id'],
            'supplier_id' => ['nullable','integer','exists:suppliers,id'],
            'initial_stock' => ['required','integer','min:0'],
            'reorder_level' => ['required','integer','min:0'],
            'smallest_unit_id' => ['required','integer','exists:units,id'],
            'is_active' => ['boolean'],
            'is_sale_item' => ['boolean'],
        ]);

        $item = Item::updateOrCreate(
            ['id' => $this->itemId],
            array_merge($data, ['current_stock' => $data['initial_stock']])
        );

        $this->dispatch('hide-item-modal');
        $this->resetForm();
        session()->flash('success', 'Item saved.');
    }

    public function confirmDelete(int $id): void
    {
        $this->itemId = $id;
        $this->dispatch('show-delete-modal');
    }

    public function delete(): void
    {
        if ($this->itemId) {
            Item::where('id', $this->itemId)->delete();
        }
        $this->dispatch('hide-delete-modal');
        $this->resetForm();
        session()->flash('success', 'Item deleted.');
    }

    public function render()
    {
        $items = Item::with(['category','supplier','smallestUnit'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($qq) {
                    $qq->where('name', 'like', "%{$this->search}%")
                       ->orWhere('barcode', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.item-manager', [
            'items' => $items,
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ])->title('Items')->layout('layouts.app');
    }
}


