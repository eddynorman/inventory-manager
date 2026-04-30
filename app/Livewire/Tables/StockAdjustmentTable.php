<?php

namespace App\Livewire\Tables;

use App\Models\StockAdjustment;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class StockAdjustmentTable extends PowerGridComponent
{
    public string $tableName = 'stock-adjustment-table-qovu0z-table';
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
        return StockAdjustment::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('adjustment_no',fn(StockAdjustment $model) => str_pad($model->id, 5, "0", STR_PAD_LEFT))
            ->add('user_id')
            ->add('created_by', fn (StockAdjustment $model) => $model->createdBy?->name)
            ->add('description')
            ->add('created_at')
            ->add('created_at_formatted',fn (StockAdjustment $model) => optional($model->created_at)->format('d/m/Y H:i') );
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'adjustment_no','id')
                ->sortable(),
            Column::make('Created By','created_by' ,'user_id')
                ->sortable()
                ->searchable(),
            Column::make('Description', 'description')
                ->sortable()
                ->searchable(),

            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable()
                ->searchable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    public function actions(StockAdjustment $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa fa-eye"></i>')
                ->class('btn btn-sm btn-info')
                ->dispatch('viewAdjustment', ['id' => $row->id]),
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
