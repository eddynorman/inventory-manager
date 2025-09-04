<?php

namespace App\Livewire;

use App\Models\Unit;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class UnitManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public ?int $unitId = null;
    public string $name = '';
    public string $buying_price = '';
    public ?string $selling_price = '';
    public bool $is_smallest_unit = false;
    public int $smallest_units_number = 1;
    public bool $buying_price_includes_tax = false;
    public bool $selling_price_includes_tax = false;
    public bool $is_active = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset(['unitId','name','buying_price','selling_price','is_smallest_unit','smallest_units_number','buying_price_includes_tax','selling_price_includes_tax','is_active']);
        $this->smallest_units_number = 1;
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('show-unit-modal');
    }

    public function edit(int $id): void
    {
        $u = Unit::findOrFail($id);
        $this->unitId = $u->id;
        $this->name = $u->name;
        $this->buying_price = (string)$u->buying_price;
        $this->selling_price = $u->selling_price !== null ? (string)$u->selling_price : '';
        $this->is_smallest_unit = (bool)$u->is_smallest_unit;
        $this->smallest_units_number = (int)$u->smallest_units_number;
        $this->buying_price_includes_tax = (bool)$u->buying_price_includes_tax;
        $this->selling_price_includes_tax = (bool)$u->selling_price_includes_tax;
        $this->is_active = (bool)$u->is_active;
        $this->dispatch('show-unit-modal');
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required','string','max:255', Rule::unique(Unit::class, 'name')->ignore($this->unitId)],
            'buying_price' => ['required','numeric','min:0'],
            'selling_price' => ['nullable','numeric','min:0'],
            'is_smallest_unit' => ['boolean'],
            'smallest_units_number' => ['required','integer','min:1'],
            'buying_price_includes_tax' => ['boolean'],
            'selling_price_includes_tax' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        Unit::updateOrCreate(['id' => $this->unitId], $data);
        $this->dispatch('hide-unit-modal');
        $this->resetForm();
        session()->flash('success', 'Unit saved.');
    }

    public function confirmDelete(int $id): void
    {
        $this->unitId = $id;
        $this->dispatch('show-delete-modal');
    }

    public function delete(): void
    {
        if ($this->unitId) {
            Unit::where('id', $this->unitId)->delete();
        }
        $this->dispatch('hide-delete-modal');
        $this->resetForm();
        session()->flash('success', 'Unit deleted.');
    }

    public function render()
    {
        $units = Unit::query()
            ->when($this->search !== '', function ($q) {
                $q->where('name','like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.unit-manager', compact('units'))
            ->title('Units')->layout('layouts.app');
    }
}


