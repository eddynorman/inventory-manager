<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class LocationManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public ?int $locationId = null;
    public string $name = '';
    public string $location_type = 'store';
    public ?string $address = '';
    public ?string $phone = '';
    public ?string $email = '';
    public ?int $staff_responsible = null;
    public ?string $description = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset(['locationId','name','location_type','address','phone','email','staff_responsible','description']);
        $this->location_type = 'store';
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('show-location-modal');
    }

    public function edit(int $id): void
    {
        $l = Location::findOrFail($id);
        $this->locationId = $l->id;
        $this->name = $l->name;
        $this->location_type = $l->location_type;
        $this->address = (string)($l->address ?? '');
        $this->phone = (string)($l->phone ?? '');
        $this->email = (string)($l->email ?? '');
        $this->staff_responsible = $l->staff_responsible;
        $this->description = (string)($l->description ?? '');
        $this->dispatch('show-location-modal');
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required','string','max:255', Rule::unique(Location::class, 'name')->ignore($this->locationId)],
            'location_type' => ['required','in:warehouse,store,office'],
            'address' => ['nullable','string','max:255'],
            'phone' => ['nullable','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'staff_responsible' => ['nullable','integer','exists:users,id'],
            'description' => ['nullable','string'],
        ]);

        Location::updateOrCreate(['id' => $this->locationId], $data);
        $this->dispatch('hide-location-modal');
        $this->resetForm();
        session()->flash('success', 'Location saved.');
    }

    public function confirmDelete(int $id): void
    {
        $this->locationId = $id;
        $this->dispatch('show-delete-modal');
    }

    public function delete(): void
    {
        if ($this->locationId) {
            Location::where('id', $this->locationId)->delete();
        }
        $this->dispatch('hide-delete-modal');
        $this->resetForm();
        session()->flash('success', 'Location deleted.');
    }

    public function render()
    {
        $locations = Location::query()
            ->when($this->search !== '', function ($q) {
                $q->where('name','like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.location-manager', [
            'locations' => $locations,
            'users' => User::orderBy('name')->get(),
        ])->title('Locations');
    }
}


