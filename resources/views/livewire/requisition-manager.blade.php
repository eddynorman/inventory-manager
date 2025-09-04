<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Requisitions</h5>
        @if(auth()->user()->hasAnyRole(['super','admin','manager']))
            <button wire:click="create" class="btn btn-primary">New Requisition</button>
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
                        <th>Status</th>
                        <th>Cost</th>
                        <th>Requested</th>
                        <th>Approved</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requisitions as $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td><span class="badge text-bg-{{ $r->status === 'approved' ? 'success' : ($r->status === 'rejected' ? 'danger':'secondary') }}">{{ ucfirst($r->status) }}</span></td>
                            <td>{{ number_format($r->cost, 2) }}</td>
                            <td>{{ optional($r->requestedBy)->name }} • {{ $r->date_requested }}</td>
                            <td>{{ optional($r->approvedBy)->name ?? '-' }} • {{ $r->date_approved ?? '-' }}</td>
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
            {{ $requisitions->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="requisitionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $reqId ? 'Edit Requisition' : 'New Requisition' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Requested By</label>
                            <select class="form-select" wire:model.defer="requested_by_id">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Approved By</label>
                            <select class="form-select" wire:model.defer="approved_by_id">
                                <option value="">Select...</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cost</label>
                            <input type="number" step="0.01" class="form-control" wire:model.defer="cost">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" wire:model.defer="status">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date Requested</label>
                            <input type="date" class="form-control" wire:model.defer="date_requested">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date Approved</label>
                            <input type="date" class="form-control" wire:model.defer="date_approved">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" rows="3" wire:model.defer="description"></textarea>
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
    <div class="modal fade" id="requisitionDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Are you sure you want to delete this requisition?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('show-requisition-modal', () => new bootstrap.Modal(document.getElementById('requisitionModal')).show());
        window.addEventListener('hide-requisition-modal', () => bootstrap.Modal.getInstance(document.getElementById('requisitionModal'))?.hide());
        window.addEventListener('show-delete-modal', () => new bootstrap.Modal(document.getElementById('requisitionDeleteModal')).show());
        window.addEventListener('hide-delete-modal', () => bootstrap.Modal.getInstance(document.getElementById('requisitionDeleteModal'))?.hide());
    </script>
</div>


