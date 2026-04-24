<div>
    @include('layouts.flash')
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
            <div>
                <h5 class="mb-0 fw-semibold">Banking Management</h5>
                <small class="text-muted">Manage accounts and transactions</small>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm shadow-sm"
                    wire:click="$set('showTxnForm', true)">
                    <i class="fa fa-plus-circle me-1"></i> Transaction
                </button>

                <button class="btn btn-primary btn-sm shadow-sm"
                    wire:click="$set('showAccountForm', true)">
                    <i class="fa fa-university me-1"></i> Account
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- TRANSACTIONS --}}
                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light fw-semibold">
                            <i class="fa fa-exchange-alt me-1"></i> Transactions
                        </div>

                        <div class="card-body p-2">
                            <livewire:tables.banking-table/>
                        </div>
                    </div>
                </div>

                {{-- ACCOUNTS --}}
                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light fw-semibold">
                            <i class="fa fa-wallet me-1"></i> Accounts
                        </div>

                        <div class="card-body p-2">
                            <ul class="list-group list-group-flush">
                                @forelse ($accounts as $acc)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold">{{ $acc->bank_name }}</div>
                                            <small class="text-muted">
                                                {{ $acc->account_number }}
                                            </small>
                                        </div>

                                        <div class="text-end">
                                            <div class="fw-bold text-success">
                                                {{ number_format($acc->balance,2) }}
                                            </div>

                                            <button wire:click="editAccount({{ $acc->id }})"
                                                class="btn btn-sm btn-outline-primary mt-1">
                                                <i class="fa fa-pen"></i>
                                            </button>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted">
                                        <i class="fa fa-inbox mb-1"></i><br>
                                        No accounts added
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @if($showAccountForm)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:2100;"
            wire:click="$set('showAccountForm', false)">

            <div class="card shadow-lg w-100"
                style="max-width: 500px;"
                wire:click.stop>

                <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between">
                    Input Account info
                    <button class="btn-close"
                        wire:click="$set('showAccountForm', false)">
                    </button>
                </div>

                <div class="card-body">
                    <input wire:model="bank_name" class="form-control mb-2" placeholder="Bank Name">
                    @error('bank_name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                    <input wire:model="account_number" class="form-control mb-2" placeholder="Account Number">
                    @error('account_number')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                    <input wire:model="balance" class="form-control mb-2" placeholder="Balance" @if($account_id != null)disabled @endif>
                    @error('balance')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary"
                        wire:click="$set('showAccountForm', false)">
                        Cancel
                    </button>

                    <button wire:click="saveAccount" class="btn btn-primary">
                        Save Account
                    </button>
                </div>
            </div>
        </div>
    @endif
    @if($showTxnForm)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:2100;"
            wire:click="$set('showTxnForm', false)">

            <div class="card shadow-lg w-100 d-flex flex-column"
                style="max-width: 500px; max-height: 90vh;"
                wire:click.stop>

                <div class="card-header bg-primary text-white fw-semibold d-flex justify-content-between">
                    Add Transaction
                    <button class="btn-close btn-close-white"
                        wire:click="$set('showTxnForm', false)">
                    </button>
                </div>

                <div class="card-body overflow-auto" style="max-height: 65vh;">
                    <select wire:model="bank_account" class="form-control mb-2">
                        <option value="">Select Account</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->bank_name }} | {{ $acc->account_number }}</option>
                        @endforeach
                    </select>
                    @error('bank_account')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    <input wire:model="amount" class="form-control mb-2" placeholder="Amount">
                    @error('amount')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    <select wire:model="type" class="form-control mb-2">
                        <option value="deposit">Deposit</option>
                        <option value="withdraw">Withdraw</option>
                    </select>
                    @error('type')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    <textarea wire:model="description" class="form-control mb-2" placeholder="Description"></textarea>
                    @error('description')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    <input type="file" wire:model="receipt" class="form-control mb-2">
                    @error('receipt')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary"
                        wire:click="$set('showTxnForm', false)">
                        Cancel
                    </button>

                    <button wire:click="saveTransaction" class="btn btn-success">
                        Save Transaction
                    </button>
                </div>
            </div>
        </div>
    @endif
    @if($showViewTxn && $viewTxn)
        <div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(0,0,0,0.5); z-index:2100;"
            wire:click="$set('showViewTxn', false)">

            <div class="card shadow-lg w-100 d-flex flex-column"
                style="max-width: 550px; max-height: 90vh;"
                wire:click.stop>

                {{-- HEADER --}}
                <div class="card-header bg-info text-white fw-semibold d-flex justify-content-between">
                    Transaction Details
                    <button class="btn-close btn-close-white"
                        wire:click="$set('showViewTxn', false)">
                    </button>
                </div>

                {{-- BODY --}}
                <div class="card-body overflow-auto" style="max-height: 65vh;">

                    <div class="mb-3 text-center">
                        <h5 class="fw-bold mb-0">
                            TXN #{{ str_pad($viewTxn->id,5,'0',STR_PAD_LEFT) }}
                        </h5>
                        <small class="text-muted">
                            {{ $viewTxn->created_at?->format('d M Y, H:i') }}
                        </small>
                    </div>

                    <hr>

                    <div class="row mb-2">
                        <div class="col-6 text-muted">Account</div>
                        <div class="col-6 text-end fw-semibold">
                            {{ $viewTxn->account?->bank_name ?? '-' }}
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-6 text-muted">Amount</div>
                        <div class="col-6 text-end fw-bold
                            {{ $viewTxn->type === 'deposit' ? 'text-success' : 'text-danger' }}">
                            {{ number_format($viewTxn->amount,2) }}
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-6 text-muted">Type</div>
                        <div class="col-6 text-end">
                            @if($viewTxn->type === 'deposit')
                                <span class="badge bg-success">Deposit</span>
                            @else
                                <span class="badge bg-danger">Withdraw</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-6 text-muted">Recorded By</div>
                        <div class="col-6 text-end">
                            {{ $viewTxn->recordedBy?->name ?? '-' }}
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-6 text-muted">Description</div>
                        <div class="col-6 text-end">
                            {{ $viewTxn->description ?? '-' }}
                        </div>
                    </div>

                    @if($viewTxn->receipt_path)
                    <div class="mt-3">
                        <div class="text-muted mb-1">Receipt</div>
                        <a href="{{ asset('storage/'.$viewTxn->receipt_path) }}"
                        target="_blank"
                        class="btn btn-sm btn-outline-primary w-100">
                            <i class="fa fa-file me-1"></i> View Receipt
                        </a>
                    </div>
                    @endif

                </div>

                {{-- FOOTER --}}
                <div class="card-footer d-flex justify-content-end bg-light">
                    <button class="btn btn-secondary"
                        wire:click="$set('showViewTxn', false)">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
