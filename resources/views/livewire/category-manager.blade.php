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
    @if($showViewModal)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showViewModal', false)">

            <div class="card shadow-lg w-100" style="max-width: 500px;" wire:click.stop>
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Category Details</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showViewModal', false)"></button>
                </div>
                <div class="card-body">
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
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button class="btn btn-secondary" wire:click="$set('showViewModal', false)">Cancel</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showCategoryModal)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showCategoryModal', false)">

            <div class="card shadow-lg w-100" style="max-width: 700px; height: auto;" wire:click.stop>
                <!-- Header -->
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">{{ $categoryId ? 'Edit Category' : 'New Category' }}</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showCategoryModal', false)"></button>
                </div>


                <form action="#" method="post" wire:submit.prevent="save">
                    <!-- Body (scrollable) -->
                    <div class="card-body overflow-auto">
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

                    <!-- Footer -->
                    <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showCategoryModal', false)">Close</button>
                        <button type="submit" class="btn btn-primary" >Save</button>
                    </div>
                </form>

            </div>
        </div>
    @endif

    <!-- Delete Modal -->
    @if($showDeleteModal)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showDeleteModal', false)">

            <div class="card shadow-lg w-100" style="max-width: 700px; height: auto;" wire:click.stop>
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showDeleteModal', false)"></button>
                </div>
                <div class="card-body overflow-auto">
                    <p>Are you sure you want to delete this category?</p>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk delete modal -->
    @if($showBulkDeleteModal)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index:1050;" wire:click="$set('showBulkDeleteModal', false)">

            <div class="card shadow-lg w-100" style="max-width: 700px; height: auto;" wire:click.stop>
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showBulkDeleteModal', false)"></button>
                </div>
                <div class="card-body overflow-auto">
                    <p>Are you sure you want to delete these {{ count($bulkIds) }} categories?</p>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showBulkDeleteModal', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="bulkDelete">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>

