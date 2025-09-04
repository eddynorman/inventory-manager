<?php

namespace App\Livewire;

use App\Models\Supplier;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public ?int $supplierId = null;
    public string $name = '';
    public ?string $email = '';
    public ?string $zip_code = '';
    public ?string $street = '';
    public ?string $city = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset(['supplierId','name','email','zip_code','street','city']);
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('show-supplier-modal');
    }

    public function edit(int $id): void
    {
        $s = Supplier::findOrFail($id);
        $this->supplierId = $s->id;
        $this->name = $s->name;
        $this->email = (string)($s->email ?? '');
        $this->zip_code = (string)($s->zip_code ?? '');
        $this->street = (string)($s->street ?? '');
        $this->city = (string)($s->city ?? '');
        $this->dispatch('show-supplier-modal');
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required','string','max:255', Rule::unique(Supplier::class, 'name')->ignore($this->supplierId)],
            'email' => ['nullable','email','max:255'],
            'zip_code' => ['nullable','string','max:255'],
            'street' => ['nullable','string','max:255'],
            'city' => ['nullable','string','max:255'],
        ]);

        Supplier::updateOrCreate(['id' => $this->supplierId], $data);
        $this->dispatch('hide-supplier-modal');
        $this->resetForm();
        session()->flash('success', 'Supplier saved.');
    }

    public function confirmDelete(int $id): void
    {
        $this->supplierId = $id;
        $this->dispatch('show-delete-modal');
    }

    public function delete(): void
    {
        if ($this->supplierId) {
            Supplier::where('id', $this->supplierId)->delete();
        }
        $this->dispatch('hide-delete-modal');
        $this->resetForm();
        session()->flash('success', 'Supplier deleted.');
    }

    public function render()
    {
        $suppliers = Supplier::query()
            ->when($this->search !== '', function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.supplier-manager', compact('suppliers'))
            ->title('Suppliers');
    }
}


