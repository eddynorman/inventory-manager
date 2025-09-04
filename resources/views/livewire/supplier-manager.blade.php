<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Suppliers</h5>
        @if(auth()->user()->hasAnyRole(['super','admin','manager']))
            <button wire:click="create" class="btn btn-primary">New Supplier</button>
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
                        <th>Email</th>
                        <th>City</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $s)
                        <tr>
                            <td>{{ $s->name }}</td>
                            <td>{{ $s->email }}</td>
                            <td>{{ $s->city }}</td>
                            <td class="text-end">
                                @if(auth()->user()->hasAnyRole(['super','admin']))
                                    <button class="btn btn-sm btn-outline-secondary" wire:click="edit({{ $s->id }})">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="confirmDelete({{ $s->id }})">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $suppliers->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="supplierModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $supplierId ? 'Edit Supplier' : 'New Supplier' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" wire:model.defer="name">
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" wire:model.defer="email">
                        @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Zip</label>
                            <input type="text" class="form-control" wire:model.defer="zip_code">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" wire:model.defer="city">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Street</label>
                            <input type="text" class="form-control" wire:model.defer="street">
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
    <div class="modal fade" id="supplierDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Are you sure you want to delete this supplier?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('show-supplier-modal', () => new bootstrap.Modal(document.getElementById('supplierModal')).show());
        window.addEventListener('hide-supplier-modal', () => bootstrap.Modal.getInstance(document.getElementById('supplierModal'))?.hide());
        window.addEventListener('show-delete-modal', () => new bootstrap.Modal(document.getElementById('supplierDeleteModal')).show());
        window.addEventListener('hide-delete-modal', () => bootstrap.Modal.getInstance(document.getElementById('supplierDeleteModal'))?.hide());
    </script>
</div>


