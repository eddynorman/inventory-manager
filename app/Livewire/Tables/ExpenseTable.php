<?php

namespace App\Livewire\Tables;

use App\Models\Expense;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class ExpenseTable extends PowerGridComponent
{
    public string $tableName = 'expense-table-tl72v5-table';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Expense::query()->with(['category','department','recordedBy']);
    }

    public function relationSearch(): array
    {
        return [
            'recordedBy' => ['name'],
            'department'  => ['name'],
            'category' => ['name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('exp_no', fn ($expense) => str_pad($expense->id, 5, "0", STR_PAD_LEFT))
            ->add('department_id')
            ->add('department', fn ($expense) => $expense->department?->name)
            ->add('expense_category_id')
            ->add('category', fn ($expense) => $expense->category?->name)
            ->add('amount')
            ->add('amount_formatted', fn($expense) => number_format($expense->amount, 2))
            ->add('recorded_by')
            ->add('recorded_by_name', fn ($expense) => $expense->recordedBy?->name)
            ->add('created_at')
            ->add('created_at_formatted',fn ($expense) => optional($expense->created_at)->format('d/m/Y H:i') );
    }

    public function columns(): array
    {
        return [
            Column::make('Exp#','exp_no' ,'id'),
            Column::make('Department','department' ,'department_id'),
            Column::make('Category','category' ,'expense_category_id'),
            Column::make('Amount', 'amount_formatted','amount')
                ->sortable()
                ->searchable(),

            Column::make('Recorded by', 'recorded_by_name','recorded_by'),
            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    public function actions(Expense $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa fa-eye"></i>')
                ->class('btn btn-sm btn-info')
                ->dispatch('viewExpense', ['id' => $row->id]),

            Button::add('add-receipt')
                ->slot('<i class="fa fa-plus"></i>')
                ->class('btn btn-sm btn-primary')
                ->dispatch('addReceipt', ['expenseId' => $row->id]),
        ];
    }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
