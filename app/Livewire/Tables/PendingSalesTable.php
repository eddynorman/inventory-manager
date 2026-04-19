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

final class PendingSalesTable extends PowerGridComponent
{
    public string $tableName = 'pending-sales-table-xkotlo-table';

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
            ->add('created_by_name', fn($sale) => $sale->createdBy?->name ?? '-')

            ->add('served_by_names', function ($sale) {
                return $sale->servedBy->pluck('name')->implode(', ') ?: '-';
            })

            ->add('status_badge', function ($sale) {
                return match ($sale->status) {
                    'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
                    'completed' => '<span class="badge bg-success">Completed</span>',
                    default => '<span class="badge bg-secondary">'.$sale->status.'</span>',
                };
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

            Column::make('Created By', 'created_by_name')
                ->searchable(),

            Column::make('Served By', 'served_by_names')
                ->sortable(),

            Column::make('Status', 'status_badge')
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
                ->dispatch('viewSale', ['id' => $row->id]),

            Button::add('edit')
                ->slot('<i class="fa fa-pen"></i>')
                ->class('btn btn-sm btn-primary')
                ->dispatch('editSale', ['id' => $row->id]),

            Button::add('print')
                ->slot('<i class="fa fa-print"></i>')
                ->class('btn btn-sm btn-secondary')
                ->dispatch('printSale', ['id' => $row->id]),
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
