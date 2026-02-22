<div>
    @include('layouts.flash')
    @if($showListTable)
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Requisitions</h5>
        @if(auth()->user()->hasAnyRole(['super','admin','manager']))
            <button wire:click="create" class="btn btn-primary">New Requisition</button>
        @endif
    </div>
    <div class="card">
        <div class="table-responsive p-3">
            <livewire:tables.requisitions-table/>
        </div>
    </div>
    @endif

    <!-- Create/Edit page -->
    @if ($showCreateEditPage)
        <div id="create-edit-page" class="card p-0 ">
            <div class="card-header d-flex bg-primary align-items-center justify-content-between">
                <h2 class=" text-white">
                    {{ $reqId ? 'Edit': 'Create' }} Requisition
                </h2>
                <button type="button" class="btn-close btn-close-white btn-xxl" wire:click="$set('showCreateEditPage', false)"></button>
            </div>
            <div class="row">
                <div class="mb-3 col-md-6 position-relative">
                    <label class="form-label">Items</label>

                    <input type="text"
                        class="form-control"
                        wire:model.live="search"
                        placeholder="Search items to add...">

                    @if(!empty($searchItems))
                        <div class="position-absolute w-100"
                            style="z-index: 2100; max-height: 250px; overflow-y: auto;">

                            <ul class="list-group shadow">
                                @foreach ($searchItems as $item)
                                    <li class="list-group-item list-group-item-action"
                                        style="cursor: pointer;"
                                        wire:click="addItem({{ $item['id'] }})">
                                        {{ $item['name'] }}
                                    </li>
                                @endforeach
                            </ul>

                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label for="department" class="form-label">Deparment</label>
                    <select wire:model.live='department_id' name="department" id="department" class="form-select">
                        <option value="{{ null }}">--Select Department--</option>
                        @foreach ($departments as $department )
                            <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <div class="error small text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <form action="" method="post" wire:submit.prevent="save">
                <table class="table table-bordered table-striped table-responsive">
                    <thead class="table-dark">
                        <tr>
                            <th>ITEM NAME</th>
                            <th>CURRENT STOCK</th>
                            <th>REQ QUANTITY</th>
                            <th>REQ UNIT</th>
                            <th>REQ UNIT PRICE</th>
                            <th>TOTAL</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $index => $item)
                        <tr wire:key="item-{{ $item['item_id'] }}">
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['current_stock'] }}</td>
                            <td>
                                <input type="number" name="quantity" wire:model.live="items.{{ $index }}.quantity" class="form-control" min="1" >
                                @error("items.$index.quantity")
                                    <div class="danger text-small">{{ $message }}</div>
                                @enderror
                            </td>
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
                                <input type="number" name="unit_price" wire:model.live="items.{{ $index }}.unit_price" class="form-control" min="1" >
                                @error("items.$index.unit_price")
                                    <div class="danger text-small">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>{{ number_format($item['total'],2) }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    wire:click="removeItem({{ $item['item_id'] }})">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                        @if (count($items) == 0)
                        <tr>
                            <td class="text-danger" colspan="7">No Items Added</td>
                        </tr>
                        @endif
                        @if (count($items) >= 1)
                        <tr>
                            <td colspan="5" class="text-end"><b>TOTAL</b></td>
                            <td colspan="2" class="text-start">
                                <b>{{ number_format($cost,2) }}</b>
                            </td>
                        </tr>
                        @endif
                        @if ($errors->has('items') || $errors->has('items.*.*'))
                            <tr>
                                <td colspan="7" class="text-danger small">
                                    {{ $errors->first('items') ?? $errors->first('items.*.*') }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" wire:model.defer='description' class="form-control"></textarea>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showCreateEditPage', false)">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="save">Save</button>
                </div>
            </form>

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
                <div class="card-body">Are you sure you want to delete requisition {{ str_pad($reqId, 5, "0", STR_PAD_LEFT) }}?</div>
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


