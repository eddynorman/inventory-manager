<div>
    @include('layouts.flash')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">Items</div>
            <div class="card-tools">
                @if(auth()->user()->hasAnyRole(['super','admin','manager']))
                    <button wire:click="create" class="btn btn-primary">New Item</button>
                @endif
            </div>
        </div>

        <div class="card-body tale-responsive">
            <livewire:tables.item-table />
        </div>
    </div>

    <!-- Create/Edit Modal (custom modal, livewire controlled) -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" role="dialog" aria-modal="true">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl">

                <!-- Modal Header -->
                <div class="bg-primary text-white px-4 py-3 rounded-t-lg d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $itemId ? 'Edit Item' : 'New Item' }}</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="item-name" class="form-label">Name</label>
                            <input id="item-name" type="text" class="form-control" wire:model.defer="name">
                            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="item-barcode" class="form-label">Barcode</label>
                            <input id="item-barcode" type="text" class="form-control" wire:model.defer="barcode">
                            @error('barcode')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="item-category" class="form-label">Category</label>
                            <select id="item-category" class="form-select" wire:model.defer="categoryId">
                                <option value="">Select...</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                                @endforeach
                            </select>
                            @error('categoryId')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="item-supplier" class="form-label">Supplier</label>
                            <select id="item-supplier" class="form-select" wire:model.defer="supplierId">
                                <option value="">Select...</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                @endforeach
                            </select>
                            @error('supplierId')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="item-initial-stock" class="form-label">Initial Stock</label>
                            <input id="item-initial-stock" type="number" class="form-control" wire:model.defer="initialStock">
                            @error('initialStock')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="item-reorder-level" class="form-label">Reorder Level</label>
                            <input id="item-reorder-level" type="number" class="form-control" wire:model.defer="reorderLevel">
                            @error('reorderLevel')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <!-- Smallest Unit Info -->
                        <div class="col-md-4">
                            <label for="smallest-unit" class="form-label">Smallest Unit Name</label>
                            <input id="smallest-unit" type="text" class="form-control" wire:model.defer="smallestUnit">
                            @error('smallestUnit')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="buying-price" class="form-label">Buying Price</label>
                            <input id="buying-price" type="number" step="0.01" class="form-control" wire:model.defer="buyingPrice">
                            @error('buyingPrice')<div class="text-danger small">{{ $message }}</div>@enderror

                            <div class="form-check form-switch mt-2">
                                <input id="buying-includes-tax" class="form-check-input" type="checkbox" wire:model.defer="buyingPriceIncludesTax">
                                <label for="buying-includes-tax" class="form-check-label">Buying Price Includes Tax</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="selling-price" class="form-label">Selling Price</label>
                            <input id="selling-price" type="number" step="0.01" class="form-control" wire:model.defer="sellingPrice">
                            @error('sellingPrice')<div class="text-danger small">{{ $message }}</div>@enderror

                            <div class="form-check form-switch mt-2">
                                <input id="selling-includes-tax" class="form-check-input" type="checkbox" wire:model.defer="sellingPriceIncludesTax">
                                <label for="selling-includes-tax" class="form-check-label">Selling Price Includes Tax</label>
                            </div>
                        </div>

                        <div class="col-md-6 mt-3">
                            <div class="form-check form-switch">
                                <input id="is-sale-item" class="form-check-input" type="checkbox" wire:model.defer="isSaleItem">
                                <label for="is-sale-item" class="form-check-label">Sale Item</label>
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            {{-- Optionally display help text --}}
                            <small class="text-muted">Smallest unit will be created/updated automatically. Selling price must be greater than buying price.</small>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="d-flex justify-content-end gap-2 p-4 border-top">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="save">Save</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" role="dialog" aria-modal="true">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-4">
                <div class="flex justify-between items-center mb-3">
                    <h5 class="text-lg font-semibold">Confirm Delete</h5>
                    <button type="button" class="btn-close" wire:click="$set('showDeleteModal', false)" aria-label="Close"></button>
                </div>
                <div>Are you sure you want to delete this item?</div>
                <div class="flex justify-end gap-2 mt-4">
                    <button class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Cancel</button>
                    <button class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Delete Modal -->
    @if($showBulkDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-4">
                <div class="flex justify-between items-center mb-3">
                    <h5 class="text-lg font-semibold">Confirm Bulk Delete</h5>
                    <button type="button" class="btn-close" wire:click="$set('showBulkDeleteModal', false)" aria-label="Close"></button>
                </div>
                <div>Are you sure you want to delete selected items?</div>
                <div class="flex justify-end gap-2 mt-4">
                    <button class="btn btn-secondary" wire:click="$set('showBulkDeleteModal', false)">Cancel</button>
                    <button class="btn btn-danger" wire:click="bulkDelete">Delete</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Assign Category Modal -->
    @if($showAssignCategoryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-4">
                <div class="flex justify-between items-center mb-3">
                    <h5 class="text-lg font-semibold">Assign Category</h5>
                    <button type="button" class="btn-close" wire:click="$set('showAssignCategoryModal', false)" aria-label="Close"></button>
                </div>
                <div>
                    <label for="bulk-category-select" class="form-label">Category</label>
                    <select id="bulk-category-select" class="form-select" wire:model.defer="bulkCategoryId">
                        <option value="">Select...</option>
                        @foreach($categories as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button class="btn btn-secondary" wire:click="$set('showAssignCategoryModal', false)">Cancel</button>
                    <button class="btn btn-primary" wire:click="assignCategoryToItems">Assign</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Assign Supplier Modal -->
    @if($showAssignSupplierModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-4">
                <div class="flex justify-between items-center mb-3">
                    <h5 class="text-lg font-semibold">Assign Supplier</h5>
                    <button type="button" class="btn-close" wire:click="$set('showAssignSupplierModal', false)" aria-label="Close"></button>
                </div>
                <div>
                    <label for="bulk-supplier-select" class="form-label">Supplier</label>
                    <select id="bulk-supplier-select" class="form-select" wire:model.defer="bulkSupplierId">
                        <option value="">Select...</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button class="btn btn-secondary" wire:click="$set('showAssignSupplierModal', false)">Cancel</button>
                    <button class="btn btn-primary" wire:click="assignSupplierToItems">Assign</button>
                </div>
            </div>
        </div>
    @endif

</div>

<script>
    // PowerGrid bulk handlers - fetch selected IDs from pgBulkActions and emit to Livewire
    window.addEventListener('bulkDelete.item-table-effbnx-table', (event) => {
        const selectedIds = window.pgBulkActions?.get(event.detail.table) || [];
        Livewire.emit('bulk-delete-items', selectedIds);
    });

    window.addEventListener('bulkToggleActive.item-table-effbnx-table', (event) => {
        const selectedIds = window.pgBulkActions?.get(event.detail.table) || [];
        Livewire.emit('bulk-toggle-active-items', selectedIds);
    });

    window.addEventListener('bulkAssignCategory.item-table-effbnx-table', (event) => {
        const selectedIds = window.pgBulkActions?.get(event.detail.table) || [];
        Livewire.emit('bulk-assign-category-items', selectedIds);
    });

    window.addEventListener('bulkAssignSupplier.item-table-effbnx-table', (event) => {
        const selectedIds = window.pgBulkActions?.get(event.detail.table) || [];
        Livewire.emit('bulk-assign-supplier-items', selectedIds);
    });

    // Allow server dispatched browser events to show/hide/flash UI
    // window.addEventListener('flash', () => {
    //     // existing flash script uses jQuery; call the same if present
    //     if (window.jQuery) {
    //         $('.alert').fadeTo(2000, 500).slideUp(500, function(){
    //             $(this).remove();
    //         });
    //     }
    // });

    // Powergrid refresh event handler is handled via the component's dispatchBrowserEvent
</script>
