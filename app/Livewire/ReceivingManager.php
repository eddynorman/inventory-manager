<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\Purchase;
use App\Models\Receiving;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ReceivingManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public ?int $receivingId = null;
    public ?int $purchase_id = null;
    public int $received_by_id;
    public ?int $location_id = null;

    public function mount(): void
    {
        $this->received_by_id = auth()->id();
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function resetForm(): void
    {
        $this->reset(['receivingId','purchase_id','location_id']);
        $this->received_by_id = auth()->id();
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('show-receiving-modal');
    }

    public function edit(int $id): void
    {
        $r = Receiving::findOrFail($id);
        $this->receivingId = $r->id;
        $this->purchase_id = $r->purchase_id;
        $this->received_by_id = $r->received_by_id;
        $this->location_id = $r->location_id;
        $this->dispatch('show-receiving-modal');
    }

    public function save(): void
    {
        $data = $this->validate([
            'purchase_id' => ['required','integer','exists:purchases,id'],
            'received_by_id' => ['required','integer','exists:users,id'],
            'location_id' => ['nullable','integer','exists:locations,id'],
        ]);

        Receiving::updateOrCreate(['id' => $this->receivingId], $data);
        $this->dispatch('hide-receiving-modal');
        $this->resetForm();
        session()->flash('success', 'Receiving saved.');
    }

    public function confirmDelete(int $id): void
    {
        $this->receivingId = $id;
        $this->dispatch('show-delete-modal');
    }

    public function delete(): void
    {
        if ($this->receivingId) {
            Receiving::where('id', $this->receivingId)->delete();
        }
        $this->dispatch('hide-delete-modal');
        $this->resetForm();
        session()->flash('success', 'Receiving deleted.');
    }

    public function render()
    {
        $receivings = Receiving::with(['purchase','receiver','location'])
            ->when($this->search !== '', function ($q) {
                $q->whereHas('purchase', function ($qq) {
                    $qq->where('purchase_date', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.receiving-manager', [
            'receivings' => $receivings,
            'purchases' => Purchase::orderByDesc('id')->get(),
            'users' => User::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
        ])->title('Receivings')->layout('layouts.app');
    }
}


