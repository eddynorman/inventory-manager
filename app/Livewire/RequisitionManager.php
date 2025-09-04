<?php

namespace App\Livewire;

use App\Models\Requisition;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class RequisitionManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public ?int $reqId = null;
    public string $cost = '';
    public string $status = 'pending';
    public ?string $description = '';
    public string $date_requested = '';
    public ?string $date_approved = '';
    public ?int $requested_by_id = null;
    public ?int $approved_by_id = null;

    public function updatingSearch(): void { $this->resetPage(); }

    public function resetForm(): void
    {
        $this->reset(['reqId','cost','status','description','date_requested','date_approved','requested_by_id','approved_by_id']);
        $this->status = 'pending';
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->date_requested = now()->toDateString();
        $this->requested_by_id = auth()->id();
        $this->dispatch('show-requisition-modal');
    }

    public function edit(int $id): void
    {
        $r = Requisition::findOrFail($id);
        $this->reqId = $r->id;
        $this->cost = (string)$r->cost;
        $this->status = $r->status;
        $this->description = (string)($r->description ?? '');
        $this->date_requested = $r->date_requested;
        $this->date_approved = $r->date_approved;
        $this->requested_by_id = $r->requested_by_id;
        $this->approved_by_id = $r->approved_by_id;
        $this->dispatch('show-requisition-modal');
    }

    public function save(): void
    {
        $data = $this->validate([
            'requested_by_id' => ['required','integer','exists:users,id'],
            'approved_by_id' => ['nullable','integer','exists:users,id'],
            'cost' => ['required','numeric','min:0'],
            'status' => ['required','in:pending,approved,rejected'],
            'description' => ['nullable','string'],
            'date_requested' => ['required','date'],
            'date_approved' => ['nullable','date'],
        ]);

        Requisition::updateOrCreate(['id' => $this->reqId], $data);
        $this->dispatch('hide-requisition-modal');
        $this->resetForm();
        session()->flash('success', 'Requisition saved.');
    }

    public function confirmDelete(int $id): void
    {
        $this->reqId = $id;
        $this->dispatch('show-delete-modal');
    }

    public function delete(): void
    {
        if ($this->reqId) {
            Requisition::where('id', $this->reqId)->delete();
        }
        $this->dispatch('hide-delete-modal');
        $this->resetForm();
        session()->flash('success', 'Requisition deleted.');
    }

    public function render()
    {
        $requisitions = Requisition::with(['requestedBy','approvedBy'])
            ->when($this->search !== '', function ($q) {
                $q->where('description','like', "%{$this->search}%")
                  ->orWhere('status','like', "%{$this->search}%");
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.requisition-manager', [
            'requisitions' => $requisitions,
            'users' => User::orderBy('name')->get(),
        ])->title('Requisitions');
    }
}


