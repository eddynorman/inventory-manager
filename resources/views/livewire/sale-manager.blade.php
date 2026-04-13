<div>
    @include('layouts.flash')

    @if($showIndex)
    <div class="container">
        <div class="row g-3">

            <!-- ================= LEFT SIDE ================= -->
            <div class="col-lg-9">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Create Sale</h5>
                    </div>
                    <div class="card-body">
                        <!-- 🔹 LOCATION + SEARCH -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-body">
                                <!-- Locations -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <select name="location-select" id="location-select" class="form-select" wire:change="selectLocation($event.target.value)">
                                            <option value="">Select Location...</option>
                                            @foreach ($locations as $location)
                                                <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-control">
                                            @forelse ($selectedLocations as $location)
                                                <span class="badge bg-primary d-inline-block align-items-center px-3 py-2">
                                                    {{ $location['name'] }}
                                                    <button type="button"
                                                        class="btn-close btn-close-white ms-1"
                                                        style="font-size:12px;"
                                                        wire:click.stop="removeLocation({{ $location['id'] }})">
                                                    </button>
                                                </span>
                                            @empty
                                                <span class="text-secondary">Selected locations appear here!</span>
                                            @endforelse
                                        </div>
                                    </div>
                                    @error('locationIds')
                                        <small class="text-danger">Please Select a Location</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 🔹 CART -->
                        <div class="card shadow-sm">
                            <div class="card-body p-2">
                                <!-- Search -->
                                <div class="position-relative mb-3 w-50">
                                    <input type="text"
                                        id="search-input"
                                        class="form-control"
                                        wire:model.live.debounce.300ms="search"
                                        wire:keydown.arrow-down.prevent="moveDown"
                                        wire:keydown.arrow-up.prevent="moveUp"
                                        wire:keydown.enter.prevent="selectHighlighted"
                                        wire:keydown.escape="$set('searchItems', [])"
                                        placeholder="🔍 Search items...">

                                    @if(!empty($searchItems))
                                        <div class="dropdown-menu show w-100 mt-1 shadow">
                                            @foreach ($searchItems as $index => $item)
                                                <button
                                                    class="dropdown-item d-flex justify-content-between
                                                        {{ $highlightedIndex === $index ? 'active bg-primary text-white' : '' }}"
                                                    wire:click="selectItem({{ $index }})">

                                                    <span>{{ $item['name'] }}</span>
                                                    <small>Stock: {{ $item['stock'] }}</small>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Item</th>
                                            <th width="80">Stock</th>
                                            <th width="120">Unit</th>
                                            <th width="100">Qty</th>
                                            <th width="120">Price</th>
                                            <th width="120" class="fw-bold text-dark">Total</th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($sale['items'] ?? [] as $index => $item)
                                            <tr>
                                                <td>{{ $item['name'] }}</td>
                                                <td>{{ $item['stock'] }}</td>

                                                <td>
                                                    <select class="form-select form-select-md"
                                                        wire:model.live="sale.items.{{$index}}.selected_unit_id">
                                                        @foreach ($item['units'] as $unit)
                                                            <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>

                                                <td>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <!-- MINUS -->
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm px-2"
                                                            wire:click="decreaseQty({{ $index }})">
                                                            −
                                                        </button>
                                                        <!-- INPUT -->
                                                        <input type="number"
                                                            class="form-control text-center fw-bold qty-input"
                                                            style="width:70px;"
                                                            data-index="{{ $index }}"
                                                            max="{{ $item['stock'] }}"
                                                            min="1"
                                                            wire:model.live.debounce.500ms="sale.items.{{$index}}.quantity">

                                                        <!-- PLUS -->
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm px-2"
                                                            wire:click="increaseQty({{ $index }})">
                                                            +
                                                        </button>

                                                    </div>
                                                </td>

                                                <td>{{ number_format($item['selling_price']) }}</td>
                                                <td class="fw-bold">{{ number_format($item['total']) }}</td>

                                                <td>
                                                    <button class="btn btn-outline-danger btn-sm"
                                                        wire:click="removeItem({{ $index }})">
                                                        ✕
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    No items added <br>
                                                    @error('sale.items')
                                                        <small class="text-danger">Add Items First</small>
                                                    @enderror
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= RIGHT SIDE ================= -->
            <div class="col-lg-3">

                <!-- 🔹 SUMMARY -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body">

                        <h6 class="fw-bold mb-3 text-uppercase text-muted">Summary</h6>

                        <div class="d-flex justify-content-between">
                            <span>Total</span>
                            <strong>{{ number_format($sale['total'] ?? 0) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between text-success">
                            <span>Paid</span>
                            <strong>{{ number_format($sale['paid'] ?? 0) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between text-danger">
                            <span>Balance</span>
                            <strong>{{ number_format($sale['balance'] ?? 0) }}</strong>
                        </div>

                    </div>
                </div>

                <!-- 🔹 SERVED BY -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body">

                        <h6 class="fw-bold mb-2">Served By</h6>

                        <div class="mb-2">
                            @forelse ($servers as $server)
                                <span class="badge bg-dark align-items-center px-3 py-2 mb-2">
                                    {{ $server['name'] }}
                                    <button type="button"
                                        class="btn-close btn-close-white ms-1"
                                        style="font-size:10px;"
                                        wire:click="removeUser({{ $server['id'] }})">
                                    </button>
                                </span>
                            @empty
                                <small class="text-muted">No user selected</small>
                            @endforelse
                        </div>

                        <select class="form-select"
                            wire:change="selectUser($event.target.value)">
                            <option value="">Select user</option>
                            @foreach ($users as $user)
                                <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                            @endforeach
                        </select>
                        @error('sale.served_by')
                            <small class="text-danger">Please Select a User</small>
                        @enderror

                    </div>
                </div>

                <!-- 🔹 PAYMENT -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div id="payments">
                            <table class="table table-hover table-bordered table-striped">
                                <thead class='table-dark'>
                                    <tr>
                                        <th>Amount</th>
                                        <th>Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payments as $payment)
                                        <tr>
                                            <td>{{ number_format($payment['amount'],2) }}</td>
                                            <td>{{ $payment['method']['name']}}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2"><small class="text-danger">No Payment Added!</small></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <h6 class="fw-bold mb-2">Add Payment</h6>

                        <input type="number"
                            class="form-control mb-2"
                            placeholder="Amount"
                            wire:model.live.debounce.300ms="paymentAmount">
                        <div class="w-100 row">
                            <div class="col-md-10">
                                 <select class="form-select"
                                    wire:model="selectedMethodId">
                                    @foreach ($paymentMethods as $method)
                                        <option value="{{ $method['id'] }}">{{ $method['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 px-1">
                                <button class="btn text-center btn-primary mb-2"
                                    wire:click="$set('showAddPaymentMethod', true)" title="Add New Payment Method"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>

                        <button class="btn btn-dark w-100" wire:click='addPayment'>
                            Add Payment
                        </button>

                    </div>
                </div>

                <!-- 🔹 ACTIONS -->
                <div class="d-grid gap-2">
                    @if (count($sale['items']) > 0)
                        <button class="btn btn-success btn-lg"
                            wire:click="$set('showConfirmSale', true)">
                            Save Sale
                        </button>
                    @endif

                    <button class="btn btn-outline-secondary">
                        Cancel
                    </button>
                </div>

            </div>

        </div>
    </div>
    @endif

    <!-- 🔹 CONFIRM MODAL (UNCHANGED BUT CLEANED) -->
    @if ($showConfirmSale)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:1050;"
            wire:click="$set('showConfirmSale', false)">

            <div class="card shadow-lg w-100"
                style="max-width: 500px;"
                wire:click.stop>

                <div class="card-header bg-info fw-bold d-flex justify-content-between">
                    Confirm Sale
                    <button class="btn-close"
                        wire:click="$set('showConfirmSale', false)">
                    </button>
                </div>

                <div class="card-body">
                    Are you sure you want to save this sale?
                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary"
                        wire:click="$set('showConfirmSale', false)">
                        Cancel
                    </button>

                    <button class="btn btn-danger"
                        wire:click="saveSale">
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showAddPaymentMethod)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:1050;"
            wire:click="$set('showAddPaymentMethod', false)">

            <div class="card shadow-lg w-100"
                style="max-width: 500px;border-width:0px;"
                wire:click.stop>

                <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between">
                    Add Payment Method
                    <button class="btn-close"
                        wire:click="$set('showAddPaymentMethod', false)">
                    </button>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <label for="method-name" class="form-labl">Name</label>
                        <input type="text" name="method-name" id="method-name" class="form-control" wire:model.debounce='methodName'>
                    </div>
                    <div class="mb-3">
                        <label for="method-number" class="form-labl">Reference number</label>
                        <input type="text" name="method-number" id="method-number" class="form-control" wire:model.debounce='referenceNumber'>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary"
                        wire:click="$set('showAddPaymentMethod', false)">
                        Cancel
                    </button>

                    <button class="btn btn-danger"
                        wire:click="savePaymentMethod">
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif


    <!-- 🔹 KEYBOARD + AUTO FOCUS -->
    <script>
    document.addEventListener('livewire:initialized', () => {

        Livewire.on('focus-qty', (data) => {
            setTimeout(() => {
                let input = document.querySelector(`.qty-input[data-index='${data.index}']`);
                if (input) {
                    input.focus();
                    input.select();
                }
            }, 50);
        });

        Livewire.on('focus-search', () => {
            setTimeout(() => {
                let search = document.querySelector('#search-input');
                if (search) search.focus();
            }, 50);
        });

    });
    </script>

</div>
