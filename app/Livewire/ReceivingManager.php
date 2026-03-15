<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\Purchase;
use App\Models\Receiving;
use App\Models\User;
use App\Services\ReceivingService;
use Livewire\Component;
use Livewire\WithPagination;

class ReceivingManager extends Component
{
    use WithPagination;

    protected $listeners = [
        'viewReceiving' => 'viewReceiving',
        'deleteReceiving' => 'confirmDelete',
    ];

    public string $search = '';
    public int $perPage = 10;

    public ?int $receivingId = null;
    public ?int $purchase_id = null;
    public int $received_by_id;
    public ?int $location_id = null;
    public string $type = 'purchase';
    public array $items = [];
    private ReceivingService $receivingService;

    public function mount(): void
    {
        $this->received_by_id = auth()->id();
    }

    public function boot(ReceivingService $receivingService)
    {
        $this->receivingService = $receivingService;
    }

    public function updatedPurchaseId()
    {
        if ($this->purchase_id != null) {
            $this->items = $this->receivingService->loadItemsFor($this->type, $this->purchase_id);
        } else {
            $this->items = [];
        }
    }

    public function updatedType()
    {
        // clear selected source when type changes
        $this->purchase_id = null;
        $this->items = [];
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
        // editing is not allowed once created/received
        session()->flash('error','Receiving records cannot be edited. Create a new receiving if needed.');
        $this->dispatch('flash');
    }

    public function save(): void
    {
        $rules = [
            'type' => ['required','in:purchase,order'],
            'received_by_id' => ['required','integer','exists:users,id'],
            'location_id' => ['nullable','integer','exists:locations,id'],
            'items' => ['required','array','min:1'],
            'items.*.item_id' => ['required','integer','exists:items,id'],
            'items.*.unit_id' => ['required','integer','exists:units,id'],
            'items.*.quantity' => ['required','numeric','gt:0'],
            'items.*.unit_price' => ['required','numeric','gte:0'],
        ];

        $data = $this->validate($rules + [$this->type === 'purchase' ? 'purchase_id' : 'purchase_id' => ['nullable','integer']]);

        $payload = [
            'type' => $this->type,
            'source_id' => $this->type === 'purchase' ? $this->purchase_id : $this->purchase_id,
            'location_id' => $this->location_id,
            'items' => $this->items,
        ];

        try {
            $this->receivingService->saveReceiving($payload, auth()->id());
            $this->dispatch('hide-receiving-modal');
            $this->resetForm();
            session()->flash('success', 'Receiving recorded.');
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            session()->flash('error', 'Fatal error while recording receiving.');
            $this->dispatch('flash');
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->receivingId = $id;
        $this->dispatch('show-delete-modal');
    }

    public function delete(): void
    {
        if ($this->receivingId) {
            // only admins allowed to delete
            if (!auth()->user()->hasAnyRole(['super','admin'])){
                session()->flash('error','Only admins can delete receivings.');
                $this->dispatch('flash');
                return;
            }
            $this->receivingService->deleteReceiving($this->receivingId);
        }
        $this->dispatch('hide-delete-modal');
        $this->resetForm();
        session()->flash('success', 'Receiving deleted.');
    }

    public function render()
    {
        return view('livewire.receiving-manager', [
            'purchases' => $this->receivingService->loadUnreceivedPurchases(),
            'orders' => $this->receivingService->loadUnreceivedOrders(),
            'users' => User::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
        ])->title('Receivings')->layout('layouts.app');
    }

    public function viewReceiving(int $id)
    {
        $this->receivingId = $id;
        $this->dispatch('show-view-receiving');
    }
}


