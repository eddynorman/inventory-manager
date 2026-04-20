<?php

namespace App\Livewire\Tables;

use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Facades\Rule;

final class PendingSalesTable extends PowerGridComponent
{
    public string $tableName = 'pending-sales-table-xkotlo-table';

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
        return Sale::query()->where('status','pending');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('sale_no', fn($sale) => str_pad($sale->id, 5, '0', STR_PAD_LEFT))
            ->add('total_amount')
            ->add('balance')
            ->add('payment_status')
            ->add('status')
            ->add('created_at')
            ->add('created_at_formatted',fn ($sale) => optional($sale->created_at)->format('d/m/Y H:i') )
            ->add('created_by_name', fn($sale) => $sale->createdBy?->name ?? '-')

            ->add('served_by_names', function ($sale) {
                return $sale->servedBy->pluck('name')->implode(', ') ?: '-';
            })
            ->add('total_amount_formatted', fn($sale) => number_format($sale->total_amount, 2))
            ->add('balance_formatted', fn($sale) => number_format($sale->balance, 2));
    }

    public function columns(): array
    {
        return [
            Column::make('Sale #', 'sale_no','id')
                ->sortable()
                ->searchable(),

            Column::make('Created By', 'created_by_name','created_by')
                ->searchable(),

            Column::make('Created At', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make('Served By', 'served_by_names')
                ->sortable(),

            Column::make('Total', 'total_amount_formatted', 'total_amount')
                ->sortable(),

            Column::make('Balance', 'balance_formatted', 'balance')
                ->sortable(),

            Column::action('Actions'),
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    public function actions(Sale $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa fa-eye"></i>')
                ->class('btn btn-sm btn-info')
                ->dispatch('viewSale', ['saleId' => $row->id]),

            Button::add('edit')
                ->slot('<i class="fa fa-pen"></i>')
                ->class('btn btn-sm btn-primary')
                ->dispatch('editSale', ['saleId' => $row->id]),

            Button::add('add-payment')
                ->slot('<i class="fa fa-money-bill"></i>')
                ->class('btn btn-sm btn-success')
                ->dispatch('addPayment', ['saleId' => $row->id]),

            Button::add('print')
                ->slot('<i class="fa fa-print"></i>')
                ->class('btn btn-sm btn-secondary')
                ->dispatch('printSale', ['id' => $row->id]),
        ];
    }

    public function actionRules($row): array
    {
        return [
            Rule::button('edit')
                ->when(fn($row) =>
                    !($row->status === 'pending' && $row->created_at->isToday())
                )
                ->hide(),

            Rule::button('add-payment')
                ->when(fn($row) => $row->status !== 'pending' && $row->balance <= 0)
                ->hide(),
        ];
    }
}
