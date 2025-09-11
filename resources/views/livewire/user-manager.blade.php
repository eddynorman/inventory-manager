<div>
    @include('layouts.flash')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Users</h5>
            <button wire:click="create" class="btn btn-primary">New User</button>
        </div>

        <div class="card-body">
            <livewire:tables.user-table/>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 d-flex align-items-center justify-content-center bg-dark bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg w-100" style="max-width: 30rem; max-height: 80%; overflow-y: auto;">

                <div class="bg-primary text-white px-4 py-3 rounded-top d-flex justify-content-between align-items-center">
                    <h5>{{ $userId ? 'Edit User' : 'New User' }}</h5>
                    <button class="btn-close btn-close-white" wire:click="$set('showModal', false)"></button>
                </div>

                <div class="p-4" >
                    <div class="mb-3">
                        <label class="form-label" for="">Name</label>
                        <input type="text" name="name" class="form-control" wire:model.defer="name">
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="">Email</label>
                        <input type="email" class="form-control" wire:model.defer="email">
                        @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="">Password</label>
                        <input type="password" class="form-control" wire:model.defer="password">
                        @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="">Type</label>
                        <select class="form-select" wire:model.defer="type">
                            <option value="staff">Staff</option>
                            <option value="customer">Customer</option>
                            <option value="supplier">Supplier</option>
                            <option value="accountant">Accountant</option>
                            <option value="other">Other</option>
                        </select>
                        @error('type')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="">Role</label>
                        <select class="form-select" wire:model.defer="role">
                            <option value="super">Super</option>
                            <option value="admin">Admin</option>
                            <option value="manager">Manager</option>
                        </select>
                        @error('role')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" id="active" class="form-check-input" wire:model.defer="isActive">
                        <label class="form-check-label" for="active">Active</label>
                    </div>
                </div>

                <div class="bg-light px-4 py-3 rounded-bottom d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary" wire:click="$set('showModal', false)">Close</button>
                    <button class="btn btn-primary" wire:click="save">Save</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 d-flex align-items-center justify-content-center bg-dark bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg w-100" style="max-width: 400px;">
                <div class="bg-danger text-white px-4 py-3 rounded-top d-flex justify-content-between align-items-center">
                    <h5>Confirm Delete</h5>
                    <button class="btn-close btn-close-white" wire:click="$set('showDeleteModal', false)"></button>
                </div>
                <div class="p-4">
                    <p>Are you sure you want to delete this user?</p>
                </div>
                <div class="bg-light px-4 py-3 rounded-bottom d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Cancel</button>
                    <button class="btn btn-danger" wire:click="delete">Delete</button>
                </div>
            </div>
        </div>
    @endif

</div>
