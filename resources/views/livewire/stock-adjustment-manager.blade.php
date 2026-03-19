<div>
    @include('layouts.flash')
    @if ($showIndexPage)
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Stock Adjustments</h4>
                <div class="gap-2">
                    @if(auth()->user()->hasAnyRole(['super','admin','manager']))
                        <button wire:click="create" class="btn btn-primary">New Adjustment</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <livewire:tables.stock-adjustment-table/>
            </div>
        </div>
    @endif

    @if ($showAdjustmentCreationPage)
        <div class="card">
            <div class="card-header bg-primary text-white align-items-center d-flex justify-content-between">
                <h4>Create Stock Adjustment</h4>
                <button class="btn-close btn-close-white" wire:click="$set('showAdjustmentCreationPage', false); $set('showIndexPage', true)"></button>
            </div>

            <div class="card-body">

                <!-- Location -->
                <div class="mb-3">
                    <label>Location</label>
                    <select wire:model.live="location_id" class="form-control">
                        <option value="{{ null }}">Select Location</option>
                        @foreach($locations as $location)
                            <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <small class="text-danger">You must select A location</small>
                    @enderror
                </div>

                <!-- Search -->
                @if($location_id)
                    <div class="mb-3">
                        <label>Search Item</label>
                        <input type="text" wire:model.live="search" class="form-control" placeholder="Search item...">

                        @if(!empty($item_list))
                            <div class="list-group mt-2">
                                @foreach($item_list as $index => $item)
                                    <button type="button"
                                        class="list-group-item list-group-item-action"
                                        wire:click="addItem({{ $index }})">
                                        {{ $item['name'] }}
                                        (Stock: {{ $item['quantity'] }})
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Items Table -->
                @if(!empty($adjustment_items))
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Current</th>
                                <th>Adjustment</th>
                                <th>New Stock</th>
                                <th>Reason</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adjustment_items as $index => $item)
                                @php
                                    $newStock = 0;
                                    if($item['adjustment_qty'] != "" && $item['adjustment_qty'] != null){
                                        $newStock = $item['quantity'] + ($item['adjustment_qty'] ?? 0);
                                    }else{
                                        $newStock = $item['quantity'];
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $item['name'] }}</td>

                                    <td>{{ $item['quantity'] }}</td>

                                    <td>
                                        <input type="number" step="any"
                                            wire:model.live.debounce.100ms="adjustment_items.{{ $index }}.adjustment_qty"
                                            class="form-control">
                                        @error("adjustment_items.$index.adjustment_qty")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </td>

                                    <td class="{{ $newStock < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $newStock }}
                                    </td>

                                    <td>
                                        <input type="text"
                                            wire:model="adjustment_items.{{ $index }}.reason"
                                            class="form-control">
                                        @error("adjustment_items.$index.reason")
                                            <small class="text-danger">The reason is required!</small>
                                        @enderror
                                    </td>

                                    <td>
                                        <button class="btn btn-danger btn-sm"
                                            wire:click="removeItem({{ $index }})">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-danger">No items added</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">General description</label>
                        <textarea name="description" id="description" rows="2" class="form-control" placeholder="Add optional general description" wire:model='description'></textarea>
                    </div>
                @endif

            </div>
            <div class="card-footer">
                <div class="d-flex gap-2 justify-content-end">
                    <button class="btn btn-secondary" wire:click="$set('showAdjustmentCreationPage', false); $set('showIndexPage', true)">Close</button>
                    @if (count($adjustment_items) > 0)
                        <button class="btn btn-success" wire:click="confirmSave">
                            Save Adjustment
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if ($showViewAdjustment)
        <div class="card">
            <div class="card-header bg-info text-white align-items-center d-flex justify-content-between">
                <h4>View Stock Adjustment</h4>
                <button class="btn-close btn-close-white"
                    wire:click="$set('showViewAdjustment', false)">
                </button>
            </div>

            <div class="card-body">

                <p><strong>Location:</strong> {{ $viewAdjustmentData->location->name }}</p>
                <p><strong>Created By:</strong> {{ $viewAdjustmentData->createdBy->name }}</p>
                <p><strong>Date:</strong> {{ $viewAdjustmentData->created_at }}</p>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Before</th>
                            <th>Adjustment</th>
                            <th>After</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($viewAdjustmentData->items as $item)
                            <tr>
                                <td>{{ $item->item->name }}</td>
                                <td>{{ $item->current_stock }}</td>
                                <td class="{{ $item->adjustment_qty < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $item->quantity }}
                                </td>
                                <td>{{ $item->new_stock }}</td>
                                <td>{{ $item->reason }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    @endif

    @if ($showConfirmSave)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showConfirmSave', false)">
            <div class="card shadow-lg w-100" style="max-width: 500px; height: auto; padding:0px; border-width:0px" wire:click.stop>
                <!-- Header -->
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Confirm Adjustment</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showConfirmSave', false)"></button>
                </div>
                <div class="card-body">Are you sure you want to save this Adustment?</div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showConfirmSave', false)">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="save">Save</button>
                </div>
            </div>
        </div>
    @endif
</div>
