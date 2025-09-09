<div>
    @include('layouts.flash')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Categories</h5>
        @if(auth()->user()->hasAnyRole(['super','admin','manager']))
            <button wire:click="create" class="btn btn-primary">New Category</button>
        @endif
    </div>

    <div class="card">
        <div class=" card-body table-responsive px-2">
             @livewire('tables.category-table')
        </div>
    </div>
    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Category Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($categoryId)
                        <h5>{{ $name }}</h5>
                        <p>{{ $description }}</p>
                        <h6>Items in this Category:</h6>
                        <dl class="row">
                            <dt class="col-sm-9">Name</dt>
                            <dd class="col-sm-3"><b>Current Stock</b></dd>
                            @if($items)
                                @foreach($items as $item)
                                    <dt class="col-sm-9">{{ $item['name'] }}</dt>
                                    <dd class="col-sm-3">{{ $item['current_stock'] }}</dd>
                                @endforeach
                            @endif
                        </dl>
                    @else
                        <p>Loading...</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">{{ $categoryId ? 'Edit Category' : 'New Category' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="name">Name</label>
                        <input type="text" id="name" class="form-control" wire:model.defer="name">
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" class="form-control" rows="3" wire:model.defer="description"></textarea>
                        @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
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
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this category?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Bulk delete modal -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete these categories?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="bulkDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.addEventListener('show-view-modal', () => new bootstrap.Modal(document.getElementById('viewModal')).show());
        window.addEventListener('show-category-modal', () => new bootstrap.Modal(document.getElementById('categoryModal')).show());
        window.addEventListener('hide-category-modal', () => bootstrap.Modal.getInstance(document.getElementById('categoryModal'))?.hide());
        window.addEventListener('show-delete-modal', () => new bootstrap.Modal(document.getElementById('deleteModal')).show());
        window.addEventListener('hide-delete-modal', () => bootstrap.Modal.getInstance(document.getElementById('deleteModal'))?.hide());
        window.addEventListener('show-bulk-delete-modal', () => new bootstrap.Modal(document.getElementById('bulkDeleteModal')).show());
        window.addEventListener('hide-bulk-delete-modal', () => bootstrap.Modal.getInstance(document.getElementById('bulkDeleteModal'))?.hide());

        window.addEventListener('bulkDelete.category-table-1uitsn-table', ( event ) => {
            alert("Bulk delete triggered for table: " + event.detail.table);
            const selectedIds = window.pgBulkActions.get(event.detail.table) || [];
            alert("Selected IDs: " + selectedIds.join(", "));
            window.dispatchEvent(new CustomEvent('bulkDeleteConfirmWithIds', { detail: { ids: selectedIds } }));
        });
    </script>
</div>

