<div>
    @include('layouts.flash')
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
            <div>
                <h5 class="mb-0 fw-semibold">Expense Management</h5>
                <small class="text-muted">Manage departmental expenses</small>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary"
                    wire:click="$set('showCreateExpense', true)">
                    <i class="fa fa-plus"></i> New Expense
                </button>

                <button class="btn btn-secondary"
                    wire:click="$set('showCreateCategory', true)">
                    <i class="fa fa-tags"></i> Categories
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="card shadow-sm">
                <div class="card-body">
                    <livewire:tables.expense-table />
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- ===================== CREATE CATEGORY ==================== --}}
    {{-- ========================================================= --}}
    @if($showCreateCategory)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:1050;"
            wire:click="$set('showCreateCategory', false)">
            <div class="card shadow-lg w-100" style="max-width: 600px;" wire:click.stop>

                <div class="card-header bg-info fw-bold d-flex justify-content-between">
                    <h5>Create Category</h5>
                    <button class="btn-close"
                        wire:click="$set('showCreateCategory', false)"></button>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" class="form-control"
                            wire:model="category_name">
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea class="form-control"
                            wire:model="category_description"></textarea>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary"
                        wire:click="$set('showCreateCategory', false)">Cancel</button>
                    <button class="btn btn-primary"
                        wire:click="createCategory">Save</button>
                </div>

            </div>

        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- ===================== CREATE EXPENSE ===================== --}}
    {{-- ========================================================= --}}
    @if($showCreateExpense)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:1050;"
            wire:click="$set('showCreateExpense', false)">
            <div class="card shadow-lg w-100" style="max-width: 750px;" wire:click.stop>
                <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between">
                    <h5>Create Expense</h5>
                    <button class="btn-close btn-close-white"
                        wire:click="$set('showCreateExpense', false)"></button>
                </div>

                <div class="card-body" style="max-height:70vh; overflow-y:auto;">

                    {{-- Top fields --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Department</label>
                            <select class="form-control"
                                wire:model="department_id">
                                <option value="">-- select --</option>
                                @foreach($departments ?? [] as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error("department_id")
                                <small class="text-danger">Department is required</small>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label>Category</label>
                            <select class="form-control"
                                wire:model="expense_category_id">
                                <option value="">-- select --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error("expense_category_id")
                                <small class="text-danger">Category is required</small>
                            @enderror
                        </div>
                    </div>

                    {{-- ITEMS --}}
                    <div class="d-flex justify-content-between mb-2">
                        <h6>Items</h6>
                        <button class="btn btn-sm btn-success"
                            wire:click="addItem">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>

                    <table class="table table-responsive table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Cost</th>
                                <th>Del</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ( $items as $index => $item )
                                <tr>
                                    <td>
                                        <input type="text" class="form-control" wire:model="items.{{ $index }}.description">
                                        @error("items.$index.description")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control" wire:model.live="items.{{ $index }}.cost">
                                        @error("items.$index.cost")
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </td>
                                    <td>
                                        <button class="btn btn-danger w-100"
                                            wire:click="removeItem({{ $index }})">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-danger text-center" colspan="3">No Expense Items Added</td>
                                </tr>
                            @endforelse
                            @error('items')
                                <tr>
                                    <td colspan="3" class="text-danger">{{ $message }}</td>
                                </tr>
                            @enderror
                            <tr>
                                <td class="fw-bold text-end">Total:</td>
                                <td class="fw-bold text-center" colspan="2">{{ number_format($amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>

                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary"
                        wire:click="$set('showCreateExpense', false)">Cancel</button>

                    <button class="btn btn-primary"
                        wire:click="createExpense">
                        Save Expense
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- ===================== VIEW EXPENSE ======================= --}}
    {{-- ========================================================= --}}
    @if($showViewExpense && $expense)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:1050;"
            wire:click="$set('showViewExpense', false)">
            <div class="card w-100 shadow-lg" style="max-width: 750px;" wire:click.stop>

                <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between">
                    <h5>Expense #{{ str_pad($expense->id,5,'0',STR_PAD_LEFT) }}</h5>
                    <button class="btn-close btn-close-white"
                        wire:click="$set('showViewExpense', false)">
                    </button>
                </div>

                <div class="card-body" style="max-height:70vh; overflow-y:auto;">

                    <p><strong>Department:</strong> {{ $expense->department?->name }}</p>
                    <p><strong>Category:</strong> {{ $expense->category?->name }}</p>
                    <p><strong>Recorded By:</strong> {{ $expense->recordedBy?->name }}</p>

                    <hr>

                    <h6>Items</h6>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expense->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td class="text-end">{{ number_format($item->cost,2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <hr>

                    <h6>Receipts</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($expense->receipts as $index => $receipt)
                            <a href="{{ asset('storage/'.$receipt->receipt_path) }}"
                                target="_blank"
                                class="btn btn-sm btn-outline-primary">
                                View Receipt {{ $index + 1 }}
                            </a>
                        @endforeach
                    </div>

                </div>

            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- ===================== ADD RECEIPTS ======================= --}}
    {{-- ========================================================= --}}
    @if($showAddReceipt)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:1050;"
            wire:click="$set('showAddReceipt', false)">
                <div class="card w-100 shadow-lg" style="max-width: 600px;" wire:click.stop>

                    <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between">
                        <h5>Add Receipts</h5>
                        <button class="btn-close btn-close-white"
                            wire:click="$set('showAddReceipt', false)"></button>
                    </div>

                    <div class="card-body">
                        <label for="receipts" class="form-label">Select receipts</label>
                        <input type="file" id="receipts" multiple class="form-control"
                            wire:model="receipts">
                        {{-- General error (array level) --}}
                        @error('receipts')
                            <small class="text-danger d-block">{{ $message }}</small>
                        @enderror

                        {{-- Individual file errors --}}
                        @foreach ($errors->get('receipts.*') as $messages)
                            @foreach ($messages as $message)
                                <small class="text-danger d-block">{{ $message }}</small>
                            @endforeach
                        @endforeach
                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <button class="btn btn-secondary"
                            wire:click="$set('showAddReceipt', false)">Cancel</button>

                        <button class="btn btn-primary"
                            wire:click="saveReceipts">
                            Upload
                        </button>
                    </div>

                </div>
        </div>
    @endif

</div>
</div>
