<div>
    @include('layouts.flash')

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title">Locations</h5>
            <button wire:click="create" class="btn btn-primary">New Location</button>
        </div>

        <div class="card-body table-responsive">
            <livewire:tables.location-table/>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 w-100 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg custom-modal-width-md">

                <!-- Modal Header -->
                <div class="bg-primary text-white px-4 py-3 rounded-t-lg flex justify-between items-center">
                    <h5 class="mb-0">{{ $locationId ? 'Edit Location' : 'New Location' }}</h5>
                    <button class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>

                <!-- Modal Body -->
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" wire:model.defer="name">
                            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <select class="form-select" wire:model.defer="location_type">
                                <option value="store">Store</option>
                                <option value="warehouse">Warehouse</option>
                                <option value="office">Office</option>
                            </select>
                            @error('location_type')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" wire:model.defer="address">
                            @error('address')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" wire:model.defer="phone">
                            @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" wire:model.defer="email">
                            @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Staff Responsible</label>
                            <select class="form-select" wire:model.defer="staff_responsible">
                                <option value="">-- Select --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('staff_responsible')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" wire:model.defer="description"></textarea>
                            @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-light px-4 py-3 rounded-b-lg d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary" wire:click="$set('showModal', false)">Close</button>
                    <button class="btn btn-primary" wire:click="save">Save</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg custom-modal-width-sm">

                <!-- Modal Header -->
                <div class="bg-danger text-white px-4 py-3 rounded-t-lg flex justify-between items-center">
                    <h5 class="mb-0">Confirm Delete</h5>
                    <button class="btn-close btn-close-white" wire:click="$set('showDeleteModal', false)"></button>
                </div>

                <!-- Modal Body -->
                <div class="p-4">
                    <p>Are you sure you want to delete this location?</p>
                </div>

                <!-- Modal Footer -->
                <div class="bg-light px-4 py-3 rounded-b-lg d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Cancel</button>
                    <button class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    @endif

    <!-- View Modal -->
    @if($showViewModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg w-full custom-modal-width-2xl h-[90vh] overflow-y-auto">

                <!-- Modal Header -->
                <div class="bg-info text-white px-4 py-3 rounded-t-lg flex justify-between items-center">
                    <h5 class="mb-0">Location: {{ $name }}</h5>
                    <button class="btn-close btn-close-white" wire:click="$set('showViewModal', false)"></button>
                </div>

                <!-- Modal Body -->
                <div class="p-4">
                    <h6>Details</h6>
                    <ul class="list-group mb-4">
                        <li class="list-group-item"><strong>Type:</strong> {{ ucfirst($location_type) }}</li>
                        <li class="list-group-item"><strong>Address:</strong> {{ $address }}</li>
                        <li class="list-group-item"><strong>Phone:</strong> {{ $phone }}</li>
                        <li class="list-group-item"><strong>Email:</strong> {{ $email }}</li>
                        <li class="list-group-item"><strong>Responsible:</strong> {{ optional($users->find($staff_responsible))->name }}</li>
                        <li class="list-group-item"><strong>Description:</strong> {{ $description }}</li>
                    </ul>

                    <h6>Items in this location</h6>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items ?? [] as $itemLocation)
                                <tr>
                                    <td>{{ $itemLocation['item']['name'] ?? 'Unknown' }}</td>
                                    <td>{{ $itemLocation['stock'] ?? 0 }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary"
                                                wire:click="openMoveModal({{ $itemLocation['id'] }})">
                                            Move
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Modal Footer -->
                <div class="bg-light px-4 py-3 rounded-b-lg d-flex justify-content-end">
                    <button class="btn btn-secondary" wire:click="$set('showViewModal', false)">Close</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Move Item Modal -->
    @if($showMoveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg">

                <!-- Modal Header -->
                <div class="bg-warning text-white px-4 py-3 rounded-t-lg flex justify-between items-center">
                    <h5 class="mb-0">Move Item</h5>
                    <button class="btn-close btn-close-white" wire:click="$set('showMoveModal', false)"></button>
                </div>

                <!-- Modal Body -->
                <div class="p-4">
                    <div class="mb-3">
                        <label class="form-label">Target Location</label>
                        <select class="form-select" wire:model.defer="targetLocationId">
                            <option value="">-- Select Location --</option>
                            @foreach($allLocations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                        @error('targetLocationId')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" wire:model.defer="quantity" min="1">
                        @error('quantity')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-light px-4 py-3 rounded-b-lg d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary" wire:click="$set('showMoveModal', false)">Cancel</button>
                    <button class="btn btn-primary" wire:click="moveItem">Move</button>
                </div>
            </div>
        </div>
    @endif
</div>
