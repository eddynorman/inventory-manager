<div>
    @include('layouts.flash')
    @if($showListTable)
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Requisitions</h5>
            @if(auth()->user()->hasAnyRole(['super','admin','manager']))
                <button wire:click="create" class="btn btn-primary">New Requisition</button>
            @endif
        </div>
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
            <div class="card-body">
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
                                    <input type="number" name="quantity" wire:model.live.debounce.500ms="items.{{ $index }}.quantity" class="form-control" min="1" >
                                    @error("items.$index.quantity")
                                        <div class="danger text-small">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <select class="form-select"
                                            wire:model.live.debounce.500ms="items.{{ $index }}.selected_unit_id">
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
                                    <input type="number" name="unit_price" wire:model.live.debounce.500ms="items.{{ $index }}.unit_price" class="form-control" min="1" >
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
                </form>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                <button type="button" class="btn btn-secondary" wire:click="$set('showCreateEditPage', false)">Close</button>
                <button type="button" class="btn btn-primary" wire:click="save">Save</button>
            </div>
        </div>
    @endif

    <!-- View page -->
    @if ($showViewPage)
        <div id="view-page">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-4">
                            <h1 style="font-size:2rem">Requisition - {{ str_pad($reqId,5,"0",STR_PAD_LEFT) }}</h1>
                        </div>
                        <div class="col-md-8">
                            <div class="progress">
                                <div class="progress-bar @if ($status=='rejected')
                                    bg-danger
                                @else
                                    bg-success
                                @endif"
                                    role="progressbar"
                                    style="width: {{ ($currentStep / 4) * 100 }}%">
                                </div>

                            </div>
                            <div class="d-flex justify-content-between mt-2 small text-muted">
                                <span>Requested</span>
                                <span>Reviewed</span>
                                <span>Approved</span>
                                <span>
                                    @if($status=='rejected')
                                        Rejected
                                    @else
                                        Funded
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped table-responsive">
                        <thead class="table-dark">
                            <tr>
                                <th>ITEM NAME</th>
                                <th>CURRENT STOCK</th>
                                <th>REQ QUANTITY</th>
                                <th>REQ UNIT</th>
                                <th>REQ UNIT PRICE</th>
                                <th>TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $index => $item)
                            <tr wire:key="item-{{ $item['item_id'] }}">
                                <td>{{ $item['name'] }}</td>
                                <td>{{ $item['current_stock'] }}</td>
                                <td>{{ $item['quantity'] }}</td>
                                <td>{{ $item['selected_unit_name'] }}</td>
                                <td>{{ number_format($item['unit_price'],2) }}</td>
                                <td>{{ number_format($item['total'],2) }}</td>
                            </tr>
                            @endforeach
                            @if (count($items) >= 1)
                            <tr>
                                <td colspan="5" class="text-end"><b>TOTAL</b></td>
                                <td colspan="2" class="text-start">
                                    <b>{{ number_format($cost,2) }}</b>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-md-4 text-muted">{{ $department_name }} Department</div>
                        <div class="col-md-4 text-muted">Requested by {{ $requested_by_name }}</div>
                        <div class="col-md-4 text-muted">Requested on {{ $date_requested }}</div>
                    </div>
                    @if($status != 'pending')
                        <div id="progress-info">
                            <div class="mt-4 p-3 bg-light rounded">

                                <h6 class="fw-bold mb-3">Process Tracking</h6>
                                <hr>

                                <div class="row small">

                                    @if($reviewed_by_name)
                                        <div class="col-md-3">
                                            <div class="text-muted">Reviewed By</div>
                                            <div class="fw-semibold">{{ $reviewed_by_name }}</div>
                                        </div>
                                    @endif

                                    @if($approved_by_name)
                                        <div class="col-md-3">
                                            <div class="text-muted">Approved By</div>
                                            <div class="fw-semibold">{{ $approved_by_name }}</div>
                                        </div>
                                    @endif

                                    @if($funded_by_name)
                                        <div class="col-md-3">
                                            <div class="text-muted">Funded By</div>
                                            <div class="fw-semibold text-success">
                                                {{ $funded_by_name }}
                                            </div>
                                        </div>
                                    @endif

                                    @if($rejected_by_name)
                                        <div class="col-md-3">
                                            <div class="text-muted">Rejected By</div>
                                            <div class="fw-semibold text-danger">
                                                {{ $rejected_by_name }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-small text-muted pt-2">
                                    @if ($status == 'rejected')
                                        Rejection reason : <b class="text-danger">{{ $rejectionReason }}</b>
                                    @endif
                                    @if ($status == 'funded')
                                        Fund amount : <b class="text-success"> {{ $fund_amount }} Funded To: {{ $funded_to_name }}</b>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($description != "")
                        <div class="mt-4">
                            <h6 class="fw-bold">Description</h6>
                            <div class="border rounded p-3 bg-white">
                                {{ $description }}
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showViewPage', false)">Close</button>
                    @if ($canReject)
                        <button type="button" class="btn btn-danger" wire:click="enterRejectionReason">Reject</button>
                    @endif
                    @if ($canReview)
                        <button type="button" class="btn btn-info" wire:click="markAsReviewed">Mark as Reviewed</button>
                    @endif
                    @if ($canApprove)
                        <button type="button" class="btn btn-primary" wire:click="approve">Approve</button>
                    @endif
                    @if ($canFund)
                        <button type="button" class="btn btn-success" wire:click="showFundAmountModal">Fund</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Rejection Reason Modal -->
    @if($showRejectionReasonModal)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showRejectionReasonModal', false)">

            <div class="card shadow-lg w-100" style="max-width: 600px; height: auto; padding:0px; border-width:0px" wire:click.stop>
                <!-- Header -->
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Enter Rejection reason</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showRejectionReasonModal', false)"></button>
                </div>
                <div class="card-body">
                    <textarea name="rejection-reason" id="rejection-reason" class="form-control" wire:model.defer='rejectionReason' placeholder="Enter Reason..."></textarea>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showRejectionReasonModal', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="reject">Reject</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Fund Amount Modal -->
    @if($showFundAmountEntryModal)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showFundAmountEntryModal', false)">

            <div class="card shadow-lg w-100" style="max-width: 600px; height: auto; padding:0px; border-width:0px" wire:click.stop>
                <!-- Header -->
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Enter Fund Amount</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showFundAmountEntryModal', false)"></button>
                </div>
                <div class="card-body">
                    <input type="number" name="fundamount" id="fund-amount" wire:model.defer='fund_amount' class="form-control">
                    <div class="text-danger text-small">{{ $fundErrorMessage }}</div>
                    <div class="mb-3">
                        <label for="user-id" class="form-label">Funded To:</label>
                        <select name="funded-to" id="user-id" wire:model.live ='funded_to_id' class="form-select">
                            @foreach($users as $user)
                                <option value="{{ $user['id'] }}">
                                    {{ $user['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showFundAmountEntryModal', false)">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="fund">Fund</button>
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


