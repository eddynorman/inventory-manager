<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Receivings</h5>
        @if(auth()->user()->hasAnyRole(['super','admin','manager']))
            <button wire:click="create" class="btn btn-primary">New Receiving</button>
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
                        <th>#</th>
                        <th>Purchase</th>
                        <th>Receiver</th>
                        <th>Location</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receivings as $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td>{{ optional($r->purchase)->purchase_date }}</td>
                            <td>{{ optional($r->receiver)->name }}</td>
                            <td>{{ optional($r->location)->name }}</td>
                            <td class="text-end">
                                @if(auth()->user()->hasAnyRole(['super','admin']))
                                    <button class="btn btn-sm btn-outline-secondary" wire:click="edit({{ $r->id }})">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="confirmDelete({{ $r->id }})">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $receivings->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="receivingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $receivingId ? 'Edit Receiving' : 'New Receiving' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Purchase</label>
                            <select class="form-select" wire:model.defer="purchase_id">
                                @foreach($purchases as $p)
                                    <option value="{{ $p->id }}">#{{ $p->id }} • {{ $p->purchase_date }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Receiver</label>
                            <select class="form-select" wire:model.defer="received_by_id">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Location</label>
                            <select class="form-select" wire:model.defer="location_id">
                                <option value="">Select...</option>
                                @foreach($locations as $l)
                                    <option value="{{ $l->id }}">{{ $l->name }}</option>
                                @endforeach
                            </select>
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
    <div class="modal fade" id="receivingDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Are you sure you want to delete this receiving?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('show-receiving-modal', () => new bootstrap.Modal(document.getElementById('receivingModal')).show());
        window.addEventListener('hide-receiving-modal', () => bootstrap.Modal.getInstance(document.getElementById('receivingModal'))?.hide());
        window.addEventListener('show-delete-modal', () => new bootstrap.Modal(document.getElementById('receivingDeleteModal')).show());
        window.addEventListener('hide-delete-modal', () => bootstrap.Modal.getInstance(document.getElementById('receivingDeleteModal'))?.hide());
    </script>
</div>


