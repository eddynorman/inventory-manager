<div>
    @include('layouts.flash')
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Issues</h5>
                <small class="text-muted">Manage transfer requests(Issues)</small>
            </div>

            <div class="d-flex gap-2">
                @if(auth()->user()->canAccess('issues.create'))
                    <button class="btn btn-outline-primary btn-sm"
                        wire:click="newIssue">
                        New Issue
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <livewire:tables.issue-table/>
        </div>
    </div>

    {{-- CREATE ISSUE --}}
    @if ($showCreatePage)

        <div
            class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:2100;"
            wire:click="$set('showCreatePage', false)">

            <div
                class="card shadow-lg w-100 p-0"
                style="max-width: 900px;"
                wire:click.stop

                x-data="issueManager()">

                {{-- HEADER --}}
                <div class="card-header d-flex bg-primary align-items-center justify-content-between">

                    <h2 class="text-white mb-0">
                        Create Issue
                    </h2>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        wire:click="$set('showCreatePage', false)"
                    ></button>

                </div>

                {{-- BODY --}}
                <div
                    class="card-body"
                    style="max-height:70vh; overflow-y:auto;">

                    <div class="row">

                        {{-- SEARCH --}}
                        <div class="mb-3 col-md-6 position-relative">

                            <label class="form-label">
                                Items
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                placeholder="Search items to add..."
                                autocomplete="off"

                                x-model="search"

                                wire:model.live.debounce.300ms="search"
                            >

                            {{-- SEARCH RESULTS --}}
                            <div
                                class="dropdown-menu show w-100 mt-1 shadow border-0"
                                x-show="searchItems.length > 0"
                                x-transition
                                style="
                                    z-index: 3000;
                                    max-height: 250px;
                                    overflow-y: auto;
                                "
                            >

                                <template
                                    x-for="(item, index) in searchItems"
                                    :key="item.item_id"
                                >

                                    <button
                                        type="button"
                                        class="dropdown-item py-2"

                                        @click="addItem(index)"
                                    >

                                        <div class="fw-semibold">
                                            <span x-text="item.name"></span>
                                        </div>

                                    </button>

                                </template>

                            </div>

                        </div>

                        {{-- SOURCE --}}
                        <div class="col-md-3">

                            <label class="form-label">
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

                        {{-- DESTINATION --}}
                        <div class="col-md-3">

                            <label class="form-label">
                                Destination
                            </label>

                            <select
                                wire:model.live="destinationId"
                                class="form-select"
                            >

                                <option value="">
                                    --Select Destination--
                                </option>

                                @foreach ($locations as $location)

                                    <option value="{{ $location['id'] }}">
                                        {{ $location['name'] }}
                                    </option>

                                @endforeach

                            </select>

                            @error('destinationId')
                                <small class="text-danger">
                                    {{ $message }}
                                </small>
                            @enderror

                        </div>

                    </div>

                    {{-- TABLE --}}
                    <div class="table-responsive">

                        <table class="table table-bordered align-middle">

                            <thead class="table-dark">

                                <tr>

                                    <th>ITEM</th>

                                    <th width="120">
                                        STOCK
                                    </th>

                                    <th width="140">
                                        QUANTITY
                                    </th>

                                    <th width="180">
                                        UNIT
                                    </th>

                                    <th width="70">
                                        ACTION
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <template
                                    x-for="(item, index) in items"
                                    :key="item.item_id"
                                >

                                    <tr>

                                        {{-- NAME --}}
                                        <td>

                                            <span
                                                class="fw-semibold"
                                                x-text="item.name"
                                            ></span>

                                        </td>

                                        {{-- STOCK --}}
                                        <td>

                                            <span
                                                x-text="Number(item.stock).toFixed(2)"
                                            ></span>

                                        </td>

                                        {{-- QUANTITY --}}
                                        <td style="width:140px;">

                                            <input
                                                type="number"

                                                min="1"

                                                class="form-control"

                                                x-model.number="item.quantity"

                                                @input="updateItem()"

                                                :class="
                                                    errors['issueItems.' + index + '.quantity']
                                                        ? 'is-invalid'
                                                        : ''
                                                "
                                            >

                                            {{-- ERROR --}}
                                            <template
                                                x-if="errors['issueItems.' + index + '.quantity']"
                                            >

                                                <small
                                                    class="text-danger d-block mt-1 fw-bold"

                                                    x-text="
                                                        errors['issueItems.' + index + '.quantity'][0]
                                                    "
                                                ></small>

                                            </template>

                                        </td>

                                        {{-- UNIT --}}
                                        <td>

                                            <select
                                                class="form-select"

                                                x-model="item.selected_unit_id"

                                                @change="updateItem()"

                                                :class="
                                                    errors['issueItems.' + index + '.quantity']
                                                        ? 'is-invalid'
                                                        : ''
                                                ">

                                                <template
                                                    x-for="unit in item.units"
                                                    :key="unit.id">

                                                    <option
                                                        :value="unit.id"

                                                        x-text="unit.name"
                                                    ></option>

                                                </template>

                                            </select>

                                        </td>

                                        {{-- REMOVE --}}
                                        <td>

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger"

                                                @click="removeItem(index)"
                                            >

                                                <i class="fa fa-trash"></i>

                                            </button>

                                        </td>

                                    </tr>

                                </template>

                                {{-- EMPTY --}}
                                <tr x-show="items.length === 0">

                                    <td
                                        colspan="5"
                                        class="text-danger"
                                    >

                                        No Items Added

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="mb-3">

                        <label class="form-label">
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

                    <button
                        type="button"
                        class="btn btn-primary"

                        wire:click="save"
                    >

                        Save

                    </button>

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
                    @if (auth()->user()->canAccess('issues.reject') && !(isset($viewIssue['processed_by']) || isset($viewIssue['rejected_by'])) && $user_id != $current_user_id )
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

    <script>

        document.addEventListener('alpine:init', () => {

            Alpine.data('issueManager', () => ({

                /*
                |--------------------------------------------------------------------------
                | DATA
                |--------------------------------------------------------------------------
                */

                items: @entangle('issueItems').live,

                search: '',

                errors: {},

                /*
                |--------------------------------------------------------------------------
                | INIT
                |--------------------------------------------------------------------------
                */

                init() {

                    window.addEventListener('issue-errors-updated', (e) => {

                        this.errors = e.detail.errors || {};
                    });
                },

                /*
                |--------------------------------------------------------------------------
                | SEARCH ITEMS
                |--------------------------------------------------------------------------
                */

                get searchItems() {

                    return this.$wire.searchItems || [];
                },

                /*
                |--------------------------------------------------------------------------
                | ADD ITEM
                |--------------------------------------------------------------------------
                */

                addItem(index) {

                    const item = this.searchItems[index];

                    if (!item) {

                        this.clearSearch();

                        return;
                    }

                    const existingIndex = this.items.findIndex(
                        existing => existing.item_id === item.item_id
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | EXISTS
                    |--------------------------------------------------------------------------
                    */

                    if (existingIndex !== -1) {

                        this.items[existingIndex].quantity =
                            Number(this.items[existingIndex].quantity || 0) + 1;

                        this.syncItems();

                        this.clearSearch();

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ADD TO TOP
                    |--------------------------------------------------------------------------
                    */

                    this.items.unshift({

                        item_id: item.item_id,

                        name: item.name,

                        stock: item.stock,

                        quantity: 1,

                        selected_unit_id: item.selected_unit_id,

                        units: item.units,
                    });

                    this.syncItems();

                    this.clearSearch();
                },

                /*
                |--------------------------------------------------------------------------
                | REMOVE
                |--------------------------------------------------------------------------
                */

                removeItem(index) {

                    this.items.splice(index, 1);

                    this.syncItems();
                },

                /*
                |--------------------------------------------------------------------------
                | UPDATE ITEM
                |--------------------------------------------------------------------------
                */

                updateItem() {

                    /*
                    |--------------------------------------------------------------------------
                    | CLEAR OLD ERRORS
                    |--------------------------------------------------------------------------
                    */

                    this.errors = {};

                    this.syncItems();
                },

                /*
                |--------------------------------------------------------------------------
                | CLEAR SEARCH
                |--------------------------------------------------------------------------
                */

                clearSearch() {

                    this.search = '';

                    this.$wire.set('search', '');

                    this.$wire.set('searchItems', []);
                },

                /*
                |--------------------------------------------------------------------------
                | SYNC
                |--------------------------------------------------------------------------
                */

                syncItems() {

                    this.$wire.set('issueItems', this.items);
                }
            }));
        });

    </script>
</div>
