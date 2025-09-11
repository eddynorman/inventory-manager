<div>
    @include('layouts.flash')
    {{-- <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Units</h5>
        @if(auth()->user()->hasAnyRole(['super','admin','manager']))
            <button wire:click="create" class="btn btn-primary">New Unit</button>
        @endif
    </div> --}}

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title">Units</h5>
            <button wire:click="create" class="btn btn-primary">New Unit</button>
        </div>

        <div class="card-body">
            <livewire:tables.units-table/>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl">

                <!-- Modal Header -->
                <div class="bg-primary text-white px-4 py-3 rounded-t-lg flex justify-between items-center">
                    <h5 class="mb-0">{{ $unitId ? 'Edit Unit' : 'New Unit' }}</h5>
                    <button class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>

                <!-- Modal Body -->
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" id="unit-name" class="form-control" wire:model.defer="name">
                            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 position-relative">
                            <label class="form-label">Item</label>
                            <input type="text" class="form-control" wire:model.live.debounce.500ms="search" placeholder="Search item...">

                            @if($items)
                                <div class="list-group position-absolute w-100 shadow" style="z-index:1050; max-height:200px; overflow-y:auto;">
                                    @foreach($items as $item)
                                        <button type="button"
                                                class="list-group-item list-group-item-action"
                                                wire:click="selectItem({{ $item['id'] }}, '{{ $item['name'] }}')">
                                            {{ $item['name'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <input type="hidden" wire:model="selectedItemId">
                            @error('selectedItemId')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Buying Price</label>
                            <input type="number" step="0.01" class="form-control" wire:model.defer="buyingPrice">
                            @error('buyingPrice')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Selling Price</label>
                            <input type="number" step="0.01" class="form-control" wire:model.defer="sellingPrice">
                            @error('sellingPrice')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Smallest Units Number</label>
                            <input type="number" class="form-control" wire:model.defer="smallestUnitsNumber">
                            @error('smallestUnitsNumber')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" wire:model.defer="isActive">
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" wire:model.defer="buyingPriceIncludesTax">
                                <label class="form-check-label">Buying Price Includes Tax</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" wire:model.defer="sellingPriceIncludesTax">
                                <label class="form-check-label">Selling Price Includes Tax</label>
                            </div>
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
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md">

                <!-- Modal Header -->
                <div class="bg-danger text-white px-4 py-3 rounded-t-lg flex justify-between items-center">
                    <h5 class="mb-0">Confirm Delete</h5>
                    <button class="btn-close btn-close-white" wire:click="$set('showDeleteModal', false)"></button>
                </div>

                <!-- Modal Body -->
                <div class="p-4">
                    <p>Are you sure you want to delete this unit?</p>
                </div>

                <!-- Modal Footer -->
                <div class="bg-light px-4 py-3 rounded-b-lg d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Cancel</button>
                    <button class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Delete Modal -->
    @if($showBulkDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md">

                <!-- Modal Header -->
                <div class="bg-danger text-white px-4 py-3 rounded-t-lg flex justify-between items-center">
                    <h5 class="mb-0">Confirm Bulk Delete</h5>
                    <button class="btn-close btn-close-white" wire:click="$set('showBulkDeleteModal', false)"></button>
                </div>

                <!-- Modal Body -->
                <div class="p-4">
                    <p>Are you sure you want to delete the selected <strong>{{ count($selectedUnits) }}</strong> units?</p>
                </div>

                <!-- Modal Footer -->
                <div class="bg-light px-4 py-3 rounded-b-lg d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary" wire:click="$set('showBulkDeleteModal', false)">Cancel</button>
                    <button class="btn btn-danger" wire:click="bulkDelete">Delete Selected</button>
                </div>
            </div>
        </div>
    @endif

    <script>
        window.addEventListener('focus-unit-name', () => {
            setTimeout(() => {
                document.getElementById('unit-name')?.focus();
            }, 50);
        });

        // Listen for PowerGrid bulk delete event
        window.addEventListener('bulkDelete.units-table-p5xboo-table', (event) => {
            const selectedIds = window.pgBulkActions.get(event.detail.table) || [];
            Livewire.dispatch('bulk-delete-units', { ids: selectedIds });
        });
    </script>
</div>
