<div>
    @include('layouts.flash')
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
            <div>
                <h5 class="mb-0 fw-semibold">Stock Closing</h5>
                <small class="text-muted">Record closing stock for a given location.</small>
            </div>
            <select wire:model.live="locationId" class="form-select w-auto">
                <option value="">Select Location</option>
                @foreach($locations as $loc)
                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                @endforeach
            </select>
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
                                <th style="width:120px;">Closing</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($items as $index => $item)
                                <tr>
                                    <td class="text-start fw-semibold">
                                        {{ $item['name'] }}
                                    </td>

                                    <td>{{ $item['opening_stock'] }}</td>

                                    <td class="fw-bold text-danger">
                                        {{ $item['used'] }}
                                    </td>

                                    <td>
                                        <input type="number" min="0"
                                            wire:model.lazy="items.{{ $index }}.closing_stock"
                                            class="form-control text-center @error("items.$index.closing_stock") is-invalid @enderror">

                                        @error("items.$index.closing_stock")
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <!-- Totals -->
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>Total</td>
                                <td>{{ $this->totals['opening'] }}</td>
                                <td class="text-danger">{{ $this->totals['used'] }}</td>
                                <td>{{ $this->totals['closing'] }}</td>
                            </tr>
                        </tfoot>

                    </table>
                </div>

                <!-- Action -->
                <div class="d-flex justify-content-end mt-3">
                    <button wire:click="save"
                            wire:loading.attr="disabled"
                            class="btn btn-success px-4">
                        <span wire:loading.remove>Save</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
