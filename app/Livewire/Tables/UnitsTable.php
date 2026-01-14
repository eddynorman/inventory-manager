<?php

namespace App\Livewire\Tables;

use App\Models\Unit;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class UnitsTable extends PowerGridComponent
{
    public string $tableName = 'units-table-p5xboo-table';

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
        return Unit::where('is_active', true);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name')
            ->add('item_id')
            ->add('item',function($unit){
                return $unit->item->name ?? '-';
            })
            ->add('buying_price', function ($unit) {
                return number_format($unit->buying_price, 2);
            })
            ->add('selling_price', function ($unit) {
                return number_format($unit->selling_price, 2);
            })
            ->add('is_smallest_unit')
            ->add('smallest_unit', function ($unit) {
                return $unit->is_smallest_unit ? '<b>Yes</b>' : 'No';
            })
            ->add('smallest_units_number')
            ->add('buying_price_includes_tax')
            ->add('selling_price_includes_tax');
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),
            Column::make('Item', 'item','item_id')
                ->sortable()
                ->searchable(),

            Column::make('Buying price', 'buying_price')
                ->sortable()
                ->searchable(),

            Column::make('Selling price', 'selling_price')
                ->sortable()
                ->searchable(),

            Column::make('Is smallest unit', 'smallest_unit', 'is_smallest_unit')
                ->sortable()
                ->searchable(),

            Column::make('Smallest units number', 'smallest_units_number')
                ->sortable()
                ->searchable(),

            Column::action('Action')
        ];
    }
    public function header(): array
    {
        return [
            Button::add('bulk-delete')
                ->class('btn btn-danger')
                ->slot('Bulk Delete')
                ->dispatch('bulkDelete.' . $this->tableName, ['table' => $this->tableName]),
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
    }

    public function actions(Unit $row): array
    {
        return [
            Button::add('edit')
                ->slot('<i class="fa-solid fa-pen-to-square"></i>')
                ->class('btn btn-sm btn-primary text-white me-1')
                ->dispatch('edit-unit', ['id' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fa-solid fa-trash"></i>')
                ->class('btn btn-sm btn-danger me-1')
                ->dispatch('delete-unit', ['id' => $row->id]),
        ];
    }

    public function actionRules($row): array
    {
       return [
            // Hide button delete for smallest units
            Rule::button('delete')
                ->when(fn($row) => $row->is_smallest_unit === 1)
                ->hide(),
        ];
    }
}
