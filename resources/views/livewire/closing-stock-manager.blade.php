<div>
    @include('layouts.flash')

    <!-- Alpine Root Component Wrap -->
    <div class="card shadow-sm"
         x-data="{
            items: [],
            init() {
                // Listen to Livewire's dispatch to instantly load data into Alpine state
                $wire.on('items-loaded', (event) => {
                    this.items = event.items.map(item => ({
                        ...item,
                        closing_stock: parseFloat(item.closing_stock) || 0
                    }));
                });
            },
            // Computed property for client-side totals
            get totals() {
                let opening = 0, used = 0, closing = 0;
                this.items.forEach(item => {
                    let op = parseFloat(item.opening_stock) || 0;
                    let cl = parseFloat(item.closing_stock) || 0;
                    opening += op;
                    closing += cl;
                    used += Math.max(0, op - cl);
                });
                return { opening, used, closing };
            }
         }">

        <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
            <div>
                <h5 class="mb-0 fw-semibold">Stock Closing</h5>
                <small class="text-muted">Record closing stock for a given location.</small>
            </div>
            <div>
                <select wire:model.live="locationId" class="form-select w-auto @error('locationId') is-invalid @endif">
                    <option value="">Select Location</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                    @endforeach
                </select>
                @error('locationId')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div class="card-body">
            <div class="container-fluid">
                <!-- Table -->
                <div class="table-responsive" style="max-height: 70vh;">
                    <table class="table table-hover table-striped align-middle text-center">
                        <thead class="sticky-top">
                            <tr>
                                <th>Item</th>
                                <th>Opening</th>
                                <th>Used</th>
                                <th style="width:140px;">Closing</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- Loop over Alpine data structure, not Livewire arrays -->
                            <template x-for="(item, index) in items" :key="item.item_id">
                                <tr>
                                    <td class="text-start fw-semibold" x-text="item.name"></td>
                                    <td x-text="parseFloat(item.opening_stock).toFixed(2)"></td>

                                    <!-- Automatically updates as user types -->
                                    <td class="fw-bold text-danger"
                                        x-text="Math.max(0, parseFloat(item.opening_stock) - (parseFloat(item.closing_stock) || 0)).toFixed(2)">
                                    </td>

                                    <td>
                                        <!-- Real time validation and bindings -->
                                        <input type="number" min="0" :max="item.opening_stock"
                                            x-model.number="item.closing_stock"
                                            @input="if(item.closing_stock > item.opening_stock) item.closing_stock = item.opening_stock; if(item.closing_stock < 0) item.closing_stock = 0"
                                            class="form-control text-center">

                                        <span class="text-danger small" x-show="item.closing_stock > item.opening_stock">
                                            Exceeds opening stock
                                        </span>
                                    </td>
                                </tr>
                            </template>

                            <!-- Fallback UI for zero loading data -->
                            <tr x-show="items.length === 0">
                                <td colspan="4" class="text-muted py-4">No items loaded. Select a location above.</td>
                            </tr>
                        </tbody>

                        <!-- Totals updating smoothly in real-time -->
                        <tfoot class="table-light fw-bold" x-show="items.length > 0">
                            <tr>
                                <td>Total</td>
                                <td x-text="totals.opening.toFixed(2)"></td>
                                <td class="text-danger" x-text="totals.used.toFixed(2)"></td>
                                <td x-text="totals.closing.toFixed(2)"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Action Button passing data to backend only when ready -->
                <div class="d-flex justify-content-end mt-3">
                    <button @click="$wire.save(items)"
                            wire:loading.attr="disabled"
                            class="btn btn-success px-4"
                            :disabled="items.length === 0">
                        <span wire:loading.remove>Save</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
