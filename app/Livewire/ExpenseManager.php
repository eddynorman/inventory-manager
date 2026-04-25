<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\ExpenseCategory;
use App\Services\ExpenseService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ExpenseManager extends Component
{
    use WithFileUploads;

    protected ExpenseService $service;

    /*
    |--------------------------------------------------------------------------
    | STATE
    |--------------------------------------------------------------------------
    */

    public $showCreateCategory = false;
    public $showCreateExpense = false;
    public $showViewExpense = false;
    public $showAddReceipt = false;

    public $selectedExpenseId;

    // Expense form
    public $department_id;
    public $expense_category_id;
    public $amount;
    public $items = [];

    // Category form
    public $category_name;
    public $category_description;

    // Receipts
    public $receipts = [];

    // View data
    public $expense;
    public $categories;
    public $departments;

    //listeners
    protected $listeners = [
        'viewExpense' => 'openViewModal',
        'addReceipt' => 'openReceiptModal',
    ];

    public function boot(ExpenseService $service)
    {
        $this->service = $service;
        $this->categories = $this->service->getCategories();
        $this->departments = Department::all();
    }


    /*
    |--------------------------------------------------------------------------
    | CATEGORY
    |--------------------------------------------------------------------------
    */

    public function createCategory()
    {
       try {
            $this->service->createCategory($this->category_name,$this->category_description);
            $this->reset(['category_name','category_description','showCreateCategory']);
            session()->flash('success','Category saved');
            $this->dispatch('flash');
       } catch (\Throwable $th) {
            session()->flash('error',$th->getMessage());
            $this->dispatch('flash');
       }

    }

    public function refreshTable(){
        $this->dispatch('pg:eventRefresh-expense-table-tl72v5-table');
    }

    /*
    |--------------------------------------------------------------------------
    | EXPENSE
    |--------------------------------------------------------------------------
    */

    public function updatedShowCreateExpense(){
        if($this->showCreateExpense == true){
            if(count($this->categories) == 0){
                session()->flash('error','Create Category first!');
                $this->dispatch('flash');
                $this->showCreateExpense  = false;
                $this->showCreateCategory = true;
                return;
            }
            if(count($this->departments) == 0){
                session()->flash('error','Create Department first!');
                $this->dispatch('flash');
                return redirect()->route('departments');
            }
            $this->reset(['department_id','expense_category_id','amount','items']);
            $this->addItem();
        }else{
            $this->reset(['department_id','expense_category_id','amount','items','showCreateExpense']);
        }
    }

    public function addItem()
    {
        $this->items[] = ['description' => '', 'cost' => 0];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value,$keys){
        $key = explode('.',$keys)[1];
        if($key == 'cost'){
            $this->calculateTotal();
        }
    }

    public function calculateTotal(){
        $this->amount = 0;
        foreach($this->items as $item){
            if($item['cost'] != '' && $item['cost'] != null){
                $this->amount += $item['cost'];
            }else{
                $this->amount += 0;
            }
        }
    }

    public function createExpense()
    {

        $data = $this->validate($this->service->rules());

        try {

            $this->service->create($data, Auth::id());

            $this->reset(['department_id','expense_category_id','amount','items','showCreateExpense']);

            $this->refreshTable();
            session()->flash('success',"Expense saved successfully!");
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            session()->flash('error',$th->getMessage());
            $this->dispatch('flash');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW EXPENSE
    |--------------------------------------------------------------------------
    */

    public function openViewModal($id)
    {
        $this->expense = $this->service->find($id);
        $this->showViewExpense = true;
    }

    public function updatedShowViewExpense(){
        if($this->showViewExpense == false){
            $this->reset(['expense']);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RECEIPTS
    |--------------------------------------------------------------------------
    */

    public function openReceiptModal($expenseId)
    {
        $this->selectedExpenseId = $expenseId;
        $this->showAddReceipt = true;
    }

    public function saveReceipts()
    {
        $this->validate($this->service->receiptRules());
        try {
            $this->service->addReceipts($this->selectedExpenseId, $this->receipts);
            $this->reset(['receipts','showAddReceipt']);
            session()->flash('success',"Receipts saved successfully!");
            $this->dispatch('flash');
        } catch (\Throwable $th) {
            session()->flash('error',$th->getMessage());
            $this->dispatch('flash');
        }
    }

    public function render()
    {
        return view('livewire.expense-manager');
    }

    public function messages(): array
    {
        return [
            // Department & Category
            'department_id.required' => 'Please select a department.',
            'department_id.exists' => 'Selected department is invalid.',

            'expense_category_id.required' => 'Please select an expense category.',
            'expense_category_id.exists' => 'Selected category is invalid.',

            // Items
            'items.required' => 'At least one expense item is required.',
            'items.array' => 'Items must be in a valid format.',
            'items.min' => 'Add at least one item.',

            'items.*.description.required' => 'Item description is required.',
            'items.*.description.string' => 'Item description must be text.',

            'items.*.cost.required' => 'Item cost is required.',
            'items.*.cost.numeric' => 'Item cost must be a number.',
            'items.*.cost.gt' => 'Item cost must be greater than 0.',

            // Receipts (optional)
            'receipts.array' => 'Receipts must be a valid list.',
            'receipts.*.file' => 'Each receipt must be a valid file.',
            'receipts.*.mimes' => 'Receipts must be JPG, PNG, or PDF files.',
            'receipts.*.max' => 'Each receipt must not exceed 2MB.',

            // Receipt upload (required case)
            'receipts.required' => 'Please upload at least one receipt.',
        ];
    }
}
