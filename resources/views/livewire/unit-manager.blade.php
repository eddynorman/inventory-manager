<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Units</h5>
        @if(auth()->user()->hasAnyRole(['super','admin','manager']))
            <button wire:click="create" class="btn btn-primary">New Unit</button>
        @endif
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <input type="text" wire:model.debounce.500ms="search" class="form-control" placeholder="Search...">
                </div>
                <div class="col-auto">
                    <select wire:model="perPage" class="form-select">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped datatable mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Buying</th>
                        <th>Selling</th>
                        <th>Smallest</th>
                        <th>Factor</th>
                        <th>Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($units as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ number_format($u->buying_price, 2) }}</td>
                            <td>{{ $u->selling_price !== null ? number_format($u->selling_price, 2) : '-' }}</td>
                            <td><span class="badge bg-{{ $u->is_smallest_unit ? 'success':'secondary' }}">{{ $u->is_smallest_unit ? 'Yes' : 'No' }}</span></td>
                            <td>{{ $u->smallest_units_number }}</td>
                            <td><span class="badge bg-{{ $u->is_active ? 'success':'secondary' }}">{{ $u->is_active ? 'Yes' : 'No' }}</span></td>
                            <td class="text-end">
                                @if(auth()->user()->hasAnyRole(['super','admin']))
                                    <button class="btn btn-sm btn-outline-secondary" wire:click="edit({{ $u->id }})">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="confirmDelete({{ $u->id }})">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $units->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="unitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $unitId ? 'Edit Unit' : 'New Unit' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" wire:model.defer="name">
                            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Buying Price</label>
                            <input type="number" step="0.01" class="form-control" wire:model.defer="buying_price">
                            @error('buying_price')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Selling Price</label>
                            <input type="number" step="0.01" class="form-control" wire:model.defer="selling_price">
                            @error('selling_price')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Smallest Units Number</label>
                            <input type="number" class="form-control" wire:model.defer="smallest_units_number">
                            @error('smallest_units_number')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="smallestSwitch" wire:model.defer="is_smallest_unit">
                                <label class="form-check-label" for="smallestSwitch">Is Smallest Unit</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="activeSwitch" wire:model.defer="is_active">
                                <label class="form-check-label" for="activeSwitch">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="bpTaxSwitch" wire:model.defer="buying_price_includes_tax">
                                <label class="form-check-label" for="bpTaxSwitch">Buying Price Includes Tax</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="spTaxSwitch" wire:model.defer="selling_price_includes_tax">
                                <label class="form-check-label" for="spTaxSwitch">Selling Price Includes Tax</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="save">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="unitDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Are you sure you want to delete this unit?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('show-unit-modal', () => new bootstrap.Modal(document.getElementById('unitModal')).show());
        window.addEventListener('hide-unit-modal', () => bootstrap.Modal.getInstance(document.getElementById('unitModal'))?.hide());
        window.addEventListener('show-delete-modal', () => new bootstrap.Modal(document.getElementById('unitDeleteModal')).show());
        window.addEventListener('hide-delete-modal', () => bootstrap.Modal.getInstance(document.getElementById('unitDeleteModal'))?.hide());
    </script>
</div>


