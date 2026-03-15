<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Receivings</h5>
        @if(auth()->user()->hasAnyRole(['super','admin','manager']))
            <button wire:click="create" class="btn btn-primary">New Receiving</button>
        @endif
    </div>

    {{-- View Receiving Modal --}}
    <div id="view-receiving" style="display:none;">
        <div class="card shadow-lg border-0" style="max-width:900px;margin:20px auto;padding:0;">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Receiving Details - #{{ $receivingId }}</h5>
                <button type="button" class="btn-close btn-close-white" wire:click="$set('receivingId', null)"></button>
            </div>
            <div class="card-body">
                @if($receivingId)
                    @php
                        $r = \App\Models\Receiving::with(['purchase','supplierOrder','receiver','location','items.item','items.unit'])->find($receivingId);
                    @endphp
                    @if($r)
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
                @endif
            </div>
            <div class="card-footer d-flex justify-content-end bg-light">
                <button type="button" class="btn btn-secondary" wire:click="$set('receivingId', null)">Close</button>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('show-view-receiving', () => {
            document.getElementById('view-receiving').style.display = 'block';
        });
        window.addEventListener('hide-view-receiving', () => {
            document.getElementById('view-receiving').style.display = 'none';
        });
    </script>

    <div class="card">
        <div class="card-body">
            <livewire:tables.receivings-table />
        </div>
    </div>

    {{-- Create Receiving Modal --}}
    <div id="create-receiving" class="card shadow-lg border-0" style="display:none;">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Create Receiving</h4>
            <button type="button" class="btn-close btn-close-white" wire:click="$set('showReceivingModal', false)"></button>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Type</label>
                    <select class="form-select" wire:model="type">
                        <option value="purchase">Purchase</option>
                        <option value="order">Order</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Source</label>
                    <select class="form-select" wire:model="purchase_id">
                        <option value="">-- Select --</option>
                        @if($type == 'purchase')
                            @foreach($purchases as $p)
                                <option value="{{ $p->id }}">Purchase #{{ $p->id }} — {{ number_format($p->total_amount,2) }}</option>
                            @endforeach
                        @else
                            @foreach($orders as $o)
                                <option value="{{ $o->id }}">Order #{{ $o->id }} — {{ number_format($o->total_amount,2) }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Location</label>
                    <select class="form-select" wire:model="location_id">
                        <option value="">Select...</option>
                        @foreach($locations as $l)
                            <option value="{{ $l->id }}">{{ $l->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Items (will be recorded as received)</label>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Item</th>
                                    <th>Unit</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($items) > 0)
                                    @foreach($items as $it)
                                        <tr>
                                            <td>{{ $it['item_id'] }}</td>
                                            <td>{{ $it['unit_id'] }}</td>
                                            <td class="text-end">{{ $it['quantity'] }}</td>
                                            <td class="text-end">{{ number_format($it['unit_price'],2) }}</td>
                                            <td class="text-end">{{ number_format($it['total'],2) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="5" class="text-center">Select a source to load items</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2 bg-light">
            <button type="button" class="btn btn-secondary" wire:click="$set('showReceivingModal', false)">Cancel</button>
            <button type="button" class="btn btn-success" wire:click="save">Record Receiving</button>
        </div>
    </div>

    <script>
        window.addEventListener('show-receiving-modal', () => {
            document.getElementById('create-receiving').style.display = 'block';
        });
        window.addEventListener('hide-receiving-modal', () => {
            document.getElementById('create-receiving').style.display = 'none';
        });
    </script>
</div>


