<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseItem;
use App\Models\ExpenseReceipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required','exists:departments,id'],
            'expense_category_id' => ['required','exists:expense_categories,id'],
            'items' => ['required','array','min:1'],
            'items.*.description' => ['required','string'],
            'items.*.cost' => ['required','numeric','gt:0'],
            'receipts' => ['nullable','array'],
            'receipts.*' => ['file','mimes:jpg,jpeg,png,pdf','max:2048']
        ];
    }

    public function receiptRules(): array
    {
        return [
            'receipts' => ['required','array'],
            'receipts.*' => ['file','mimes:jpg,jpeg,png,pdf','max:2048']
        ];
    }

    public function createCategory($name,$description)
    {
        return ExpenseCategory::create([
            'name' => $name,
            'description' => $description
        ]);
    }

    public function getCategories(){
        return ExpenseCategory::all();
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE EXPENSE
    |--------------------------------------------------------------------------
    */

    public function create(array $data, int $userId): Expense
    {
        return DB::transaction(function () use ($data, $userId) {
            $amount = 0;
            foreach ($data['items'] as $item) {
                $amount += $item['cost'];
            }
            $expense = Expense::create([
                'department_id' => $data['department_id'],
                'expense_category_id' => $data['expense_category_id'],
                'amount' => $amount,
                'recorded_by' => $userId,
            ]);

            foreach ($data['items'] as $item) {
                ExpenseItem::create([
                    'expense_id' => $expense->id,
                    'description' => $item['description'],
                    'cost' => $item['cost'],
                ]);
            }

            return $expense->load('items');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | GET FULL EXPENSE
    |--------------------------------------------------------------------------
    */

    public function find(int $id): Expense
    {
        return Expense::with([
            'department',
            'category',
            'recordedBy',
            'receipts',
            'items'
        ])->findOrFail($id);
    }

    /*
    |--------------------------------------------------------------------------
    | ADD RECEIPTS
    |--------------------------------------------------------------------------
    */

    public function addReceipts(int $expenseId, array $files): void
    {
        DB::transaction(function () use ($expenseId, $files) {

            foreach ($files as $file) {
                $path = $file->store('expenses/receipts', 'public');

                ExpenseReceipt::create([
                    'expense_id' => $expenseId,
                    'receipt_path' => $path,
                ]);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE RECEIPT
    |--------------------------------------------------------------------------
    */

    public function deleteReceipt(int $receiptId): void
    {
        $receipt = ExpenseReceipt::findOrFail($receiptId);

        Storage::disk('public')->delete($receipt->receipt_path);

        $receipt->delete();
    }
}
