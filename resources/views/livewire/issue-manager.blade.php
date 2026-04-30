<div>
    @include('layouts.flash')
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Issues</h5>
                <small class="text-muted">Manage transfer requests(Issues)</small>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm"
                    wire:click="newIssue">
                    New Issue
                </button>

            </div>
        </div>
        <div class="card-body">
            <livewire:tables.issue-table/>
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
                        Create Issue
                    </h2>
                    <button type="button" class="btn-close btn-close-white btn-xxl" wire:click="$set('showCreatePage', false)"></button>
                </div>
                <div class="card-body" style="max-height:70vh; overflow-y:auto;">
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
                                                wire:click="addItem({{ $item['item_id'] }})">
                                                {{ $item['name'] }}
                                            </li>
                                        @endforeach
                                    </ul>

                                </div>
                            @endif
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-3">
                            <label for="destination" class="form-label">Destination</label>
                            <select wire:model.live='destinationId' name="destination" id="destination" class="form-select">
                                <option value="{{ null }}">--Select Destination--</option>
                                @foreach ($locations as $location )
                                    <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
                                @endforeach
                            </select>
                            @error('destinationId')
                                <div class="error small text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <form action="" method="post" wire:submit.prevent="save">
                        <table class="table table-bordered table-striped table-responsive">
                            <thead class="table-dark">
                                <tr>
                                    <th>ITEM NAME</th>
                                    <th>STOCK</th>
                                    <th>QUANTITY</th>
                                    <th>UNIT</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($issueItems as $index => $item)
                                <tr wire:key="item-{{ $item['item_id'] }}">
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['stock'] }}</td>
                                    <td style="width:120px;">
                                        <input type="number" name="quantity" wire:model.live.debounce.500ms="issueItems.{{ $index }}.quantity" class="form-control @error("issueItems.$index.quantity") is-invalid @enderror" min="1" >
                                        @error("issueItems.$index.quantity")
                                            <small class="text-danger text-small">{{ $message }}</small>
                                        @enderror
                                    </td>
                                    <td>
                                        <select class="form-select"
                                                wire:model.live="issueItems.{{ $index }}.selected_unit_id">
                                            @foreach($item['units'] as $unit)
                                                <option value="{{ $unit['id'] }}">
                                                    {{ $unit['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("issueItems.$index.selected_unit_id")
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
                                @if (count($issueItems) == 0)
                                <tr>
                                    <td class="text-danger" colspan="5">No Items Added</td>
                                </tr>
                                @endif
                                @if ($errors->has('issueItems') || $errors->has('issueItems.*.*'))
                                    <tr>
                                        <td colspan="5" class="text-danger small">
                                            {{ $errors->first('issueItems') ?? $errors->first('issueItems.*.*') }}
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
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showCreatePage', false)">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="save">Save</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showViewIssue)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
                style="background: rgba(0,0,0,0.5); z-index:2100;"
                wire:click="$set('showViewIssue', false)">
            <div id="create-edit-page" class="card shadow-lg w-100 p-0 " style="max-width: 800px;" wire:click.stop>
                <div class="card-header d-flex bg-primary align-items-center justify-content-between">
                    <h4 class=" text-white">
                        View Issue #{{ $viewIssue['issue_number'] }}
                    </h4>
                    <button type="button" class="btn-close btn-close-white btn-xxl" wire:click="$set('showViewIssue', false)"></button>
                </div>
                <div class="card-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="row">
                        <div class="col-md-4">Source: <b>{{$viewIssue['source']}}</b></div>
                        <div class="col-md-4">Destination: <b>{{$viewIssue['destination']}}</b></div>
                        <div class="col-md-4">
                            <div>
                                <span class="badge bg-{{ $viewIssue['color'] }}">{{ ucfirst($viewIssue['status']) }}</span>
                                <small class="text-muted d-block">{{ $viewIssue['next'] }}</small>
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
                                        @foreach ($viewIssue['items'] as $index => $item)
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
                            <small class="text-primary d-block">Issuer: <b>{{ $viewIssue['requested_by'] }}</b></small>
                            <small class="text-muted">On {{ $viewIssue['requested_at'] }}</small>
                        </div>
                        <div class="col-md-4">
                            @if(isset($viewIssue['processed_by']))
                                <small class="text-success d-block">Processed By: <b>{{ $viewIssue['processed_by'] }}</b></small>
                                <small class="text-muted">On {{ $viewIssue['processed_at'] }}</small>
                            @endif
                        </div>
                        <div class="col-md-4">
                            @if(isset($viewIssue['rejected_by']))
                                <small class="text-danger d-block">Rejected By: <b>{{ $viewIssue['rejected_by'] }}</b></small>
                                <small class="text-muted">On {{ $viewIssue['rejected_at'] }}</small>
                            @endif
                        </div>
                        <div class="col-md-12 text-danger">
                            @if(isset($viewIssue['rejected_by']))
                                {{ $viewIssue['rejection_reason'] }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showViewIssue', false)">Close</button>
                    @if (!(isset($viewIssue['processed_by']) || isset($viewIssue['rejected_by'])) && $user_id != $current_user_id )
                        <button type="button" class="btn btn-danger" wire:click="$set('showRejectIssue', true)">Reject</button>
                    @endif
                </div>
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
