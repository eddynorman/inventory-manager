<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Purchases</h5>
        @if(auth()->user()->hasAnyRole(['super','admin','manager']))
            <button wire:click="create" class="btn btn-primary">New Purchase</button>
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
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Requisition</th>
                        <th>Purchaser</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchases as $p)
                        <tr>
                            <td>{{ $p->purchase_date }}</td>
                            <td>{{ optional($p->supplier)->name }}</td>
                            <td>{{ optional($p->requisition)->id }}</td>
                            <td>{{ optional($p->purchaser)->name }}</td>
                            <td>{{ number_format($p->total_amount,2) }}</td>
                            <td><span class="badge text-bg-{{ $p->payment_status === 'paid' ? 'success' : ($p->payment_status === 'partial' ? 'warning':'secondary') }}">{{ ucfirst($p->payment_status) }}</span></td>
                            <td class="text-end">
                                @if(auth()->user()->hasAnyRole(['super','admin']))
                                    <button class="btn btn-sm btn-outline-secondary" wire:click="edit({{ $p->id }})">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="confirmDelete({{ $p->id }})">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $purchases->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="purchaseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $purchaseId ? 'Edit Purchase' : 'New Purchase' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" wire:model.defer="purchase_date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Requisition</label>
                            <select class="form-select" wire:model.defer="requisition_id">
                                <option value="">Select...</option>
                                @foreach($requisitions as $r)
                                    <option value="{{ $r->id }}">#{{ $r->id }} - {{ ucfirst($r->status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <select class="form-select" wire:model.defer="supplier_id">
                                <option value="">Select...</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Purchaser</label>
                            <select class="form-select" wire:model.defer="purchased_by_id">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Amount</label>
                            <input type="number" step="0.01" class="form-control" wire:model.defer="total_amount">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Status</label>
                            <select class="form-select" wire:model.defer="payment_status">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="partial">Partial</option>
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
    <div class="modal fade" id="purchaseDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Are you sure you want to delete this purchase?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('show-purchase-modal', () => new bootstrap.Modal(document.getElementById('purchaseModal')).show());
        window.addEventListener('hide-purchase-modal', () => bootstrap.Modal.getInstance(document.getElementById('purchaseModal'))?.hide());
        window.addEventListener('show-delete-modal', () => new bootstrap.Modal(document.getElementById('purchaseDeleteModal')).show());
        window.addEventListener('hide-delete-modal', () => bootstrap.Modal.getInstance(document.getElementById('purchaseDeleteModal'))?.hide());
    </script>
</div>


