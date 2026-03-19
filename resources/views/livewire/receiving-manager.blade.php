<div>
    @include('layouts.flash')

    @if($showIndexPage)
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Receivings</h4>
                <div class="gap-2">
                    @if(auth()->user()->hasAnyRole(['super','admin','manager']))
                        <button wire:click="create" class="btn btn-primary">New Receiving</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <livewire:tables.receivings-table />
            </div>
        </div>
    @endif

    {{-- View Receiving Modal --}}
    @if($showViewReceivingPage)
        <div id="view-receiving">
            <div class="card shadow-lg border-0" style="margin:20px auto;padding:0;">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Receiving Details - #{{ str_pad($receivingId,5,'0',STR_PAD_LEFT) }}</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showViewReceivingPage', false)"></button>
                </div>
                <div class="card-body">
                    @if($receivingId && $r)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div><strong>Source:</strong> {{ $r->purchase_id ? 'Purchase #' . str_pad($r->purchase_id,5,'0',STR_PAD_LEFT) : 'Order #' . str_pad($r->supplier_order_id,5,'0',STR_PAD_LEFT) }}</div>
                                <div><strong>Location:</strong> {{ optional($r->location)->name }}</div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div><strong>Receiver:</strong> {{ optional($r->receiver)->name }}</div>
                                <div><strong>Date:</strong> {{ optional($r->created_at)->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark"><tr><th>Item</th><th>Unit</th><th class="text-end">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Total</th></tr></thead>
                                <tbody>
                                    @foreach($r->items as $it)
                                        <tr>
                                            <td>{{ optional($it->item)->name }}</td>
                                            <td>{{ optional($it->unit)->name }}</td>
                                            <td class="text-end">{{ $it->quantity }}</td>
                                            <td class="text-end">{{ number_format($it->unit_price,2) }}</td>
                                            <td class="text-end">{{ number_format($it->total,2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="card-footer d-flex justify-content-end bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showViewReceivingPage', false)">Close</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Create Receiving Modal --}}
    @if($showCreateReceivingPage)
        <div id="create-receiving" class="card shadow-lg border-0">

            {{-- HEADER --}}
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Create Receiving</h4>
                <button type="button" class="btn-close btn-close-white"
                    wire:click="$set('showCreateReceivingPage', false)">
                </button>
            </div>


            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="type-select" class="form-label fw-bold">Source type:</label>
                            <select name="" id="type-select" class="form-select" wire:model.live.debounce.100ms='type'>
                                <option value="purchase">Purchases</option>
                                <option value="order">Orders</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="location-select" class="form-label fw-bold">Choose Location:</label>
                            <select name="" id="location-select" class="form-select" wire:model.live.debounce.100ms='location_id'>
                                <option value={{ null }}>---Choose Location---</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
                                @endforeach
                            </select>
                            @error('location_id')
                                <small class="text-danger">Valid Location Required!</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        {{-- PURCHASE/ORDER SELECT --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select @if ($type=="purchase")
                                Purchase
                            @else
                                Order
                            @endif</label>

                            <select wire:model.live="selected_source_id"
                                    id="source-select"
                                    class="form-select">

                                <option value="">--- Please Select A @if ($type=="purchase")
                                        Purchase
                                    @else
                                        Order
                                    @endif ---</option>

                                @if ($type=="purchase")
                                    @foreach ($purchases as $purchase)
                                        <option value="{{ $purchase['id'] }}">
                                            {{ str_pad($purchase['id'],5,0,STR_PAD_LEFT) }}
                                            | {{ $purchase['department_name'] }}
                                            | {{ $purchase['purchaser']['name'] }}
                                            | {{ number_format($purchase['total_amount'],2) }}
                                        </option>
                                    @endforeach
                                @endif
                                @if ($type=="order")
                                    @foreach ($orders as $order)
                                        <option value="{{ $order['id'] }}">
                                            {{ str_pad($order['id'],5,0,STR_PAD_LEFT) }}
                                            | {{ $order['department_name'] }}
                                            | {{ $order['created_by']['name'] }}
                                            | {{ number_format($order['total_amount'],2) }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('selected_source_id')
                                <small class="text-danger text-small">You must select a @if ($type=="purchase")
                                    Purchase
                                @else
                                    Order
                                @endif first</small>
                            @enderror
                        </div>
                    </div>
                </div>



                {{-- AVAILABLE ITEMS --}}
                @isset($source['available'])

                    <div class="card mb-4 border">

                        <div class="card-header bg-light fw-bold">
                            Available Items
                        </div>

                        <div class="card-body p-2">

                            @if(count($source['available']) > 0)

                                <div class="row g-2" style="max-height: 280px; overflow-y:auto;">

                                    @foreach ($source['available'] as $index => $available_item)

                                        <div class="col-lg-3 col-md-4 col-sm-6">

                                            <div class="card border h-100 shadow-sm purchase-select-item"
                                                style="cursor:pointer"
                                                wire:click='addReceivingItem({{ $index }})'>

                                                <div class="card-body p-2 text-center">

                                                    <div class="fw-semibold">
                                                        {{ $available_item['name'] }}
                                                    </div>

                                                    <small class="text-muted">
                                                        Qty: {{ $available_item['quantity'] - $available_item['received_quantity'] }}
                                                    </small>

                                                    <br>

                                                    <small class="text-success">
                                                        @ {{ number_format($available_item['unit_price'],2) }}
                                                    </small>

                                                </div>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            @else

                                <div class="text-center text-muted py-4">
                                    No items from the @if ($type=="purchase")
                                        purchase
                                    @else
                                        order
                                    @endif are available.
                                </div>

                            @endif

                        </div>

                    </div>

                @endisset

                {{-- PURCHASE ITEMS TABLE --}}
                <div id="source-items">

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped align-middle">

                            <thead class="table-dark">
                                <tr>
                                    <th>Item</th>
                                    <th>Unit</th>
                                    <th>@if ($type=="purchase")
                                        Purchase
                                    @else
                                        Order
                                    @endif Qty</th>
                                    <th>Rcvd Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th width="80">Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @if(!empty($receiving['items']) && count($receiving['items']) > 0)

                                    @foreach ($receiving['items'] as $index => $item)

                                        <tr wire:key='item-{{ $index }}'>

                                            <td>
                                                {{ $item['name'] }}
                                                @error('receiving.items.{{ $index }}.item_id')
                                                    <small class="text-danger">Item does not exist</small>
                                                @enderror
                                            </td>

                                            <td>
                                                {{ $item['unit_name'] }}
                                                @error('receiving.items.{{ $index }}.unit_id')
                                                    <small class="text-danger">Unit does not exist</small>
                                                @enderror
                                            </td>

                                            <td>
                                                {{ $item['quantity'] }}
                                                @error('receiving.items.{{ $index }}.quantity')
                                                    <small class="text-danger">{{ $errors->first("receiving.items.$index.quantity")}}</small>
                                                @enderror
                                            </td>

                                            <td width="120">
                                                <input type="number"
                                                    wire:model.live.debounce.100ms='receiving.items.{{ $index }}.received_quantity'
                                                    class="form-control form-control-sm">
                                                @error('receiving.items.{{ $index }}.received_quantity')
                                                    <small class="text-danger">{{ $errors->first("receiving.items.$index.received_quantity")}}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                {{ number_format($item['unit_price'],2) }}
                                                @error('receiving.items.{{ $index }}.unit_price')
                                                    <small class="text-danger">{{ $errors->first("receiving.items.$index.unit_price")}}</small>
                                                @enderror
                                            </td>

                                            <td>
                                                {{ number_format($item['total'],2) }}
                                            </td>

                                            <td class="text-center">

                                                <button type="button"
                                                    class="btn btn-sm btn-danger"
                                                    wire:click="removeItem({{ $index }})">

                                                    <i class="fa fa-trash"></i>

                                                </button>

                                            </td>

                                        </tr>

                                    @endforeach
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Grand Total</td>
                                        <td colspan="2" class="text-start fw-bold">{{ number_format($receiving['grand_total'],2) }}</td>
                                    </tr>
                                @else

                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">
                                            No items added
                                        </td>
                                    </tr>

                                    @if ( $errors->has('receiving.items'))
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
                        wire:click="$set('showCreateReceivingPage',false)">
                    Close
                </button>

                <button type="button"
                        class="btn btn-success"
                        wire:click="save">
                    Save Receiving
                </button>

            </div>

        </div>
    @endif
</div>


