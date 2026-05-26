<div>

    @include('layouts.flash')

    @if ($showIndexPage)

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <h4 class="mb-0">
                    Stock Adjustments
                </h4>

                @if(auth()->user()->canAccess('items.adjust_stock'))

                    <button
                        wire:click="create"
                        class="btn btn-primary">

                        New Adjustment

                    </button>

                @endif

            </div>

            <div class="card-body">

                <livewire:tables.stock-adjustment-table/>

            </div>

        </div>

    @endif

    @if ($showAdjustmentCreationPage)

        <div
            class="card"

            x-data="stockAdjustmentManager()"
        >

            {{-- HEADER --}}
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">

                <h4 class="mb-0">
                    Create Stock Adjustment
                </h4>

                <button
                    class="btn-close btn-close-white"
                    wire:click="
                        $set('showAdjustmentCreationPage', false);
                        $set('showIndexPage', true)
                    ">
                </button>

            </div>

            {{-- BODY --}}
            <div class="card-body">

                {{-- LOCATION --}}
                <div class="mb-3">

                    <label class="form-label fw-bold">
                        Location
                    </label>

                    <select
                        wire:model.live="location_id"
                        class="form-control"
                    >

                        <option value="">
                            Select Location
                        </option>

                        @foreach($locations as $location)

                            <option value="{{ $location['id'] }}">

                                {{ $location['name'] }}

                            </option>

                        @endforeach

                    </select>

                    @error('location_id')

                        <small class="text-danger">
                            You must select a location
                        </small>

                    @enderror

                </div>

                {{-- SEARCH --}}
                @if($location_id)

                    <div class="position-relative mb-4 w-50">

                        <label class="form-label fw-bold">
                            Search Item
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            placeholder="Search item..."
                            autocomplete="off"
                            x-model="search"
                            wire:model.live.debounce.300ms="search"
                        >

                        {{-- FLOATING SEARCH RESULTS --}}
                        <div
                            class="dropdown-menu show w-100 mt-1 shadow border-0"
                            x-show="searchItems.length > 0"
                            x-transition
                            style="z-index: 3000; max-height: 300px; overflow-y: auto;"
                        >
                            <template x-for="(item, index) in searchItems" :key="item.id">
                                <button
                                    type="button"
                                    class="dropdown-item py-2"
                                    @click="addItem(index)"
                                >
                                    <div class="fw-semibold">
                                        <span x-text="item.name"></span>
                                    </div>
                                    <small class="text-muted">
                                        Stock: <span x-text="Number(item.quantity).toFixed(2)"></span>
                                    </small>
                                </button>
                            </template>
                        </div>
                    </div>
                @endif
                {{-- ITEMS TABLE --}}
                <template x-if="items.length > 0">

                    <div class="table-responsive">

                        <table class="table table-bordered align-middle">

                            <thead>

                                <tr>

                                    <th>Item</th>

                                    <th width="120">
                                        Current
                                    </th>

                                    <th width="180">
                                        Adjustment
                                    </th>

                                    <th width="140">
                                        New Stock
                                    </th>

                                    <th>
                                        Reason
                                    </th>

                                    <th width="70"></th>

                                </tr>

                            </thead>

                            <tbody>

                                <template
                                    x-for="(item, index) in items"
                                    :key="item.id"
                                >

                                    <tr>

                                        {{-- ITEM --}}
                                        <td>

                                            <div
                                                class="fw-semibold"
                                                x-text="item.name">
                                            </div>

                                        </td>

                                        {{-- CURRENT STOCK --}}
                                        <td>

                                            <span
                                                x-text="Number(item.quantity).toFixed(2)">
                                            </span>

                                        </td>

                                        {{-- ADJUSTMENT --}}
                                        <td>

                                            <input
                                                type="number"

                                                step="any"

                                                class="form-control"

                                                x-model.number="item.adjustment_qty"
                                            >

                                            <template x-if="newStock(item) < 0">

                                                <small class="text-danger">

                                                    Cannot go below zero

                                                </small>

                                            </template>

                                        </td>

                                        {{-- NEW STOCK --}}
                                        <td>

                                            <span
                                                class="fw-bold"

                                                :class="
                                                    newStock(item) < 0
                                                        ? 'text-danger'
                                                        : 'text-success'
                                                "

                                                x-text="
                                                    Number(
                                                        newStock(item)
                                                    ).toFixed(2)
                                                "
                                            >
                                            </span>

                                        </td>

                                        {{-- REASON --}}
                                        <td>

                                            <input
                                                type="text"

                                                class="form-control"

                                                x-model="item.reason"
                                            >

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

                        {{-- DESCRIPTION --}}
                        <div class="mb-3">

                            <label class="form-label fw-bold">

                                General Description

                            </label>

                            <textarea
                                rows="2"

                                class="form-control"

                                placeholder="Optional general description"

                                wire:model.defer="description"
                            ></textarea>

                        </div>

                    </div>

                </template>

            </div>

            {{-- FOOTER --}}
            <div class="card-footer">

                <div class="d-flex justify-content-end gap-2">

                    <button
                        class="btn btn-secondary"

                        wire:click="
                            $set('showAdjustmentCreationPage', false);
                            $set('showIndexPage', true)
                        "
                    >

                        Close

                    </button>

                    <template x-if="items.length > 0">

                        <button
                            class="btn btn-success"

                            wire:click="confirmSave"
                        >

                            Save Adjustment

                        </button>

                    </template>

                </div>

            </div>

        </div>

    @endif

    {{-- VIEW --}}
    @if ($showViewAdjustment)

        <div class="card">

            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">

                <h4 class="mb-0">
                    View Stock Adjustment
                </h4>

                <button
                    class="btn-close btn-close-white"
                    wire:click="$set('showViewAdjustment', false)">
                </button>

            </div>

            <div class="card-body">

                <p>
                    <strong>Location:</strong>
                    {{ $viewAdjustmentData->location->name }}
                </p>

                <p>
                    <strong>Created By:</strong>
                    {{ $viewAdjustmentData->createdBy->name }}
                </p>

                <p>
                    <strong>Date:</strong>
                    {{ $viewAdjustmentData->created_at }}
                </p>

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

                                <td>
                                    {{ $item->item->name }}
                                </td>

                                <td>
                                    {{ $item->current_stock }}
                                </td>

                                <td class="{{ $item->adjustment_qty < 0 ? 'text-danger' : 'text-success' }}">

                                    {{ $item->quantity }}

                                </td>

                                <td>
                                    {{ $item->new_stock }}
                                </td>

                                <td>
                                    {{ $item->reason }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    @endif

    {{-- CONFIRM SAVE --}}
    @if ($showConfirmSave)

        <div
            class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:1050;"
            wire:click="$set('showConfirmSave', false)"
        >

            <div
                class="card shadow-lg w-100"
                style="max-width: 500px; border-width:0px;"
                wire:click.stop
            >

                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-3">

                    <h5 class="mb-0">
                        Confirm Adjustment
                    </h5>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        wire:click="$set('showConfirmSave', false)">
                    </button>

                </div>

                <div class="card-body">

                    Are you sure you want to save this adjustment?

                </div>

                <div class="card-footer d-flex justify-content-end gap-2 bg-light">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        wire:click="$set('showConfirmSave', false)"
                    >

                        Cancel

                    </button>

                    <button
                        type="button"
                        class="btn btn-success"
                        wire:click="save"
                    >

                        Save

                    </button>

                </div>

            </div>

        </div>

    @endif

    {{-- ALPINE --}}
    <script>

        document.addEventListener('alpine:init', () => {

            Alpine.data('stockAdjustmentManager', () => ({

                items: @entangle('adjustment_items').live,

                search: '',

                // Getter to safely read search items directly from Livewire dynamically
                get searchItems() {
                    return this.$wire.item_list || [];
                },

                addItem(index) {
                    // Grab item from our getter
                    const item = this.searchItems[index];

                    if (!item) {
                        this.search = '';
                        this.$wire.set('item_list', []); // Clear on backend
                        return;
                    }

                    const exists = this.items.find(
                        existing => existing.id === item.id
                    );

                    if (exists) {
                        this.search = '';
                        this.$wire.set('item_list', []);
                        return;
                    }

                    this.items.unshift({
                        item_id: item.item_id,
                        id: item.id,
                        name: item.name,
                        quantity: Number(item.quantity),
                        adjustment_qty: 0,
                        reason: '',
                    });

                    // Clear search inputs and notify Livewire to clear results
                    this.search = '';
                    this.$wire.set('search', '');
                    this.$wire.set('item_list', []);
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                newStock(item) {
                    return (
                        Number(item.quantity) +
                        Number(item.adjustment_qty || 0)
                    );
                }
            }));
        });

    </script>

</div>
