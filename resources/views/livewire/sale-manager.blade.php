<div>
<div x-data="{
    sale: @entangle('sale').live,
    payments: @entangle('payments').live,
    locationIds: @entangle('locationIds').live,
    selectedMethodId: @entangle('selectedMethodId').live,
    paymentAmount: @entangle('paymentAmount').live,
    selected_user_id: '',
    selected_location_id: '',
    highlightedIndex: @entangle('highlightedIndex'),

    // 🧮 Basic Math handled by Alpine for instant feedback
    get totals() {
        let items = this.sale.items || [];
        let total = items.reduce((acc, item) => acc + (parseFloat(item.total) || 0), 0);
        let paid = (this.payments || []).reduce((acc, p) => acc + (parseFloat(p.amount) || 0), 0);

        // We also sync these back to the entangled 'sale' object
        // so the backend SaleManager.php is aware of the current state
        this.sale.total = total;
        this.sale.paid = paid;
        this.sale.balance = total - paid;

        return { total, paid, balance: total - paid };
    },

    // Logic to update row totals locally
    updateItem(item) {
        let unit = (item.units || []).find(u => u.id == item.selected_unit_id);
        if (unit) {
            item.selling_price = unit.selling_price;
            item.total = (item.quantity || 0) * unit.selling_price;
        }

        // Update payment amount to match current balance
        this.paymentAmount = this.totals.balance;
    }
}" x-cloak>
    @include('layouts.flash')

    @if($showIndex)
        <div class="container">
            <div class="row g-3">

                <!-- ================= LEFT SIDE ================= -->
                <div class="col-lg-9">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Create Sale</h5>
                                <small class="text-muted">Search items and build cart</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary btn-sm"
                                    wire:click="showPendingFn">
                                    ⏳ Pending
                                </button>

                                <button class="btn btn-outline-success btn-sm"
                                    wire:click="showTodaysFn">
                                    📊 Today
                                </button>
                            </div>
                        </div>

                        @if (auth()->user()->canAccess('sales.create') || auth()->user()->canAccess('sales.edit'))
                            <div class="card-body">
                                <!-- 🔹 LOCATION + SEARCH -->
                                <div class="card shadow-sm mb-3">
                                    <div class="card-body">
                                        <!-- Locations -->
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <select class="form-select form-select-lg"
                                                    x-model="selected_location_id"
                                                    @change="if($el.value) { $wire.selectLocation($el.value); selected_location_id = ''; }">
                                                    <option value="">Select Location...</option>
                                                    @foreach ($locations as $location)
                                                        <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-control d-flex flex-wrap gap-2">
                                                    <template x-for="loc in $wire.selectedLocations" :key="loc.id">
                                                        <span class="badge bg-primary d-inline-flex align-items-center px-3 py-2">
                                                            <span x-text="loc.name"></span>
                                                            <button type="button" class="btn-close btn-close-white ms-2" style="font-size:10px;" @click="$wire.removeLocation(loc.id)"></button>
                                                        </span>
                                                    </template>
                                                    <template x-if="locationIds.length === 0">
                                                        <span class="text-secondary">Selected locations appear here!</span>
                                                    </template>
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
                                        <!-- Search And Table Views -->
                                        <div class="position-relative mb-3 w-50">
                                            <input type="text"
                                                id="search-input"
                                                class="form-control form-control-lg"
                                                wire:model.live.debounce.300ms="search"
                                                @keydown.arrow-down.prevent="highlightedIndex = Math.min(highlightedIndex + 1, $wire.searchItems.length - 1)"
                                                @keydown.arrow-up.prevent="highlightedIndex = Math.max(highlightedIndex - 1, 0)"
                                                @keydown.enter.prevent="if($wire.searchItems[highlightedIndex]) { $wire.selectItem(highlightedIndex); highlightedIndex = 0; }"
                                                placeholder="🔍 Search Products by name..."
                                            >

                                            <div class="dropdown-menu show w-100 mt-1 shadow border-0" x-show="$wire.searchItems.length > 0" style="z-index: 1000;">
                                                <template x-for="(item, index) in $wire.searchItems" :key="index">
                                                    <button type="button"
                                                        class="dropdown-item d-flex justify-content-between align-items-center"
                                                        :class="highlightedIndex === index ? 'active bg-primary text-white' : ''"
                                                        @click="$wire.selectItem(index)">
                                                        <span x-text="item.name"></span>
                                                        <small :class="highlightedIndex === index ? 'text-white' : 'text-muted'" x-text="'Stock: ' + item.stock"></small>
                                                    </button>
                                                </template>
                                            </div>
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
                                                <template x-for="(item, index) in sale.items" :key="index">
                                                    <tr>
                                                        <td x-text="item.name"></td>
                                                        <td>
                                                            <span x-text="item.type === 'service' ? 'Non Stock' : item.stock"></span>
                                                        </td>

                                                        <td>
                                                            <select class="form-select form-select-md"
                                                                x-model="item.selected_unit_id"
                                                                @change="updateItem(item)">
                                                                <template x-for="unit in item.units">
                                                                    <option :value="unit.id" x-text="unit.name"></option>
                                                                </template>
                                                            </select>
                                                        </td>

                                                        <td>
                                                            <div class="d-flex align-items-center gap-1">
                                                                <!-- MINUS -->
                                                                <button type="button"
                                                                    class="btn btn-outline-secondary btn-sm px-2"
                                                                    @click="if(item.quantity > 1) { item.quantity--; updateItem(item); }">
                                                                    −
                                                                </button>
                                                                <!-- INPUT -->
                                                                <input type="number"
                                                                    class="form-control text-center fw-bold qty-input"
                                                                    style="width:70px;"
                                                                    x-model.number="item.quantity"
                                                                    @input="updateItem(item)">

                                                                <!-- PLUS -->
                                                                <button type="button"
                                                                    class="btn btn-outline-secondary btn-sm px-2"
                                                                    @click="item.quantity++; updateItem(item);">
                                                                    +
                                                                </button>

                                                            </div>
                                                        </td>

                                                        <td x-text="new Intl.NumberFormat().format(item.selling_price)"></td>
                                                        <td class="fw-bold" x-text="new Intl.NumberFormat().format(item.total)"></td>
                                                        <td><button type="button" class="btn btn-outline-danger btn-sm" @click="$wire.removeItem(index)">✕</button></td>
                                                    </tr>
                                                </template>

                                                <tr x-show="sale.items.length === 0">
                                                    <td colspan="7" class="text-center text-muted py-4">
                                                        No items added
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- ================= RIGHT SIDE ================= -->
                @if (auth()->user()->canAccess('sales.create') || auth()->user()->canAccess('sales.edit'))
                    <div class="col-lg-3">

                        <!-- 🔹 SUMMARY -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-body">

                                <h6 class="fw-bold mb-3 text-uppercase text-muted">Summary</h6>

                                <div class="d-flex justify-content-between">
                                    <span>Total</span>
                                    <strong x-text="new Intl.NumberFormat().format(totals.total)"></strong>
                                </div>

                                <div class="d-flex justify-content-between text-success">
                                    <span>Paid</span>
                                    <strong x-text="new Intl.NumberFormat().format(totals.paid)"></strong>
                                </div>

                                <div class="d-flex justify-content-between text-danger">
                                    <span>Balance</span>
                                    <strong x-text="new Intl.NumberFormat().format(totals.balance)"></strong>
                                </div>

                            </div>
                        </div>

                        <!-- 🔹 SERVED BY -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-body">

                                <h6 class="fw-bold mb-2">Served By</h6>

                                <div class="mb-2">
                                    <template x-for="server in $wire.servers" :key="server.id">
                                        <span class="badge bg-dark d-inline-flex align-items-center px-3 py-2 mb-2 me-1">
                                            <span x-text="server.name"></span>
                                            <button type="button" class="btn-close btn-close-white ms-2" style="font-size:10px;" @click="$wire.removeUser(server.id)"></button>
                                        </span>
                                    </template>
                                    <template x-if="sale.served_by.length === 0">
                                        <small class="text-muted">No user selected</small>
                                    </template>
                                </div>

                                <select class="form-select" x-model="selected_user_id"
                                    @change="if($el.value) { $wire.selectUser($el.value); selected_user_id = ''; }">
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
                                            <template x-for="p in payments">
                                                <tr>
                                                    <td x-text="new Intl.NumberFormat().format(p.amount)"></td>
                                                    <td x-text="p.method.name"></td>
                                                </tr>
                                            </template>
                                            <tr x-show="payments.length === 0">
                                                <td colspan="2"><small class="text-danger">No Payment Added!</small></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <h6 class="fw-bold mb-2">Add Payment</h6>

                                <input type="number"
                                    class="form-control mb-2"
                                    placeholder="Amount"
                                    x-model.number="paymentAmount"
                                    :disabled="sale.balance <= 0"
                                >
                                <div class="w-100 row">
                                    <div class="col-md-10">
                                        <select class="form-select"
                                            x-model="selectedMethodId">
                                            <option value="">Method...</option>
                                            @foreach ($paymentMethods as $index => $method)
                                                <option value="{{ $method['id'] }}" @if ($index == 0) selected @endif>{{ $method['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 px-1">
                                        <button class="btn text-center btn-primary mb-2"
                                            wire:click="$set('showAddPaymentMethod', true)" title="Add New Payment Method"><i class="fa fa-plus"></i></button>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-dark w-100" @click="$wire.addPayment()">
                                    Add Payment
                                </button>
                            </div>
                        </div>

                        <!-- 🔹 ACTIONS -->
                        <div class="d-grid gap-2">
                            <button class="btn btn-success btn-lg"
                                x-show="sale.items && sale.items.length > 0"
                                @click="$wire.checkValidity()">
                                Save Sale
                            </button>

                            <button class="btn btn-outline-secondary" @click="location.reload()">Cancel</button>
                        </div>

                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($showSalesTables)
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="fw-bold">
                    {{ $showPending ? 'Pending Sales' : 'Today\'s Sales' }}
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary"
                        wire:click="$set('showPending', true)">
                        Pending
                    </button>

                    <button class="btn btn-sm btn-outline-success"
                        wire:click="$set('showTodays', true)">
                        Today
                    </button>

                    <button class="btn-close"
                        wire:click="$set('showSalesTables', false)">
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($showPending)
                    <livewire:tables.pending-sales-table/>
                @endif
                @if($showTodays)
                    <livewire:tables.todays-sales-table/>
                @endif
            </div>
        </div>
    @endif

    @if ($showViewSale)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:1050;"
            wire:click="$set('showViewSale', false)">

            <div class="card shadow-lg w-100"
                style="max-width: 750px;"
                wire:click.stop>

                <!-- HEADER -->
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">

                    <div>
                        <div class="fw-bold">Sale Details</div>
                        <small class="text-light">
                            {{ \Carbon\Carbon::parse($saleView['created_at'])->format('d M Y, H:i') }}
                        </small>
                    </div>

                    <div class="d-flex align-items-center gap-2">

                        <!-- PRINT -->
                        <button class="btn btn-sm btn-light"
                            wire:click="$dispatch('print-sale', { saleId: {{ $saleView['id'] }} })">
                            <i class="fa fa-print"></i> Print
                        </button>

                        <!-- EDIT (ONLY IF PENDING) -->
                        @if($saleView['status'] === 'pending' && \Carbon\Carbon::parse($saleView['created_at'])->isToday())
                            <button class="btn btn-sm btn-warning"
                                wire:click="editSale({{ $saleView['id'] }})">
                                <i class="fa fa-pen-to-square"></i> Edit
                            </button>
                        @endif
                        @if($saleView['status'] === 'pending')
                            <button class="btn btn-success btn-sm" wire:click="showPaymentForm({{ $saleView['id'] }})">
                                <i class="fa fa-money-bill me-1"></i> Add Payment
                            </button>
                        @endif
                        <button class="btn-close btn-close-white"
                            wire:click="$set('showViewSale', false)">
                        </button>
                    </div>
                </div>

                <!-- BODY -->
                <div class="card-body overflow-auto">

                    <!-- 🔹 META INFO -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted">Recorded By</small><br>
                            <strong>{{ $saleView['recorded_by'] }}</strong>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted">Served By</small><br>
                            @forelse($saleView['served_by'] as $user)
                                <span class="badge bg-dark">{{ $user['name'] }}</span>
                            @empty
                                <span class="text-muted">N/A</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- 🔹 SUMMARY -->
                    <div class="border rounded p-2 mb-3 bg-light">
                        <div class="d-flex justify-content-between">
                            <span>Total</span>
                            <strong>{{ number_format($saleView['total']) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between text-success">
                            <span>Paid</span>
                            <strong>{{ number_format($saleView['paid']) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between text-danger">
                            <span>Balance</span>
                            <strong>{{ number_format($saleView['balance']) }}</strong>
                        </div>
                    </div>

                    <!-- 🔹 ITEMS -->
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th width="100">Unit</th>
                                <th width="80">Qty</th>
                                <th width="120">Price</th>
                                <th width="120">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($saleView['items'] as $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['unit_name'] }}</td>
                                    <td>{{ $item['quantity'] }}</td>
                                    <td>{{ number_format($item['selling_price']) }}</td>
                                    <td class="fw-bold">{{ number_format($item['total']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- 🔹 PAYMENTS -->
                    <div class="mt-3 mb-3">
                        <h6 class="fw-bold border-bottom pb-1">Payments</h6>

                        @forelse ($saleView['payments'] as $p)
                            <div class="d-flex justify-content-between">
                                <span>{{ $p['method']['name'] }}</span>
                                <strong>{{ number_format($p['amount']) }}</strong>
                            </div>
                        @empty
                            <small class="text-danger">No payments recorded</small>
                        @endforelse
                    </div>

                </div>

            </div>
        </div>
    @endif

    <!-- 🔹 CONFIRM MODAL -->
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

                    <button class="btn btn-success"
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

    @if($showAddPaymentForm)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:2100;"
            wire:click="$set('showAddPaymentForm', false)">

            <div class="card shadow-lg w-100"
                style="max-width: 500px;"
                wire:click.stop>

                <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between">
                    Add Payment Form
                    <button class="btn-close"
                        wire:click="$set('showAddPaymentForm', false)">
                    </button>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <label for="amount-input" class="form-label fw-bold">Payment Amount</label>
                            <input type="number"
                                class="form-control"
                                id="amount-input"
                                placeholder="Amount"
                                wire:model.live.debounce.300ms="paymentAmount"
                            >
                    </div>
                    <div class="mb-3">
                        <label for="method-select" class="form-label fw-bold">Payment Method</label>
                            <select class="form-select mb-3"
                                id="method-select"
                                wire:model="selectedMethodId">
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method['id'] }}">{{ $method['name'] }}</option>
                                @endforeach
                            </select>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary"
                        wire:click="$set('showAddPaymentForm', false)">
                        Cancel
                    </button>

                    <button class="btn btn-success"
                        wire:click="savePayment">
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        window.addEventListener('print-sale', event => {
            let url = `/print/sale/${event.detail.saleId}`;

            let iframe = document.getElementById('print-frame');

            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'print-frame';
                iframe.style.position = 'absolute';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                document.body.appendChild(iframe);
            }

            iframe.src = url;

            iframe.onload = function () {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            };
        });
    </script>
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
