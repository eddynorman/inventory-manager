<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Banking;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BankingService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    /* =======================
     * ACCOUNT METHODS
     * ======================= */

    public function getAllAccounts()
    {
        return BankAccount::latest()->get();
    }

    public function getAccountById($id)
    {
        return BankAccount::findOrFail($id);
    }

    public function saveAccount(array $data, $id = null)
    {
        return DB::transaction(function () use ($data, $id) {
            if ($id) {
                $account = BankAccount::findOrFail($id);
                $account->update($data);
            } else {
                $account = BankAccount::create($data);
            }

            return $account;
        });
    }

    /* =======================
     * TRANSACTION METHODS
     * ======================= */

    public function getAllTransactions()
    {
        return Banking::with(['account', 'recordedBy'])
            ->latest()
            ->get();
    }

    public function getTransactionById($id)
    {
        return Banking::with(['account', 'recordedBy'])
            ->findOrFail($id);
    }

    public function saveTransaction(array $data)
    {
        return DB::transaction(function () use ($data) {

            $account = BankAccount::findOrFail($data['bank_account']);

            // adjust balance
            if ($data['type'] === 'deposit') {
                $account->balance += $data['amount'];
            } elseif ($data['type'] === 'withdraw') {
                if ($account->balance < $data['amount']) {
                    throw new \Exception("Insufficient balance");
                }
                $account->balance -= $data['amount'];
            }

            $account->save();

            return Banking::create($data);
        });
    }

    /* =======================
     * VALIDATION RULES
     * ======================= */

    public static function accountRules($accountId = null)
    {
        return [
            'bank_name' => ['required','string','max:255'],
            'account_number' => ['required','string','max:255',Rule::unique('bank_accounts', 'account_number')->ignore($accountId),
        ],
            'balance' => ['nullable','numeric','min:0'],
            'account_id' =>['nullable','integer','exists:bank_accounts,id'],
        ];
    }

    public static function transactionRules()
    {
        return [
            'bank_account' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:deposit,withdraw',
            'description' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpg,png,pdf|max:2048',
        ];
    }

}
