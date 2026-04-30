<div>
    @include('layouts.flash')
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Transfers</h5>
                <small class="text-muted">Manage item transfers</small>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm"
                    wire:click="newTransfer">
                    New Transfer
                </button>

            </div>
        </div>
        <div class="card-body">
            <livewire:tables.transfer-table/>
        </div>
    </div>

    <!-- Create/Edit page -->
    @if ($showCreatePage)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
                style="background: rgba(0,0,0,0.5); z-index:2100;"
                wire:click="$set('showCreatePage', false)">
            <div id="create-edit-page" class="card shadow-lg w-100 p-0 " style="max-width: 800px;" wire:click.stop>
                <div class="card-header d-flex bg-primary align-items-center justify-content-between">
                    <h2 class=" text-white">
                        Create Transfer
                    </h2>
                    <button type="button" class="btn-close btn-close-white btn-xxl" wire:click="$set('showCreatePage', false)"></button>
                </div>
                <div class="card-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="location" class="form-label">Source</label>
                            <select wire:model.live='locationId' name="location" id="location" class="form-select">
                                <option value="{{ null }}">--Select Location--</option>
                                @foreach ($locations as $location )
                                    <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
                                @endforeach
                            </select>
                            @error('locationId')
                                <div class="error small text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="issue-id" class="form-label">Select Issue</label>
                            <select wire:model.live='selectedIssue' name="destination" id="issue-id" class="form-select">
                                <option value="{{ null }}">--Select Issue--</option>
                                @foreach ($issues as $issue )
                                    <option value="{{ $issue['id'] }}">#{{str_pad($issue['id'],5,'0',STR_PAD_LEFT)}} From: {{ $issue['from_location']['name'] }}</option>
                                @endforeach
                            </select>
                            @error('selectedIssue')
                                <div class="error small text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if(count($transferItems) > 0)
                        <form action="" method="post" wire:submit.prevent="save">
                            <table class="table table-bordered table-striped table-responsive">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ITEM NAME</th>
                                        <th>REQ QUANTITY</th>
                                        <th>REQ UNIT</th>
                                        <th>QUANTITY</th>
                                        <th>UNIT</th>
                                        <th>ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transferItems as $index => $item)
                                    <tr wire:key="item-{{ $item['item_id'] }}">
                                        <td>{{ $item['name'] }}</td>
                                        <td>{{ $item['requested_quantity'] }}</td>
                                        <td>{{ $item['requested_unit'] }}</td>
                                        <td style="width:120px;">
                                            <input type="number" name="quantity" wire:model.live.debounce.500ms="transferItems.{{ $index }}.quantity" class="form-control @error("transferItems.$index.quantity") is-invalid @enderror" min="1" >
                                            @error("transferItems.$index.quantity")
                                                <small class="text-danger text-small">{{ $message }}</small>
                                            @enderror
                                        </td>
                                        <td>
                                            <select class="form-select"
                                                    wire:model.live="transferItems.{{ $index }}.selected_unit_id">
                                                @foreach($item['units'] as $unit)
                                                    <option value="{{ $unit['id'] }}">
                                                        {{ $unit['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("transferItems.$index.selected_unit_id")
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </td>

                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                wire:click="removeItem({{ $item['item_id'] }})">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if (count($transferItems) == 0)
                                    <tr>
                                        <td class="text-danger" colspan="5">No Items Added</td>
                                    </tr>
                                    @endif
                                    @if ($errors->has('transferItems') || $errors->has('transferItems.*.*'))
                                        <tr>
                                            <td colspan="5" class="text-danger small">
                                                {{ $errors->first('transferItems') ?? $errors->first('transferItems.*.*') }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" wire:model.defer='description' class="form-control"></textarea>
                            </div>
                        </form>
                    @endif
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showCreatePage', false)">Close</button>
                    @if (count($transferItems) > 0 && $user_id != $current_user_id)
                        <button type="button" class="btn btn-danger" wire:click="$set('showRejectIssue', true)">Reject</button>
                        <button type="button" class="btn btn-primary" wire:click="save">Save</button>
                    @endif

                </div>
            </div>
        </div>
    @endif

    @if ($showViewTransfer)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
                style="background: rgba(0,0,0,0.5); z-index:2100;"
                wire:click="$set('showViewTransfer', false)">
            <div id="create-edit-page" class="card shadow-lg w-100 p-0 " style="max-width: 800px;" wire:click.stop>
                <div class="card-header d-flex bg-primary align-items-center justify-content-between">
                    <h4 class=" text-white">
                        View Transfer #{{ $viewTransfer['transfer_number'] }}
                    </h4>
                    <button type="button" class="btn-close btn-close-white btn-xxl" wire:click="$set('showViewTransfer', false)"></button>
                </div>
                <div class="card-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="row">
                        <div class="col-md-4">Source: <b>{{$viewTransfer['source']}}</b></div>
                        <div class="col-md-4">Destination: <b>{{$viewTransfer['destination']}}</b></div>
                        <div class="col-md-4">
                            <div>
                                <span class="badge bg-{{ $viewTransfer['color'] }}">{{ ucfirst($viewTransfer['status']) }}</span>
                                <small class="text-muted d-block">{{ $viewTransfer['next'] }}</small>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card shadow-sm mb-3 mt-3 p-1">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Item</th>
                                            <th>Unit</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($viewTransfer['items'] as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item['name'] }}</td>
                                                <td>{{ $item['unit'] }}</td>
                                                <td>{{ $item['quantity'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-4">
                            @if(isset($viewTransfer['processed_by']))
                                <small class="text-success d-block">Processed By: <b>{{ $viewTransfer['processed_by'] }}</b></small>
                                <small class="text-muted">On {{ $viewTransfer['processed_at'] }}</small>
                            @endif
                        </div>
                        <div class="col-md-4">
                            @if(isset($viewTransfer['rejected_by']))
                                <small class="text-danger d-block">Rejected By: <b>{{ $viewTransfer['rejected_by'] }}</b></small>
                                <small class="text-muted">On {{ $viewTransfer['rejected_at'] }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showViewTransfer', false)">Close</button>
                    @if(!isset($viewTransfer['received_by']) && $user_id != $current_user_id)
                        <button type="button" class="btn btn-success" wire:click="receiveTransfer">Receive</button>
                    @endif
            </div>
        </div>
    @endif

    <!-- Rejection Reason Modal -->
    @if($showRejectIssue)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showRejectIssue', false)">

            <div class="card shadow-lg w-100" style="max-width: 600px; height: auto; padding:0px; border-width:0px" wire:click.stop>
                <!-- Header -->
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Enter Rejection reason</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showRejectIssue', false)"></button>
                </div>
                <div class="card-body">
                    <textarea name="rejection-reason" id="rejection-reason" class="form-control" wire:model.defer='rejectionReason' placeholder="Enter Reason..."></textarea>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showRejectIssue', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="rejectIssue">Reject</button>
                </div>
            </div>
        </div>
    @endif
</div>
