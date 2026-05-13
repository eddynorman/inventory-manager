<div>
    @include('layouts.flash')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">Items</div>
            <div class="card-tools">
                @if(auth()->user()->canAccess('items.create'))
                    <button wire:click="create" class="btn btn-primary">New Item</button>
                @endif
                @if(auth()->user()->canAccess('items.import'))
                    <button wire:click="$set('showBulkCreateModal', true)" class="btn btn-primary">
                        Bulk Upload Items
                    </button>
                @endif
            </div>
        </div>

        <div class="card-body table-responsive">
            <livewire:tables.item-table />
        </div>
    </div>

    <!-- Create/Edit Modal -->
@if($showModal)
    <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;">

        <div class="card shadow-lg w-100" style="max-width: 700px; height: 90vh;">
            <!-- Header -->
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">{{ $itemId ? 'Edit Item' : 'New Item' }}</h5>
                <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
            </div>

            <!-- Body (scrollable) -->
            <div class="card-body overflow-auto">
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
                    <div class="col-md-4">
                        <label for="item-category" class="form-label">Category</label>
                        <select id="item-category" class="form-select" wire:model.defer="categoryId">
                            <option value="">Select...</option>
                            @foreach($categories as $c)
                                <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                            @endforeach
                        </select>
                        @error('categoryId')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="location" class="form-label">Location</label>
                        <select id="location" class="form-select" wire:model.defer="newLocationId" {{ $itemId ? 'disabled' : '' }}>
                            <option value="">Select...</option>
                            @foreach($locations as $l)
                                <option value="{{ $l['id'] }}">{{ $l['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
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
                        <input id="item-initial-stock" type="number" class="form-control" wire:model.defer="initialStock" {{ $itemId ? 'disabled' : '' }}>
                        @error('initialStock')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="item-reorder-level" class="form-label">Reorder Level</label>
                        <input id="item-reorder-level" type="number" class="form-control" wire:model.defer="reorderLevel">
                        @error('reorderLevel')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
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
                    <div class="col-md-4 mt-3">
                        <div class="form-check form-switch">
                            <input id="is-sale-item" class="form-check-input" type="checkbox" wire:model.live="isSaleItem">
                            <label for="is-sale-item" class="form-check-label">Sale Item</label>
                        </div>
                    </div>
                    <div class="col-md-4 mt-3">
                        <div class="form-check form-switch">
                            <input id="is-stock-item" class="form-check-input" type="checkbox" wire:model.live="isStockItem">
                            <label for="is-stock-item" class="form-check-label">Stock Item</label>
                        </div>
                    </div>
                    <div class="col-md-4 mt-3">
                        <div class="form-check form-switch">
                            <input id="is-auto-tracked" class="form-check-input" type="checkbox" wire:model.live="isAutoTracked">
                            <label for="is-auto-tracked" class="form-check-label">Auto Track</label>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <small class="text-muted">Smallest unit will be created/updated automatically. Selling price must be greater than buying price.</small>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Close</button>
                <button type="button" class="btn btn-primary" wire:click="save">Save</button>
            </div>
        </div>
    </div>
@endif

<!-- Delete Modal -->
@if($showDeleteModal)
    <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
         style="background: rgba(0,0,0,0.5); z-index:1050;">

        <div class="card shadow-lg w-100" style="max-width: 500px;">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" wire:click="$set('showDeleteModal', false)"></button>
            </div>
            <div class="card-body">Are you sure you want to delete this item?</div>
            <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                <button class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Cancel</button>
                <button class="btn btn-danger" wire:click="delete">Delete</button>
            </div>
        </div>
    </div>
@endif

<!-- Bulk Delete Modal -->
@if($showBulkDeleteModal)
    <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
         style="background: rgba(0,0,0,0.5); z-index:1050;">

        <div class="card shadow-lg w-100" style="max-width: 500px;">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Confirm Bulk Delete</h5>
                <button type="button" class="btn-close btn-close-white" wire:click="$set('showBulkDeleteModal', false)"></button>
            </div>
            <div class="card-body">Are you sure you want to delete selected items?</div>
            <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                <button class="btn btn-secondary" wire:click="$set('showBulkDeleteModal', false)">Cancel</button>
                <button class="btn btn-danger" wire:click="bulkDelete">Delete</button>
            </div>
        </div>
    </div>
@endif

<!-- Assign Category Modal -->
@if($showAssignCategoryModal)
    <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
         style="background: rgba(0,0,0,0.5); z-index:1050;">

        <div class="card shadow-lg w-100" style="max-width: 500px;">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Assign Category</h5>
                <button type="button" class="btn-close btn-close-white" wire:click="$set('showAssignCategoryModal', false)"></button>
            </div>
            <div class="card-body">
                <label for="bulk-category-select" class="form-label">Category</label>
                <select id="bulk-category-select" class="form-select" wire:model.defer="bulkCategoryId">
                    <option value="">Select...</option>
                    @foreach($categories as $c)
                        <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                <button class="btn btn-secondary" wire:click="$set('showAssignCategoryModal', false)">Cancel</button>
                <button class="btn btn-primary" wire:click="assignCategoryToItems">Assign</button>
            </div>
        </div>
    </div>
@endif

<!-- Assign Supplier Modal -->
@if($showAssignSupplierModal)
    <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
         style="background: rgba(0,0,0,0.5); z-index:1050;">

        <div class="card shadow-lg w-100" style="max-width: 500px;">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Assign Supplier</h5>
                <button type="button" class="btn-close btn-close-white" wire:click="$set('showAssignSupplierModal', false)"></button>
            </div>
            <div class="card-body">
                <label for="bulk-supplier-select" class="form-label">Supplier</label>
                <select id="bulk-supplier-select" class="form-select" wire:model.defer="bulkSupplierId">
                    <option value="">Select...</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                <button class="btn btn-secondary" wire:click="$set('showAssignSupplierModal', false)">Cancel</button>
                <button class="btn btn-primary" wire:click="assignSupplierToItems">Assign</button>
            </div>
        </div>
    </div>
@endif

@if ($showBulkCreateModal)
<div class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
     style="background: rgba(0,0,0,0.6); z-index:1050;"
     wire:click="$set('showBulkCreateModal', false)">

    <div class="card shadow-lg border-0 w-100"
         style="max-width: 650px; border-radius: 12px;"
         wire:click.stop>

        {{-- Header --}}
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center"
             style="border-top-left-radius:12px; border-top-right-radius:12px;">
            <div>
                <h5 class="mb-0 fw-bold">Bulk Create Items</h5>
                <small class="opacity-75">Upload items using Excel template</small>
            </div>
            <button class="btn-close btn-close-white"
                    wire:click="$set('showBulkCreateModal', false)">
            </button>
        </div>

        {{-- Body --}}
        <div class="card-body p-4" style="max-height:75vh; overflow-y:auto;">

            {{-- Step 1: Instructions --}}
            <div class="mb-4">
                <h6 class="fw-bold mb-2">📄 Instructions</h6>
                <ul class="small text-muted mb-2">
                    <li>Download the template and fill required fields.</li>
                    <li><strong>Category:</strong> auto-created if not found.</li>
                    <li><strong>Barcode & Supplier:</strong> optional.</li>
                    <li><strong>Booleans:</strong> use <code>true</code>/<code>false</code> or <code>t</code>/<code>f</code>.</li>
                    <li>Do not rename columns.</li>
                </ul>

                <button type="button"
                        class="btn btn-outline-primary btn-sm"
                        wire:click="downloadTemplate">
                    ⬇ Download Template
                </button>
            </div>

            <hr>

            {{-- Step 2: Department --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">🏢 Department</label>
                <select wire:model="departmentId" class="form-select">
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                    @endforeach
                </select>
                @error('departmentId')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Step 3: File Upload --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">📤 Upload Excel File</label>
                <input type="file" wire:model="bulkFile" class="form-control">

                <div wire:loading wire:target="bulkFile" class="small text-primary mt-1">
                    Uploading...
                </div>

                @error('bulkFile')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Errors --}}
            @if(!empty($bulkErrors))
                <div class="alert alert-danger mt-3" style="border-radius:8px;">
                    <div class="fw-bold mb-2">⚠ Import Errors</div>
                    <ul class="mb-0 small">
                        @foreach($bulkErrors as $error)
                            <li>
                                <strong>Row {{ $error['row'] }}:</strong> {{ $error['error'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>

        {{-- Footer --}}
        <div class="card-footer bg-light d-flex justify-content-between align-items-center px-4 py-3"
             style="border-bottom-left-radius:12px; border-bottom-right-radius:12px;">

            <button class="btn btn-outline-secondary"
                    wire:click="$set('showBulkCreateModal', false)">
                Cancel
            </button>

            <button class="btn btn-success d-flex align-items-center gap-2"
                    wire:click="bulkSave"
                    wire:loading.attr="disabled">

                <span wire:loading.remove wire:target="bulkSave">
                    💾 Save Items
                </span>

                <span wire:loading wire:target="bulkSave">
                    Processing...
                </span>
            </button>
        </div>

    </div>
</div>
@endif
</div>
