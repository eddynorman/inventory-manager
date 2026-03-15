<div>
    @include('layouts.flash')
    @if($showIndexPage)
        <div class="d-flex justify-content-end align-items-center mb-3">

        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                @if($showPurchases)
                    <h4>Purchases</h4>
                    <div class="gap-2">
                        @if(auth()->user()->hasAnyRole(['super','admin','manager']))
                            <button wire:click="createPurchase" class="btn btn-primary">New Purchase</button>
                            <button wire:click="createOrder" class="btn btn-primary">New Order</button>
                        @endif
                        <button type="button" class="btn btn-dark" wire:click='togglePurchaseOrderView'>Show Orders</button>
                    </div>

                @endif
                @if ($showOrders)
                    <h4>Orders</h4>
                    <div>
                        @if(auth()->user()->hasAnyRole(['super','admin','manager']))
                        <button wire:click="createPurchase" class="btn btn-primary">New Purchase</button>
                        <button wire:click="createOrder" class="btn btn-primary">New Order</button>
                    @endif
                    <button type="button" class="btn btn-dark" wire:click='togglePurchaseOrderView'>Show Purchases</button>
                    </div>
                @endif
            </div>
            <div class="card-body">
                @if($showPurchases)
                    <div id="purchases">
                        <livewire:tables.purchases-table/>
                    </div>
                @endif
                @if ($showOrders)
                    <div id="orders">
                        <livewire:tables.orders-table/>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if ($showPurchaseCreationPage)
        <div id="create-purchase" class="card shadow-lg border-0">

            {{-- HEADER --}}
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">@if (isset($purchase['id']))
                    Edit
                @else
                    Create
                @endif Purchase</h4>
                <button type="button" class="btn-close btn-close-white"
                    wire:click="$set('showPurchaseCreationPage', false)">
                </button>
            </div>


            <div class="card-body">

                @if (!isset($purchase['id']))
                    {{-- REQUISITION SELECT --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Requisition</label>

                        <select wire:model.live="selected_requisition_id"
                                id="requisition-select"
                                class="form-select">

                            <option value="">--- Please Select A Requisition ---</option>

                            @foreach ($requisitions as $req)
                                <option value="{{ $req['id'] }}">
                                    {{ str_pad($req['id'],5,0,STR_PAD_LEFT) }}
                                    | {{ $req['department_name'] }}
                                    | {{ $req['requested_by_name'] }}
                                    | {{ number_format($req['cost'],2) }}
                                </option>
                            @endforeach

                        </select>
                        @error('requisition.requisition_id')
                            <small class="text-danger text-small">You must select a requisition first</small>
                        @enderror
                    </div>
                @endif

                {{-- AVAILABLE ITEMS --}}
                @isset($requisition['available'])

                    <div class="card mb-4 border">

                        <div class="card-header bg-light fw-bold">
                            Available Items
                        </div>

                        <div class="card-body p-2">

                            @if(count($requisition['available']) > 0)

                                <div class="row g-2" style="max-height: 280px; overflow-y:auto;">

                                    @foreach ($requisition['available'] as $index => $available_item)

                                        <div class="col-lg-3 col-md-4 col-sm-6">

                                            <div class="card border h-100 shadow-sm purchase-select-item"
                                                style="cursor:pointer"
                                                wire:click='addPurchaseItem({{ $index }})'>

                                                <div class="card-body p-2 text-center">

                                                    <div class="fw-semibold">
                                                        {{ $available_item['name'] }}
                                                    </div>

                                                    <small class="text-muted">
                                                        Qty: {{ $available_item['requested_quantity'] }}
                                                    </small>

                                                    <br>

                                                    <small class="text-success">
                                                        @ {{ number_format($available_item['requested_unit_price'],2) }}
                                                    </small>

                                                </div>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            @else

                                <div class="text-center text-muted py-4">
                                    No items from the requisition are available.
                                </div>

                            @endif

                        </div>

                    </div>

                @endisset



                {{-- PURCHASE ITEMS TABLE --}}
                <div id="purchase-items">

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped align-middle">

                            <thead class="table-dark">
                                <tr>
                                    <th>Item</th>
                                    <th>Unit</th>
                                    <th>Req Qty</th>
                                    <th>Req Price</th>
                                    <th>Actual Qty</th>
                                    <th>Actual Price</th>
                                    <th>Total</th>
                                    <th width="80">Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @if(!empty($purchase['items']) && count($purchase['items']) > 0)

                                    @foreach ($purchase['items'] as $index => $item)

                                        <tr wire:key='item-{{ $index }}'>

                                            <td>
                                                {{ $item['name'] }}
                                                @error('purchase.items.{{ $index }}.item_id')
                                                    <small class="text-danger">Item does not exist</small>
                                                @enderror
                                            </td>

                                            <td>
                                                {{ $item['unit_name'] }}
                                                @error('purchase.items.{{ $index }}.unit_id')
                                                    <small class="text-danger">Unit does not exist</small>
                                                @enderror
                                            </td>

                                            <td>
                                                {{ $item['requested_quantity'] }}
                                                @error('purchase.items.{{ $index }}.requested_quantity')
                                                    <small class="text-danger">{{ $errors->first("purchase.items.$index.requested_quantity")}}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                {{ number_format($item['requested_unit_price'],2) }}
                                                @error('purchase.items.{{ $index }}.requested_unit_price')
                                                    <small class="text-danger">{{ $errors->first("purchase.items.$index.requested_unit_price")}}</small>
                                                @enderror
                                            </td>

                                            <td width="120">
                                                <input type="number"
                                                    wire:model.live.debounce.500ms='purchase.items.{{ $index }}.quantity'
                                                    class="form-control form-control-sm">
                                                @error('purchase.items.{{ $index }}.quantity')
                                                    <small class="text-danger">{{ $errors->first("purchase.items.$index.quantity")}}</small>
                                                @enderror
                                            </td>

                                            <td width="120">
                                                <input type="number"
                                                    wire:model.live.debounce.500ms='purchase.items.{{ $index }}.unit_price'
                                                    class="form-control form-control-sm">
                                                @error('purchase.items.{{ $index }}.unit_price')
                                                    <small class="text-danger">{{ $errors->first("purchase.items.$index.unit_price")}}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                {{ number_format($item['total'],2) }}
                                            </td>

                                            <td class="text-center">

                                                <button type="button"
                                                    class="btn btn-sm btn-danger"
                                                    wire:click="removeItem({{ $index }}, 'purchase')">

                                                    <i class="fa fa-trash"></i>

                                                </button>

                                            </td>

                                        </tr>

                                    @endforeach
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Grand Total</td>
                                        <td colspan="2" class="text-start fw-bold">{{ number_format($purchase['grand_total'],2) }}</td>
                                    </tr>
                                @else

                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-3">
                                            No items added
                                        </td>
                                    </tr>

                                    @if ( $errors->has('purchase.items'))
                                        <tr>
                                            <td colspan="7" class="text-danger small">
                                                {{ $errors->first('purchase.items') }}
                                            </td>
                                        </tr>
                                    @endif

                                @endif

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            {{-- FOOTER --}}
            <div class="card-footer d-flex justify-content-end gap-2">

                <button type="button"
                        class="btn btn-secondary"
                        wire:click="$set('showPurchaseCreationPage',false)">
                    Close
                </button>

                <button type="button"
                        class="btn btn-success"
                        wire:click="savePurchase">
                    Save Purchase
                </button>

            </div>

        </div>
    @endif

    @if ($showOrderCreationPage)
        <div id="create-order" class="card shadow-lg border-0">

            {{-- HEADER --}}
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">@if (isset($purchase['id']))
                    Edit
                @else
                    Create
                @endif Order</h4>
                <button type="button" class="btn-close btn-close-white"
                    wire:click="$set('showOrderCreationPage', false)">
                </button>
            </div>


            <div class="card-body">

                {{-- REQUISITION AND SUPPLIER SELECT --}}
                @if (isset($purchase['id']))
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Requisition</label>

                            <select wire:model.live="selected_requisition_id"
                                    id="requisition-select"
                                    class="form-select">

                                <option value="">--- Please Select A Requisition ---</option>

                                @foreach ($requisitions as $req)
                                    <option value="{{ $req['id'] }}">
                                        {{ str_pad($req['id'],5,0,STR_PAD_LEFT) }}
                                        | {{ $req['department_name'] }}
                                        | {{ $req['requested_by_name'] }}
                                        | {{ number_format($req['cost'],2) }}
                                    </option>
                                @endforeach

                            </select>
                            @error('requisition.requisition_id')
                                <small class="text-danger">You must select a requisition!</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="supplier-list" class="form-label fw-bold">Select Supplier</label>
                            <select wire:model.live="selected_supplier_id"
                                    id="supplier-select"
                                    class="form-select">

                                <option value="">--- Please Select A Supplier ---</option>

                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier['id'] }}">{{ $supplier['name'] }}</option>
                                @endforeach
                            </select>
                            @error('selected_supplier_id')
                                <small class="text-danger">Supplier must be selected</small>
                            @enderror
                        </div>
                    </div>
                </div>
                @endif

                {{-- AVAILABLE ITEMS --}}
                @isset($requisition['available'])

                    <div class="card mb-4 border">

                        <div class="card-header bg-light fw-bold">
                            Available Items
                        </div>

                        <div class="card-body p-2">

                            @if(count($requisition['available']) > 0)

                                <div class="row g-2" style="max-height: 280px; overflow-y:auto;">

                                    @foreach ($requisition['available'] as $index => $available_item)

                                        <div class="col-lg-3 col-md-4 col-sm-6">

                                            <div class="card border h-100 shadow-sm order-select-item"
                                                style="cursor:pointer"
                                                wire:click='addOrderItem({{ $index }})'>

                                                <div class="card-body p-2 text-center">

                                                    <div class="fw-semibold">
                                                        {{ $available_item['name'] }}
                                                    </div>

                                                    <small class="text-muted">
                                                        Qty: {{ $available_item['requested_quantity'] }}
                                                    </small>

                                                    <br>

                                                    <small class="text-success">
                                                        @ {{ number_format($available_item['requested_unit_price'],2) }}
                                                    </small>

                                                </div>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            @else

                                <div class="text-center text-muted py-4">
                                    No items from the requisition are available.
                                </div>

                            @endif

                        </div>

                    </div>

                @endisset



                {{-- ORDER ITEMS TABLE --}}
                <div id="order-items">

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped align-middle">

                            <thead class="table-dark">
                                <tr>
                                    <th>Item</th>
                                    <th>Unit</th>
                                    <th>Req Qty</th>
                                    <th>Req Price</th>
                                    <th>Actual Qty</th>
                                    <th>Actual Price</th>
                                    <th>Total</th>
                                    <th width="80">Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @if(!empty($order['items']) && count($order['items']) > 0)

                                    @foreach ($order['items'] as $index => $item)

                                        <tr wire:key='order-item-{{ $index }}'>

                                            <td>
                                                {{ $item['name'] }}
                                                @error('order.items.{{ $index }}.item_id')
                                                    <small class="text-danger">Item does not exist!</small>
                                                @enderror
                                            </td>

                                            <td>
                                                {{ $item['unit_name'] }}
                                                @error('order.items.{{ $index }}.unit_id')
                                                    <small class="text-danger">Unit does not exist!</small>
                                                @enderror
                                            </td>

                                            <td>
                                                {{ $item['requested_quantity'] }}
                                                @error('order.items.{{ $index }}.requested_quantity')
                                                    <small class="text-danger">{{ $errors->first("order.items.$index.requested_quantity") }}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                {{ number_format($item['requested_unit_price'],2) }}
                                                @error('order.items.{{ $index }}.requested_unit_price')
                                                    <small class="text-danger">{{ $errors->first("order.items.$index.requested_unit_price") }}</small>
                                                @enderror
                                            </td>

                                            <td width="120">
                                                <input type="number"
                                                    wire:model.live.debounce.500ms='order.items.{{ $index }}.quantity'
                                                    class="form-control form-control-sm">
                                                @error('order.items.{{ $index }}.quantity')
                                                    <small class="text-danger">{{ $errors->first("order.items.$index.quantity") }}</small>
                                                @enderror
                                            </td>

                                            <td width="120">
                                                <input type="number"
                                                    wire:model.live.debounce.500ms='order.items.{{ $index }}.unit_price'
                                                    class="form-control form-control-sm">

                                                @error('order.items.{{ $index }}.unit_price')
                                                    <small class="text-danger">{{ $errors->first("order.items.$index.unit_price") }}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                {{ number_format($item['total'],2) }}
                                            </td>

                                            <td class="text-center">

                                                <button type="button"
                                                    class="btn btn-sm btn-danger"
                                                    wire:click="removeItem({{ $index }}, 'order')">

                                                    <i class="fa fa-trash"></i>

                                                </button>

                                            </td>

                                        </tr>

                                    @endforeach
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Grand Total</td>
                                        <td colspan="2" class="text-start fw-bold">{{ number_format($order['grand_total'],2) }}</td>
                                    </tr>
                                @else

                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-3">
                                            No items added
                                        </td>
                                    </tr>

                                @endif

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            {{-- FOOTER --}}
            <div class="card-footer d-flex justify-content-end gap-2">

                <button type="button"
                        class="btn btn-secondary"
                        wire:click="$set('showOrderCreationPage',false)">
                    Close
                </button>

                <button type="button"
                        class="btn btn-success"
                        wire:click="saveOrder">
                    Save Purchase
                </button>

            </div>

        </div>
    @endif

    {{-- View Order Modal --}}
    @if ($showViewOrder)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showViewOrder', false)">

            <div class="card shadow-lg w-100" style="max-width: 900px; height: auto; padding:0px; border-width:0px" wire:click.stop>
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="mb-0">Order Details - {{ isset($view_order['id']) ? str_pad($view_order['id'],5,'0',STR_PAD_LEFT) : '' }}</h5>
                        <small class="text-light">Requisition: {{ isset($view_order['requisition_id']) ? str_pad($view_order['requisition_id'],5,'0',STR_PAD_LEFT) : '-' }} &middot; Supplier: {{ $view_order['supplier_name'] ?? ($view_order['supplier'] ?? '-') }}</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showViewOrder', false)"></button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div><strong>Total:</strong> {{ number_format($view_order['grand_total'] ?? 0,2) }}</div>
                            <div><strong>Paid:</strong> {{ number_format($view_order['amount_paid'] ?? 0,2) }}</div>
                            <div><strong>Pending:</strong> {{ number_format($view_order['amount_pending'] ?? 0,2) }}</div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div><strong>Ordered By:</strong> {{ $view_order['created_by_name'] ?? ($view_order['created_by'] ?? '-') }}</div>
                            <div><strong>Date:</strong> {{ isset($view_order['created_at']) ? Illuminate\Support\Carbon::parse($view_order['created_at'])->format('d/m/Y H:i') : '-' }}</div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Item</th>
                                    <th>Unit</th>
                                    <th>Requested</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($view_order['items']) && count($view_order['items']) > 0)
                                    @foreach($view_order['items'] as $item)
                                        <tr>
                                            <td>{{ $item['name'] ?? '-' }}</td>
                                            <td>{{ $item['unit_name'] ?? '-' }}</td>
                                            <td>{{ $item['requested_quantity'] ?? ($item['requested_qty'] ?? '-') }}</td>
                                            <td>{{ $item['quantity'] ?? ($item['qty'] ?? '-') }}</td>
                                            <td>{{ number_format($item['unit_price'] ?? 0,2) }}</td>
                                            <td>{{ number_format($item['total'] ?? 0,2) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="6" class="text-center">No items</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showViewOrder', false)">Close</button>
                </div>
            </div>
        </div>
    @endif

    {{-- View Purchase Modal --}}
    @if ($showViewPurchase)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showViewPurchase', false)">

            <div class="card shadow-lg w-100" style="max-width: 900px; height: auto; padding:0px; border-width:0px" wire:click.stop>
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="mb-0">Purchase Details - {{ isset($view_purchase['id']) ? str_pad($view_purchase['id'],5,'0',STR_PAD_LEFT) : '' }}</h5>
                        <small class="text-light">Requisition: {{ isset($view_purchase['requisition_id']) ? str_pad($view_purchase['requisition_id'],5,'0',STR_PAD_LEFT) : '-' }}</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showViewPurchase', false)"></button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div><strong>Grand Total:</strong> {{ number_format($view_purchase['grand_total'] ?? 0,2) }}</div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div><strong>Purchased By:</strong> {{ $view_purchase['purchased_by_name'] ?? ($view_purchase['purchased_by_id'] ?? '-') }}</div>
                            <div><strong>Date:</strong> {{ isset($view_purchase['created_at']) ? Illuminate\Support\Carbon::parse($view_purchase['created_at'])->format('d/m/Y H:i') : '-' }}</div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Item</th>
                                    <th>Unit</th>
                                    <th>Requested</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($view_purchase['items']) && count($view_purchase['items']) > 0)
                                    @foreach($view_purchase['items'] as $item)
                                        <tr>
                                            <td>{{ $item['name'] ?? '-' }}</td>
                                            <td>{{ $item['unit_name'] ?? '-' }}</td>
                                            <td>{{ $item['requested_quantity'] ?? ($item['requested_qty'] ?? '-') }}</td>
                                            <td>{{ $item['quantity'] ?? ($item['qty'] ?? '-') }}</td>
                                            <td>{{ number_format($item['unit_price'] ?? 0,2) }}</td>
                                            <td>{{ number_format($item['total'] ?? 0,2) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="6" class="text-center">No items</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showViewPurchase', false)">Close</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showConfirmDeleteOrder)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showConfirmDeleteOrder', false)">

            <div class="card shadow-lg w-100" style="max-width: 500px; height: auto; padding:0px; border-width:0px" wire:click.stop>
                <!-- Header -->
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showConfirmDeleteOrder', false)"></button>
                </div>
                <div class="card-body">Are you sure you want to delete order {{ str_pad($idToDelete, 5, "0", STR_PAD_LEFT) }}?</div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showConfirmDeleteOrder', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteOrder">Delete</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showConfirmDeletePurchase)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showConfirmDeletePurchase', false)">

            <div class="card shadow-lg w-100" style="max-width: 500px; height: auto; padding:0px; border-width:0px" wire:click.stop>
                <!-- Header -->
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showConfirmDeletePurchase', false)"></button>
                </div>
                <div class="card-body">Are you sure you want to delete purchase {{ str_pad($idToDelete, 5, "0", STR_PAD_LEFT) }}?</div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showConfirmDeletePurchase', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deletePurchase">Delete</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showOrderPaymentForm)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showOrderPaymentForm', false)">

            <div class="card shadow-lg w-100" style="max-width: 600px; height: auto; padding:0px; border-width:0px" wire:click.stop>
                <!-- Header -->
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Enter Fund Amount</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showOrderPaymentForm', false)"></button>
                </div>
                <div class="card-body">
                    <input type="number" name="amount" id="amount" wire:model.defer='amount' class="form-control">
                    <div class="text-danger text-small"></div>
                    <div class="mb-3">
                        <label for="reference" class="form-label">Reference</label>
                        <textarea name="" id="reference" class="form-control" placeholder="Enter payment reference..." wire:model.defer></textarea>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showOrderPaymentForm', false)">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="addPayment">Add Payment</button>
                </div>
            </div>
        </div>
    @endif


</div>


