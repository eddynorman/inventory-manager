<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\BankingService;
use Illuminate\Support\Facades\Auth;

class BankingManager extends Component
{
    use WithFileUploads;

    public $accounts = [];
    public $transactions = [];

    // account fields
    public $bank_name, $account_number, $balance, $account_id;

    // transaction fields
    public $bank_account, $amount, $type = 'deposit', $description, $receipt;

    public $viewTxn = null;

    public bool $showViewAccount = false;
    public bool $showViewTxn = false;
    public bool $showAccountForm = false;
    public bool $showTxnForm = false;
    protected $service;

    protected $listeners = [
        'viewTxn' => 'viewTxnDetails',
    ];

    public function boot(BankingService $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->accounts = $this->service->getAllAccounts();
        $this->transactions = $this->service->getAllTransactions();
    }

    /* =======================
     * ACCOUNT ACTIONS
     * ======================= */

    public function updatedShowAccountForm(){
        if($this->showAccountForm == false){
            $this->reset(['bank_name', 'account_number', 'balance', 'account_id']);
        }
    }

    public function saveAccount()
    {

        $data = $this->validate(BankingService::accountRules($this->account_id));

        $this->service->saveAccount($data, $this->account_id);

        $this->reset(['bank_name', 'account_number', 'balance', 'account_id']);

        $this->showAccountForm = false;
        $this->loadData();
        $this->dispatch('scrollToTop');
        session()->flash('success', 'Account saved');
        $this->dispatch('flash');
    }

    public function editAccount($id)
    {
        $acc = $this->service->getAccountById($id);

        $this->account_id = $acc->id;
        $this->bank_name = $acc->bank_name;
        $this->account_number = $acc->account_number;
        $this->balance = $acc->balance;
        $this->showAccountForm = true;
    }

    /* =======================
     * TRANSACTION ACTIONS
     * ======================= */

    public function updatedShowTxnForm(){
        if($this->showTxnForm == false){
            $this->reset(['bank_account', 'amount', 'type', 'description', 'receipt']);
        }else{
            if(count($this->accounts)==0){
                $this->showTxnForm = false;
                session()->flash('error', 'Add account first');
                $this->dispatch('flash');
                $this->showAccountForm = true;
            }
        }
    }

    public function refreshTable(){
        $this->dispatch('pg:eventRefresh-banking-table-iq3ldr-table');
    }

    public function saveTransaction()
    {
        $this->validate(BankingService::transactionRules());

        try {
            $path = $this->receipt?->store('banking/receipts', 'public');

            $this->service->saveTransaction([
                'bank_account' => $this->bank_account,
                'amount' => $this->amount,
                'type' => $this->type,
                'description' => $this->description,
                'receipt_path' => $path,
                'recorded_by' => Auth::id(),
            ]);
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
            $this->dispatch('flash');
            return;
        }

        $this->reset(['bank_account', 'amount', 'type', 'description', 'receipt']);

        $this->loadData();
        $this->refreshTable();
        $this->showTxnForm = false;
        session()->flash('success', 'Transaction saved');
        $this->dispatch('flash');
    }

    public function viewTxnDetails($txnId){
        $this->viewTxn = $this->service->getTransactionById($txnId);
        $this->showViewTxn = true;
    }

    public function render()
    {
        return view('livewire.banking-manager');
    }
}
