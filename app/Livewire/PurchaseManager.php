<?php

namespace App\Livewire;

use App\Models\Purchase;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public ?int $purchaseId = null;
    public string $purchase_date = '';
    public ?int $requisition_id = null;
    public int $purchased_by_id;
    public ?int $supplier_id = null;
    public string $total_amount = '';
    public string $payment_status = 'pending';

    public function mount(): void
    {
        $this->purchased_by_id = auth()->id();
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function resetForm(): void
    {
        $this->reset(['purchaseId','purchase_date','requisition_id','supplier_id','total_amount','payment_status']);
        $this->purchased_by_id = auth()->id();
        $this->payment_status = 'pending';
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->purchase_date = now()->toDateString();
        $this->dispatch('show-purchase-modal');
    }

    public function edit(int $id): void
    {
        $p = Purchase::findOrFail($id);
        $this->purchaseId = $p->id;
        $this->purchase_date = $p->purchase_date;
        $this->requisition_id = $p->requisition_id;
        $this->purchased_by_id = $p->purchased_by_id;
        $this->supplier_id = $p->supplier_id;
        $this->total_amount = (string)$p->total_amount;
        $this->payment_status = $p->payment_status;
        $this->dispatch('show-purchase-modal');
    }

    public function save(): void
    {
        $data = $this->validate([
            'purchase_date' => ['required','date'],
            'requisition_id' => ['nullable','integer','exists:requisitions,id'],
            'purchased_by_id' => ['required','integer','exists:users,id'],
            'supplier_id' => ['nullable','integer','exists:suppliers,id'],
            'total_amount' => ['required','numeric','min:0'],
            'payment_status' => ['required','in:pending,paid,partial'],
        ]);

        Purchase::updateOrCreate(['id' => $this->purchaseId], $data);
        $this->dispatch('hide-purchase-modal');
        $this->resetForm();
        session()->flash('success', 'Purchase saved.');
    }

    public function confirmDelete(int $id): void
    {
        $this->purchaseId = $id;
        $this->dispatch('show-delete-modal');
    }

    public function delete(): void
    {
        if ($this->purchaseId) {
            Purchase::where('id', $this->purchaseId)->delete();
        }
        $this->dispatch('hide-delete-modal');
        $this->resetForm();
        session()->flash('success', 'Purchase deleted.');
    }

    public function render()
    {
        $purchases = Purchase::with(['requisition','purchaser','supplier'])
            ->when($this->search !== '', function ($q) {
                $q->where('payment_status','like', "%{$this->search}%")
                  ->orWhere('purchase_date','like', "%{$this->search}%");
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.purchase-manager', [
            'purchases' => $purchases,
            'requisitions' => Requisition::orderByDesc('id')->get(),
            'users' => User::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ])->title('Purchases')->layout('layouts.app');
    }
}


