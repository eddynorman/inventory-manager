<?php

namespace App\Livewire;

use App\Models\Customer;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public ?int $customerId = null;
    public string $name = '';
    public string $email = '';
    public ?string $zip_code = '';
    public ?string $street = '';
    public ?string $city = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset(['customerId','name','email','zip_code','street','city']);
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('show-customer-modal');
    }

    public function edit(int $id): void
    {
        $c = Customer::findOrFail($id);
        $this->customerId = $c->id;
        $this->name = $c->name;
        $this->email = (string)$c->email;
        $this->zip_code = (string)($c->zip_code ?? '');
        $this->street = (string)($c->street ?? '');
        $this->city = (string)($c->city ?? '');
        $this->dispatch('show-customer-modal');
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','lowercase','email','max:255', Rule::unique(Customer::class, 'email')->ignore($this->customerId)],
            'zip_code' => ['nullable','string','max:255'],
            'street' => ['nullable','string','max:255'],
            'city' => ['nullable','string','max:255'],
        ]);

        Customer::updateOrCreate(['id' => $this->customerId], $data);
        $this->dispatch('hide-customer-modal');
        $this->resetForm();
        session()->flash('success', 'Customer saved.');
    }

    public function confirmDelete(int $id): void
    {
        $this->customerId = $id;
        $this->dispatch('show-delete-modal');
    }

    public function delete(): void
    {
        if ($this->customerId) {
            Customer::where('id', $this->customerId)->delete();
        }
        $this->dispatch('hide-delete-modal');
        $this->resetForm();
        session()->flash('success', 'Customer deleted.');
    }

    public function render()
    {
        $customers = Customer::query()
            ->when($this->search !== '', function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.customer-manager', compact('customers'))
            ->title('Customers')
            ->layout('layouts.app');
    }
}


