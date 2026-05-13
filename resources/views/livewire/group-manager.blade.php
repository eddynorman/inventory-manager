<div>

    @include('layouts.flash')

    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">

            <div>
                <h4 class="fw-bold mb-1">
                    Group Management
                </h4>

                <small class="text-muted">
                    Manage ERP access groups and permissions
                </small>
            </div>

            <div class="gap-2">
                <button
                    class="btn btn-dark rounded-3 px-4"
                    wire:click="create">

                    <i class="fa fa-plus me-2"></i>
                    New Group
                </button>
                <a href="{{ route('users') }}"><button type="button" class="btn btn-primary rounded-3 px-4">Users</button></a>
            </div>
        </div>

        <div class="card-body p-4">

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>
                        <tr>
                            <th>Group</th>
                            <th>Description</th>
                            <th>Users</th>
                            <th>Permissions</th>
                            <th width="150"></th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($groups as $group)

                        <tr>

                            <td class="fw-semibold">
                                {{ $group->name }}
                            </td>

                            <td class="text-muted">
                                {{ $group->description }}
                            </td>

                            <td>
                                <span class="badge bg-primary-subtle text-primary">
                                    {{ $group->users_count }} Users
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-success-subtle text-success">
                                    {{ $group->permissions_count }} Permissions
                                </span>
                            </td>

                            <td>

                                <div class="d-flex gap-2 justify-content-end">

                                    <button
                                        class="btn btn-sm btn-light"
                                        wire:click="edit({{ $group->id }})">

                                        Edit
                                    </button>

                                    <button
                                        class="btn btn-sm btn-danger"
                                        wire:click="confirmDelete({{ $group->id }})">

                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                No groups found
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>
            </div>
        </div>
    </div>

    {{-- CREATE / EDIT MODAL --}}
    @if($showModal)

        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
             style="background: rgba(15,23,42,0.65); z-index:1050; backdrop-filter: blur(5px);"
             wire:click="$set('showModal', false)">

            <div class="card border-0 shadow-lg rounded-4"
                 style="width: 95%; max-width: 1100px; max-height: 92vh; overflow:hidden;"
                 wire:click.stop>

                <div class="card-header bg-dark text-white border-0 px-4 py-2 d-flex justify-content-between align-items-center">

                    <div>
                        <h4 class="fw-bold mb-1">
                            {{ $groupId ? 'Edit Group' : 'Create Group' }}
                        </h4>

                        <small class="text-white-50">
                            Configure ERP access control permissions
                        </small>
                    </div>

                    <button
                        class="btn-close btn-close-white"
                        wire:click="$set('showModal', false)">
                    </button>
                </div>

                <div class="card-body p-0">

                    <div class="row g-0 mb-1">

                        {{-- LEFT --}}
                        <div class="col-lg-4 border-end">

                            <div class="p-4">

                                <div class="mb-4">

                                    <label class="form-label fw-semibold">
                                        Group Name
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control rounded-3"
                                        wire:model.defer="name">

                                    @error('name')
                                        <small class="text-danger">
                                            {{ $message }}
                                        </small>
                                    @enderror
                                </div>

                                <div class="mb-4">

                                    <label class="form-label fw-semibold">
                                        Description
                                    </label>

                                    <textarea
                                        rows="5"
                                        class="form-control rounded-3"
                                        wire:model.defer="description"></textarea>

                                    @error('description')
                                        <small class="text-danger">
                                            {{ $message }}
                                        </small>
                                    @enderror
                                </div>

                                <div class="alert alert-light border rounded-4">

                                    <div class="fw-semibold mb-2">
                                        Permission Summary
                                    </div>

                                    <div class="display-6 fw-bold">
                                        {{ count($selectedPermissions) }}
                                    </div>

                                    <small class="text-muted">
                                        Permissions Selected
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT --}}
                        <div class="col-lg-8">

                            <div class="p-4 border-bottom">

                                <input
                                    type="text"
                                    class="form-control rounded-3"
                                    placeholder="Search permissions..."
                                    wire:model.live.debounce.300ms="search">
                            </div>

                            <div style="max-height:65vh; overflow-y:auto;">

                                <div class="p-4">

                                    @foreach($permissions as $category => $items)

                                        <div class="card border-0 shadow-sm rounded-4 mb-4">

                                            <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center">

                                                <div class="fw-bold text-capitalize">
                                                    {{ str_replace('_', ' ', $category) }}
                                                </div>

                                                <button
                                                    class="btn btn-sm btn-dark rounded-3"
                                                    wire:click="toggleCategory('{{ $category }}')">

                                                    Toggle All
                                                </button>
                                            </div>

                                            <div class="card-body">

                                                <div class="row">

                                                    @foreach($items as $permission)

                                                        <div class="col-md-6 mb-3">

                                                            <label class="border rounded-4 p-3 w-100 d-flex align-items-start gap-3 cursor-pointer">

                                                                <input
                                                                    type="checkbox"
                                                                    class="form-check-input mt-1"
                                                                    value="{{ $permission->id }}"
                                                                    wire:model="selectedPermissions">

                                                                <div>

                                                                    <div class="fw-semibold">
                                                                        {{ ucwords(str_replace('.', ' ', $permission->name)) }}
                                                                    </div>

                                                                    <small class="text-muted">
                                                                        {{ $permission->description }}
                                                                    </small>
                                                                </div>
                                                            </label>
                                                        </div>

                                                    @endforeach

                                                </div>
                                            </div>
                                        </div>

                                    @endforeach

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-0 p-4 d-flex justify-content-end gap-2">

                    <button
                        class="btn btn-light rounded-3 px-4"
                        wire:click="$set('showModal', false)">

                        Cancel
                    </button>

                    <button
                        class="btn btn-dark rounded-3 px-4"
                        wire:click="save">

                        Save Group
                    </button>
                </div>
            </div>
        </div>

    @endif

</div>
