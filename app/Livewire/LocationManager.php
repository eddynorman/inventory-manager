<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Location;
use App\Services\LocationService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class LocationManager extends Component
{
    use WithPagination;

    protected LocationService $service;

    public function boot(LocationService $service): void
    {
        $this->service = $service;
        $this->users = User::where('role', '!=', 'super') // exclude super role
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->toArray();
    }

    // Form State
    public ?int $locationId = null;
    public string $name = '';
    public string $locationType = 'store';
    public ?string $address = '';
    public ?string $phone = '';
    public ?string $email = '';
    public ?int $staffResponsible = null;
    public ?string $description = '';

    public array $users = [];

    // View State
    public array $items = [];

    // Move Item State
    public ?int $movingItemLocationId = null;
    public ?int $targetLocationId = null;
    public int $quantity = 1;

    // Modal State
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showViewModal = false;
    public bool $showMoveModal = false;

    protected $listeners = [
        'refresh-table' => 'refreshTable',
    ];

    public function refreshTable(): void
    {
        // Dispatch browser event the PowerGrid table expects to trigger refresh
        $this->dispatch('pg:eventRefresh-location-table');
        // also dispatch flash handler so UI hides flash messages when necessary
        $this->dispatch('flash');
    }

    public function resetForm(): void
    {
        $this->reset([
            'locationId',
            'name',
            'locationType',
            'address',
            'phone',
            'email',
            'staffResponsible',
            'description'
        ]);
        $this->locationType = 'store';
        $this->resetValidation();
    }

    public function create(): void
    {
        if(count($this->users) === 0 ){
            session()->flash('error', 'No users available to assign as staff responsible.');
            $this->dispatch('flash');
            return;
        }
        $this->resetForm();
        $this->showModal = true;
    }

    #[On('edit')]
    public function edit(int $id): void
    {
        $l = $this->service->getWithItems($id);
        $this->locationId = $l->id;
        $this->name = $l->name;
        $this->locationType = $l->locationType;
        $this->address = (string)($l->address ?? '');
        $this->phone = (string)($l->phone ?? '');
        $this->email = (string)($l->email ?? '');
        $this->staffResponsible = $l->staffResponsible;
        $this->description = (string)($l->description ?? '');

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate($this->service->rules($this->locationId));
        $this->service->save($this->locationId, $data);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Location saved.');
        $this->refreshTable();
    }

    #[On('confirmDelete')]
    public function confirmDelete(int $id): void
    {
        $this->locationId = $id;
        $this->showDeleteModal = true;
    }

    #[On('delete')]
    public function delete(): void
    {
        if ($this->locationId) {
            $this->service->delete($this->locationId);
        }
        $this->showDeleteModal = false;
        $this->resetForm();
        session()->flash('success', 'Location deleted.');
        $this->refreshTable();
    }

    #[On('view')]
    public function view(int $id): void
    {
        $location = $this->service->getWithItems($id);
        $this->locationId = $location->id;
        $this->name = $location->name;
        $this->locationType = $location->locationType;
        $this->address = (string)($location->address ?? '');
        $this->phone = (string)($location->phone ?? '');
        $this->email = (string)($location->email ?? '');
        $this->staffResponsible = $location->staffResponsible;
        $this->description = (string)($location->description ?? '');
        $this->items = $location->items->map(fn($il) => [
            'id' => $il->id,
            'item' => ['id' => $il->item->id, 'name' => $il->item->name],
            'stock' => $il->current_tock,
        ])->toArray();

        $this->showViewModal = true;
    }

    public function openMoveModal(int $itemLocationId): void
    {
        $this->movingItemLocationId = $itemLocationId;
        $this->quantity = 1;
        $this->targetLocationId = null;
        $this->showMoveModal = true;
    }

    public function moveItem(): void
    {
        $this->validate([
            'movingItemLocationId' => ['required', 'integer'],
            'targetLocationId' => ['required', 'integer', 'exists:locations,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $this->service->moveItem(
                $this->movingItemLocationId,
                $this->targetLocationId,
                $this->quantity
            );

            $this->showMoveModal = false;
            session()->flash('success', 'Item moved successfully.');
            $this->dispatch('flash');
            $this->view($this->locationId); // refresh view data
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->dispatch('flash');
        }
    }

    public function render()
    {
        return view('livewire.location-manager', [
            'users' => User::orderBy('name')->get(),
            'allLocations' => Location::orderBy('name')->get(),
        ]);
    }
}
