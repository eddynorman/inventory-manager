<?php

namespace App\Livewire\Tables;

use App\Models\AssetInventoryItems;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class AssetTable extends PowerGridComponent
{
    public string $tableName = 'asset-table-z40ldt-table';

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
        return AssetInventoryItems::query()
            ->join('departments', 'asset_inventory_items.department_id', '=', 'departments.id')
            ->select([
                'asset_inventory_items.*',
                'departments.name as department_name'
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
            ->add('name')
            ->add('department_id')
            ->add('initial_quantity')
            ->add('initial_purchase_date_formatted', fn (AssetInventoryItems $model) => Carbon::parse($model->initial_purchase_date)->format('d/m/Y'))
            ->add('initial_unit_cost')
            ->add('average_unit_cost')
            ->add('current_quantity')
            ->add('created_at')
            ->add('total', fn (AssetInventoryItems $model) => number_format($model->average_unit_cost * $model->current_quantity));
    }

    public function columns(): array
    {
        return [
            //Column::make('Id', 'id'),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Department', 'department_name')
                ->sortable()
                ->searchable(),

            Column::make('Initial quantity', 'initial_quantity')
                ->sortable()
                ->searchable(),

            // Column::make('Initial purchase date', 'initial_purchase_date_formatted', 'initial_purchase_date')
            //     ->sortable(),

            // Column::make('Initial unit cost', 'initial_unit_cost')
            //     ->sortable()
            //     ->searchable(),

            Column::make('Average unit cost', 'average_unit_cost')
                ->sortable()
                ->searchable(),

            Column::make('Current quantity', 'current_quantity')
                ->sortable()
                ->searchable(),

            Column::make('Total Value','total'),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }


    public function actions(AssetInventoryItems $row): array
    {
        return [
            Button::add('damage')
                ->slot('<i class="fa fa-trash"></i>')
                ->class('btn btn-sm btn-warning')
                ->dispatch('openDamageModal', ['id' => $row->id]),

            Button::add('view')
                ->slot('<i class="fa fa-eye"></i>')
                ->class('btn btn-sm btn-info')
                ->dispatch('viewItem', ['id' => $row->id]),
        ];
    }

}
