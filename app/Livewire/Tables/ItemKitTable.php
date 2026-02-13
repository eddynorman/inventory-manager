<?php

namespace App\Livewire\Tables;

use App\Models\ItemKit;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class ItemKitTable extends PowerGridComponent
{
    public string $tableName = 'item-kit-table';

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
        return ItemKit::query()->with('items');
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
            ->add('description')
            ->add('selling_price',function($kit){
                return number_format($kit->selling_price,2);
            })
            ->add('selling_price_includes_tax')
            ->add('includes_tax', function($kit){
                return $kit->selling_price_includes_tax ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>';
            })
            ->add('created_at')
            ->add('created_at_formatted', function($kit){
                return Carbon::parse($kit->created_at)->format('d/m/Y H:i');
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Description', 'description')
                ->sortable()
                ->searchable(),

            Column::make('Selling price','selling_price')
                ->sortable()
                ->searchable(),

            Column::make('Tax inclusive', 'includes_tax','selling_price_includes_tax')
                ->sortable()
                ->searchable(),

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

    public function header() :array {
        return [
            Button::add('bulk-delete')
                ->slot('<i class="fa-solid fa-trash"></i> Bulk Delete')
                ->class('btn btn-danger btn-sm')
                ->dispatch('confirmBulkDelete', ['ids' => $this->checkboxValues]),
        ];
    }

    public function actions(ItemKit $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa fa-eye"></i>')
                ->class('btn btn-info btn-sm')
                ->dispatch('view',['id'=> $row->id]),

            Button::add('edit')
                ->slot('<i class="fa-solid fa-pen-to-square"></i>')
                ->class('btn btn-primary btn-sm')
                ->dispatch('edit', ['id' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fa-solid fa-trash"></i>')
                ->class('btn btn-danger btn-sm')
                ->dispatch('confirmDelete', ['id' => $row->id]),
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
