<div>
    @include('layouts.flash')
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Transfers</h5>
                <small class="text-muted">Manage item transfers</small>
            </div>

            <div class="d-flex gap-2">
                @if (auth()->user()->canAccess('transfers.create'))
                    <button class="btn btn-outline-primary btn-sm"
                        wire:click="newTransfer">
                        New Transfer
                    </button>
                @endif

            </div>
        </div>
        <div class="card-body">
            <livewire:tables.transfer-table/>
        </div>
    </div>

    <!-- Create/Edit page -->
   @if ($showCreatePage)

        <div
            class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:2100;"
            wire:click="$set('showCreatePage', false)">

            <div
                class="card shadow-lg w-100 p-0"
                style="max-width: 900px;"
                wire:click.stop

                x-data="transferManager()"
            >

                {{-- HEADER --}}
                <div class="card-header d-flex bg-primary align-items-center justify-content-between">

                    <h2 class="text-white">
                        Create Transfer
                    </h2>

                    <button
                        type="button"
                        class="btn-close btn-close-white btn-xxl"
                        wire:click="$set('showCreatePage', false)">
                    </button>

                </div>

                {{-- BODY --}}
                <div
                    class="card-body"
                    style="max-height:70vh; overflow-y:auto;"
                >

                    {{-- TOP --}}
                    <div class="row mb-4">

                        {{-- SOURCE --}}
                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Source
                            </label>

                            <select
                                wire:model.live="locationId"
                                class="form-select"
                            >

                                <option value="">
                                    --Select Location--
                                </option>

                                @foreach ($locations as $location)

                                    <option value="{{ $location['id'] }}">

                                        {{ $location['name'] }}

                                    </option>

                                @endforeach

                            </select>

                            @error('locationId')

                                <small class="text-danger">

                                    {{ $message }}

                                </small>

                            @enderror

                        </div>

                        {{-- ISSUE --}}
                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Select Issue
                            </label>

                            <select
                                wire:model.live="selectedIssue"
                                class="form-select"
                            >

                                <option value="">
                                    --Select Issue--
                                </option>

                                @foreach ($issues as $issue)

                                    <option value="{{ $issue['id'] }}">

                                        #{{ str_pad($issue['id'],5,'0',STR_PAD_LEFT) }}
                                        From:
                                        {{ $issue['from_location']['name'] }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                    </div>

                    {{-- TABLE --}}
                    <template x-if="items.length > 0">

                        <div class="table-responsive">

                            <table class="table table-bordered align-middle">

                                <thead class="table-dark">

                                    <tr>

                                        <th>Item</th>

                                        <th>Requested Qty</th>

                                        <th>Requested Unit</th>

                                        <th width="180">
                                            Quantity
                                        </th>

                                        <th width="220">
                                            Unit
                                        </th>

                                        <th width="80"></th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <template
                                        x-for="(item, index) in items"
                                        :key="item.item_id"
                                    >

                                        <tr>

                                            {{-- ITEM --}}
                                            <td>

                                                <div
                                                    class="fw-semibold"
                                                    x-text="item.name">
                                                </div>

                                            </td>

                                            {{-- REQUESTED --}}
                                            <td>

                                                <span
                                                    x-text="item.requested_quantity">
                                                </span>

                                            </td>

                                            {{-- REQUESTED UNIT --}}
                                            <td>

                                                <span
                                                    x-text="item.requested_unit">
                                                </span>

                                            </td>

                                            {{-- QUANTITY --}}
                                            <td>

                                                <div class="d-flex gap-1">

                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-secondary btn-sm"

                                                        @click="
                                                            if(item.quantity > 1){
                                                                item.quantity--;
                                                                updateItem();
                                                            }
                                                        "
                                                    >

                                                        -

                                                    </button>

                                                    <input
                                                        type="number"

                                                        min="1"

                                                        class="form-control text-center"

                                                        x-model.number="item.quantity"

                                                        @input="updateItem()"

                                                        :class="
                                                            errors[
                                                                'transferItems.' +
                                                                index +
                                                                '.quantity'
                                                            ]
                                                            ? 'is-invalid'
                                                            : ''
                                                        "
                                                    >

                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-secondary btn-sm"

                                                        @click="
                                                            item.quantity++;
                                                            updateItem();
                                                        "
                                                    >

                                                        +

                                                    </button>

                                                </div>

                                                {{-- ERROR --}}
                                                <template
                                                    x-if="
                                                        errors[
                                                            'transferItems.' +
                                                            index +
                                                            '.quantity'
                                                        ]
                                                    "
                                                >

                                                    <small
                                                        class="text-danger d-block mt-1 fw-bold"

                                                        x-text="
                                                            errors[
                                                                'transferItems.' +
                                                                index +
                                                                '.quantity'
                                                            ][0]
                                                        "
                                                    >
                                                    </small>

                                                </template>

                                            </td>

                                            {{-- UNIT --}}
                                            <td>

                                                <select
                                                    class="form-select"

                                                    x-model="item.selected_unit_id"

                                                    @change="updateItem()"
                                                >

                                                    <template
                                                        x-for="unit in item.units"
                                                        :key="unit.id"
                                                    >

                                                        <option
                                                            :value="unit.id"

                                                            x-text="unit.name">
                                                        </option>

                                                    </template>

                                                </select>

                                            </td>

                                            {{-- REMOVE --}}
                                            <td>

                                                <button
                                                    type="button"

                                                    class="btn btn-danger btn-sm"

                                                    @click="removeItem(index)"
                                                >

                                                    <i class="fa fa-trash"></i>

                                                </button>

                                            </td>

                                        </tr>

                                    </template>

                                </tbody>

                            </table>

                        </div>

                    </template>

                    {{-- EMPTY --}}
                    <template x-if="items.length === 0">

                        <div class="alert alert-warning mb-0">

                            No transfer items loaded

                        </div>

                    </template>

                    {{-- DESCRIPTION --}}
                    <div class="mt-4">

                        <label class="form-label fw-bold">

                            Description

                        </label>

                        <textarea
                            rows="3"

                            class="form-control"

                            wire:model.defer="description"
                        ></textarea>

                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">

                    <button
                        type="button"

                        class="btn btn-secondary"

                        wire:click="$set('showCreatePage', false)"
                    >

                        Close

                    </button>

                    @if ($user_id != $current_user_id && count($transferItems) > 0)

                        @if (auth()->user()->canAccess('issues.reject'))

                            <button
                                type="button"

                                class="btn btn-danger"

                                wire:click="$set('showRejectIssue', true)"
                            >

                                Reject

                            </button>

                        @endif

                        <button
                            type="button"

                            class="btn btn-primary"

                            wire:click="save"
                        >

                            Save

                        </button>

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

    <script>

        document.addEventListener('alpine:init', () => {

            Alpine.data('transferManager', () => ({

                items: @entangle('transferItems').live,

                errors: {},

                init() {

                    window.addEventListener(
                        'transfer-errors-updated',
                        (e) => {

                            this.errors =
                                e.detail.errors || {};
                        }
                    );
                },

                updateItem() {

                    this.errors = {};

                    this.syncItems();
                },

                removeItem(index) {

                    const item = this.items[index];

                    if (!item) {
                        return;
                    }

                    this.items.splice(index, 1);

                    this.syncItems();

                    this.$wire.removeItem(item.item_id);
                },

                syncItems() {

                    this.$wire.set(
                        'transferItems',
                        this.items
                    );
                }
            }));
        });

    </script>
</div>
