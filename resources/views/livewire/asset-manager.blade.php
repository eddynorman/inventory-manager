<div>
    @include('layouts.flash')
    <div class="card shadow-sm",>
        <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
            <div class="card-title">
                <h5 class="mb-0 fw-semibold">Asset Management</h5>
                <small class="text-muted">Record and manage Asset details and information.</small>
            </div>
            <div class="card-tools">
                @if(auth()->user()->hasAnyRole(['super','admin','manager']))
                    <button class="btn btn-dark align-items-center gap-2"
                            wire:click="$set('showPurchaseModal',true)">
                        <span>+</span> New Purchase
                    </button>
                    <button wire:click="$set('showCreateModal', true)" class="btn btn-primary">New Item</button>
                    <button wire:click="$set('showImportModal', true)" class="btn btn-primary">
                        Import Assets
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="container-fluid">
                <livewire:tables.asset-table />
            </div>
        </div>
    </div>

    @if($showCreateModal)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(15,23,42,0.65); z-index:1050; backdrop-filter: blur(5px);"
            wire:click="$set('showCreateModal', false)">

            <div class=" card shadow-lg w-100 p-0 " style="max-width: 500px; border: transparent;"
                wire:click.stop>
                <div class="card-header d-flex bg-primary align-items-center justify-content-between">
                    <h4 class=" text-white">
                        Create Asset Item
                    </h4>
                    <button type="button" class="btn-close btn-close-white btn-xxl" wire:click="$set('showCreateModal', false)"></button>
                </div>
                <div class="card-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department</label>
                        <select wire:model="form.department_id" class="form-select @error('form.department_id') is-invalid @enderror">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                            @endforeach
                        </select>
                        @error('form.department_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Item Name</label>
                        <input type="text" id="name" class="form-control @error('form.name') is-invalid @enderror"
                            placeholder="Name"
                            wire:model="form.name">
                        @error('form.name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="initial-date" class="form-label fw-semibold">Initial Purchase Date</label>
                        <input type="date" id="initial-date" class="form-control @error('form.initial_purchase_date') is-invalid @enderror"
                            placeholder="Unit Cost"
                            max="{{ now()->toDateString() }}"
                            wire:model="form.initial_purchase_date">
                        @error('form.initial_purchase_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label fw-semibold">Quantity</label>
                        <input type="number" id="quantity" class="form-control @error('form.initial_quantity') is-invalid @enderror"
                            placeholder="Quantity"
                            wire:model="form.initial_quantity">
                        @error('form.initial_quantity') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="cost" class="form-label fw-semibold">Unit Cost</label>
                        <input type="number" id="cost" class="form-control @error('form.initial_unit_cost') is-invalid @enderror"
                            placeholder="Unit Cost"
                            wire:model="form.initial_unit_cost">
                        @error('form.initial_unit_cost') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary w-50" wire:click="$set('showCreateModal', false)">Close</button>
                    <button class="btn btn-success w-50"
                            wire:click="createItem">
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($showPurchaseModal)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(15,23,42,0.65); z-index:1050; backdrop-filter: blur(5px);"
            wire:click="$set('showPurchaseModal', false)">

            <div class=" card shadow-lg w-100 p-0 " style="max-width: 800px; border: transparent;"
                wire:click.stop>
                <div class="card-header d-flex bg-primary align-items-center justify-content-between">
                    <h4 class=" text-white">
                        Add purchased Assets
                    </h4>
                    <button type="button" class="btn-close btn-close-white btn-xxl" wire:click="$set('showPurchaseModal', false)"></button>
                </div>
                <div class="card-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="bg-white">
                        {{-- SEARCH --}}
                        <input type="text"
                            class="form-control mb-2 w-75"
                            placeholder="Search item..."
                            wire:model.debounce.500ms="searchItem">

                        {{-- RESULTS --}}
                        @if($searchResults)
                            <div class="border rounded mb-3">
                                @foreach($searchResults as $item)
                                    <div class="p-2 hover:bg-gray-100 cursor-pointer"
                                        wire:click="addItem({{ $item->id }})">
                                        {{ $item->name }}
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- TABLE --}}
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th width="120">Qty</th>
                                    <th width="150">Cost</th>
                                    <th width="150">Total</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseItems as $index => $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>

                                    <td>
                                        <input type="number"
                                            class="form-control"
                                            wire:model.lazy="purchaseItems.{{ $index }}.quantity">
                                    </td>

                                    <td>
                                        <input type="number"
                                            class="form-control"
                                            wire:model.lazy="purchaseItems.{{ $index }}.unit_cost">
                                    </td>
                                    <td>
                                        {{ number_format($item['row_total'] ?? 0, 2) }}
                                    </td>

                                    <td>
                                        <button class="btn btn-sm btn-danger"
                                                wire:click="removeItem({{ $index }})">
                                            ✕
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end mt-3">
                            <div class="fw-bold fs-5">
                                Total: {{ number_format($grandTotal, 2) }}
                            </div>
                        </div>

                        @error('purchaseItems')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary w-50" wire:click="$set('showPurchaseModal', false)">Close</button>
                    <button class="btn btn-dark w-50"
                        wire:click="recordPurchase">
                        Save Purchase
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($showDamageModal)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(15,23,42,0.65); z-index:1050; backdrop-filter: blur(5px);"
            wire:click="$set('showPurchaseModal', false)">

            <div class=" card shadow-lg w-100 p-0 " style="max-width: 500px; border: transparent;"
                wire:click.stop>
                <div class="card-header d-flex bg-primary align-items-center justify-content-between">
                    <h4 class=" text-white">
                        Damage details for {{ $damage['item_name'] }}
                    </h4>
                    <button type="button" class="btn-close btn-close-white btn-xxl" wire:click="$set('showdamageModal', false)"></button>
                </div>
                <div class="card-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="mb-3">
                        <label for="damage-qty" class="form-label fw-semibold">Quantity</label>
                        <input type="number"
                            id="damage-qty"
                            class="form-control mb-2 @error('damage.quantity') is-invalid @enderror"
                            placeholder="Quantity"
                            wire:model="damage.quantity">
                            @error('damage.quantity')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control mb-3 @error('damage.notes') is-invalid @enderror"
                            id="notes"
                            placeholder="Notes"
                            wire:model="damage.notes">
                        </textarea>
                        @error('damage.notes')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary w-50" wire:click="$set('showDamageModal', false)">Close</button>
                    <button class="btn btn-danger w-100"
                            wire:click="recordDamage">
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($showImportModal)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(15,23,42,0.65); z-index:1050; backdrop-filter: blur(5px);"
            wire:click="$set('showImportModal', false)">

            <div class="card shadow-lg w-100 p-0"
                style="max-width: 700px; border: transparent;"
                wire:click.stop>

                {{-- HEADER --}}
                <div class="card-header py-3 d-flex bg-primary align-items-center justify-content-between">
                    <h4 class="text-white mb-0">
                        Bulk Import Assets
                    </h4>
                    <button type="button"
                            class="btn-close btn-close-white"
                            wire:click="$set('showImportModal', false)">
                    </button>
                </div>

                {{-- BODY --}}
                <div class="card-body" style="max-height:70vh; overflow-y:auto;">

                    {{-- INSTRUCTIONS --}}
                    <div class="alert alert-light border mb-3">
                        <strong>Instructions:</strong>
                        <ul class="mb-0 small">
                            <li>Download the template file.</li>
                            <li><b>Do NOT change column names or order.</b></li>
                            <li>Use valid <b>department names</b> only.</li>
                            <li>Date format must be: <b>YYYY-MM-DD</b></li>
                            <li>No duplicate asset names allowed.</li>
                        </ul>
                    </div>

                    {{-- TEMPLATE DOWNLOAD --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-outline-dark"
                                wire:click="downloadTemplate">
                            Download Template
                        </button>

                        @if($importFile)
                            <span class="text-success small">
                                File selected: {{ $importFile->getClientOriginalName() }}
                            </span>
                        @endif
                    </div>

                    {{-- FILE INPUT --}}
                    <input type="file"
                        class="form-control mb-2 @error('importFile') is-invalid @enderror"
                        wire:model.live="importFile">

                    @error('importFile')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    {{-- ERRORS --}}
                    @if(!empty($importErrors))
                        <div class="alert alert-danger mt-3" style="border-radius:8px;">
                            <div class="fw-bold mb-2">⚠ Import Errors</div>
                            <ul class="mb-0 small">
                                @foreach($importErrors as $error)
                                    <li>
                                        <strong>Row {{ $error['row'] }}:</strong> {{ $error['error'] }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                </div>

                {{-- FOOTER --}}
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">

                    <button type="button"
                            class="btn btn-secondary w-50"
                            wire:click="$set('showImportModal', false)">
                        Close
                    </button>

                    <button class="btn btn-dark w-50"
                            wire:click="importAssets">
                        Import Assets
                    </button>

                </div>

            </div>
        </div>
    @endif
    @if($showViewModal && $viewItem)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(15,23,42,0.65); z-index:1050; backdrop-filter: blur(5px);"
            wire:click="$set('showViewModal', false)">

            <div class="card shadow-lg w-100 p-0"
                style="max-width: 1000px; border: transparent;"
                wire:click.stop>

                {{-- HEADER --}}
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $viewItem->name }}</h4>
                    <button class="btn-close btn-close-white"
                            wire:click="$set('showViewModal', false)"></button>
                </div>

                {{-- BODY --}}
                <div class="card-body" style="max-height:75vh; overflow-y:auto;">

                    {{-- SUMMARY CARDS --}}
                    <div class="row g-3 mb-4">

                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted">Department</small>
                                <div class="fw-bold">{{ $viewItem->department->name ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted">Initial Purchase Date</small>
                                <div class="fw-bold">
                                    {{ \Carbon\Carbon::parse($viewItem->initial_purchase_date)->format('d M Y') }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted">Initial Quantity</small>
                                <div class="fw-bold">{{ number_format($viewItem->initial_quantity,2) }}</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted">Initial Unit Cost</small>
                                <div class="fw-bold">{{ number_format($viewItem->initial_unit_cost,2) }}</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted">Purchased Quantity</small>
                                <div class="fw-bold text-success">
                                    {{ number_format($viewItem->purchased_quantity,2) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted">Damaged Quantity</small>
                                <div class="fw-bold text-danger">
                                    {{ number_format($viewItem->damaged_quantity,2) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted">Current Quantity</small>
                                <div class="fw-bold text-primary">
                                    {{ number_format($viewItem->current_quantity,2) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted">Average Cost</small>
                                <div class="fw-bold">
                                    {{ number_format($viewItem->average_unit_cost,2) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-dark text-white">
                                <small>Current Value</small>
                                <div class="fw-bold fs-5">
                                    {{ number_format($viewItem->current_quantity * $viewItem->average_unit_cost,2) }}
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- PURCHASE HISTORY --}}
                    <div class="mb-4">
                        <h5 class="fw-bold">Purchase History</h5>

                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Quantity</th>
                                    <th>Unit Cost</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($viewItem->purchaseItems as $p)
                                <tr>
                                    <td>{{ $p->purchase->created_at->format('d M Y') }}</td>
                                    <td>{{ number_format($p->quantity,2) }}</td>
                                    <td>{{ number_format($p->unit_cost,2) }}</td>
                                    <td>{{ number_format($p->quantity * $p->unit_cost,2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No purchases recorded</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- DAMAGE HISTORY --}}
                    <div>
                        <h5 class="fw-bold">Damage History</h5>

                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Quantity</th>
                                    <th>Cost</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($viewItem->damagedItems as $d)
                                <tr>
                                    <td>{{ $d->created_at->format('d M Y') }}</td>
                                    <td class="text-danger">{{ number_format($d->quantity,2) }}</td>
                                    <td>{{ number_format($d->average_unit_cost,2) }}</td>
                                    <td>{{ $d->notes }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No damage recorded</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="card-footer text-end bg-light">
                    <button class="btn btn-secondary"
                            wire:click="$set('showViewModal', false)">
                        Close
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>
