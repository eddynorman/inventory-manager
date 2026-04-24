<?php

namespace App\Livewire\Tables;

use App\Models\Banking;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class BankingTable extends PowerGridComponent
{
    public string $tableName = 'banking-table-iq3ldr-table';
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
        return Banking::query()
            ->with('recordedBy','account')
            ->join('bank_accounts', 'bankings.bank_account', '=', 'bank_accounts.id')
            ->leftJoin('users', 'bankings.recorded_by', '=', 'users.id')
            ->select([
                'bankings.*',
                'bank_accounts.bank_name as bank_name',
                'users.name as recorded_by_name',
            ]);

    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('txn',fn($txn) => str_pad($txn->id,5,'0',STR_PAD_LEFT))
            ->add('bank_account')
            ->add('bank_name')
            ->add('amount')
            ->add('amount_formatted',fn($txn) => number_format($txn->amount,2))
            ->add('type')
            ->add('type_formatted',function($txn){
                return match ($txn->type) {
                        'deposit' => '<span class="badge bg-success text-white">Deposit</span>',
                        'withdraw' => '<span class="badge bg-danger">Withdrawal</span>',
                        default => '<span class="badge bg-secondary">Error</span>',
                };
            })
            ->add('receipt_path')
            ->add('description')
            ->add('recorded_by')
            ->add('recorded_by_name', fn($txn) => $txn->recordedBy?->name ?? '-')
            ->add('created_at')
            ->add('created_at_formatted',fn ($sale) => optional($sale->created_at)->format('d/m/Y H:i') );
    }

    public function columns(): array
    {
        return [
            // Column::make('Txn','txn', 'id')
            //     ->sortable()
            //     ->searchable(),
            Column::make('Date', 'created_at_formatted', 'created_at')
                ->sortable()
                ->searchable(),
            Column::make('Bank account', 'bank_name','bank_accounts.bank_name')
                ->sortable()
                ->searchable(),
            Column::make('Amount', 'amount')
                ->sortable()
                ->searchable(),

            Column::make('Type', 'type_formatted','type')
                ->sortable()
                ->searchable(),

            // Column::make('Description', 'description')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('Recorded by', 'recorded_by_name','recorded_by'),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    public function actions(Banking $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa fa-eye"></i>')
                ->class('btn btn-sm btn-info')
                ->dispatch('viewTxn', ['txnId' => $row->id]),
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
