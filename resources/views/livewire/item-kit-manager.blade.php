<div>
    @include('layouts.flash')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Item Kits</h5>
        @if(auth()->user()->hasAnyRole(['super','admin','manager']))
            <button wire:click="create" class="btn btn-primary">New Kit</button>
        @endif
    </div>

    <div class="card">
        <div class="table-responsive p-3">
            <livewire:tables.item-kit-table/>
        </div>
    </div>

    @if($showViewKitModal)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showViewKitModal', false)">

            <div class="card shadow-lg w-100" style="max-width: 700px; height: auto; border-width:0px;" wire:click.stop>
                <!-- Header -->
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Kit Details</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showViewKitModal', false)"></button>
                </div>
                <div class="card-body overflow-auto p-3">
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-5"><p><b>Name:</b> <i>{{ $name }}</i></p></div>
                            <div class="col-md-4"><p><b>Price:</b> <i>{{ $selling_price }}</i></p></div>
                            <div class="col-md-3"><p><b>Tax incl:</b> <i>{{$selling_price_includes_tax?'Yes':"No"}}</i></p></div>
                        </div>
                        <p><b>Items:</b></p>
                        <table class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $index => $item)
                                    <tr wire:key="item-{{ $item['item_id'] }}">
                                        <td>{{ $item['name'] }}</td>
                                        <td>{{ $item['unit'] }}</td>
                                        <td>{{ $item['quantity'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                    <div class="mb-3">
                        <p><b>Description:</b></p>
                        <p>{{$description}}</p>
                    </div>
                </div>
                    <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showViewKitModal', false)">Close</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showKitModal)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showKitModal', false)">

            <div class="card shadow-lg w-100" style="max-width: 700px; height: 90vh;" wire:click.stop>
                <!-- Header -->
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">{{ $kitId ? 'Edit Kit' : 'New Kit' }}</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showKitModal', false)"></button>
                </div>
                <div class="card-body overflow-auto p-3">
                    <div class="mb-3">
                        <label class="form-label">Items</label>
                        <input type="text" class="form-control" wire:model.live="searchItem" placeholder="Search items to add...">
                        <div style="z-index:2100">
                            <ul class="list-group">
                                @foreach ($searchItems as $item)
                                    <li class="list-group-item" wire:click="addItem({{ $item['id'] }})">{{ $item['name'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <table class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $index => $item)
                                    <tr wire:key="item-{{ $item['item_id'] }}">
                                        <td>{{ $item['name'] }}</td>

                                        <td>
                                            <select class="form-select"
                                                    wire:model.live="items.{{ $index }}.selected_unit_id">
                                                @foreach($item['units'] as $unit)
                                                    <option value="{{ $unit['id'] }}">
                                                        {{ $unit['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("items.$index.selected_unit_id")
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </td>

                                        <td>
                                            <input type="number"
                                                class="form-control"
                                                wire:model.live="items.{{ $index }}.quantity">
                                            @error("items.$index.quantity")
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </td>

                                        <td>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    wire:click="removeItem({{ $item['item_id'] }})">
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($errors->has('items') || $errors->has('items.*.*'))
                                    <tr>
                                        <td colspan="4" class="text-danger small">
                                            {{ $errors->first('items') ?? $errors->first('items.*.*') }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>

                        </table>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" wire:model.defer="name">
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Selling Price</label>
                        <input type="number" step="0.01" class="form-control" wire:model.defer="selling_price">
                        @error('selling_price')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Includes Tax</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="kitTaxSwitch" wire:model.defer="selling_price_includes_tax">
                            <label class="form-check-label" for="kitTaxSwitch">Yes</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" wire:model.defer="description"></textarea>
                    </div>
                </div>
                    <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showKitModal', false)">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="save">Save</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Modal -->
    @if($showDeleteModal)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showDeleteModal', false)">

            <div class="card shadow-lg w-100" style="max-width: 500px; height: auto; padding:0px; border-width:0px" wire:click.stop>
                <!-- Header -->
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showDeleteModal', false)"></button>
                </div>
                <div class="card-body">Are you sure you want to delete this kit?</div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    @endif
    <!-- Bulk Delete Modal -->
    @if($showBulkDeleteModal)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showBulkDeleteModal', false)">

            <div class="card shadow-lg w-100" style="max-width: 500px; height: auto; padding:0px; border-width:0px" wire:click.stop>
                <!-- Header -->
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showBulkDeleteModal', false)"></button>
                </div>
                <div class="card-body">Are you sure you want to delete {{ count($selectedIds) }} kits?</div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showBulkDeleteModal', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="bulkDelete">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>


