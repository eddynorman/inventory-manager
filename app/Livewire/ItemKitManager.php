<?php

namespace App\Livewire;

use App\Models\ItemKit;
use Livewire\Component;
use Livewire\WithPagination;

class ItemKitManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public ?int $kitId = null;
    public string $name = '';
    public ?string $description = '';
    public string $selling_price = '';
    public bool $selling_price_includes_tax = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset(['kitId','name','description','selling_price','selling_price_includes_tax']);
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('show-kit-modal');
    }

    public function edit(int $id): void
    {
        $k = ItemKit::findOrFail($id);
        $this->kitId = $k->id;
        $this->name = $k->name;
        $this->description = (string)($k->description ?? '');
        $this->selling_price = (string)$k->selling_price;
        $this->selling_price_includes_tax = (bool)$k->selling_price_includes_tax;
        $this->dispatch('show-kit-modal');
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'selling_price' => ['required','numeric','min:0'],
            'selling_price_includes_tax' => ['boolean'],
        ]);

        ItemKit::updateOrCreate(['id' => $this->kitId], $data);
        $this->dispatch('hide-kit-modal');
        $this->resetForm();
        session()->flash('success', 'Item kit saved.');
    }

    public function confirmDelete(int $id): void
    {
        $this->kitId = $id;
        $this->dispatch('show-delete-modal');
    }

    public function delete(): void
    {
        if ($this->kitId) {
            ItemKit::where('id', $this->kitId)->delete();
        }
        $this->dispatch('hide-delete-modal');
        $this->resetForm();
        session()->flash('success', 'Item kit deleted.');
    }

    public function render()
    {
        $kits = ItemKit::query()
            ->when($this->search !== '', function ($q) {
                $q->where('name','like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.item-kit-manager', compact('kits'))
            ->title('Item Kits');
    }
}


