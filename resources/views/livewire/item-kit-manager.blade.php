<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Item Kits</h5>
        @if(auth()->user()->hasAnyRole(['super','admin','manager']))
            <button wire:click="create" class="btn btn-primary">New Kit</button>
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
                        <th>Selling Price</th>
                        <th>Includes Tax</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kits as $k)
                        <tr>
                            <td>{{ $k->name }}</td>
                            <td>{{ number_format($k->selling_price, 2) }}</td>
                            <td><span class="badge bg-{{ $k->selling_price_includes_tax ? 'success':'secondary' }}">{{ $k->selling_price_includes_tax ? 'Yes' : 'No' }}</span></td>
                            <td class="text-end">
                                @if(auth()->user()->hasAnyRole(['super','admin']))
                                    <button class="btn btn-sm btn-outline-secondary" wire:click="edit({{ $k->id }})">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="confirmDelete({{ $k->id }})">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $kits->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="kitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $kitId ? 'Edit Kit' : 'New Kit' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" wire:model.defer="name">
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Selling Price</label>
                        <input type="number" step="0.01" class="form-control" wire:model.defer="selling_price">
                        @error('selling_price')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Includes Tax</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="kitTaxSwitch" wire:model.defer="selling_price_includes_tax">
                            <label class="form-check-label" for="kitTaxSwitch">Yes</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" wire:model.defer="description"></textarea>
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
    <div class="modal fade" id="kitDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Are you sure you want to delete this kit?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('show-kit-modal', () => new bootstrap.Modal(document.getElementById('kitModal')).show());
        window.addEventListener('hide-kit-modal', () => bootstrap.Modal.getInstance(document.getElementById('kitModal'))?.hide());
        window.addEventListener('show-delete-modal', () => new bootstrap.Modal(document.getElementById('kitDeleteModal')).show());
        window.addEventListener('hide-delete-modal', () => bootstrap.Modal.getInstance(document.getElementById('kitDeleteModal'))?.hide());
    </script>
</div>


