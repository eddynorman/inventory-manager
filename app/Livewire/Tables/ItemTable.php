<?php

namespace App\Livewire\Tables;

use App\Models\Item;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class ItemTable extends PowerGridComponent
{
    public string $tableName = 'item-table-effbnx-table';

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
        return Item::with('category', 'supplier')->where('is_active', true);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name')
            ->add('category_id')
            ->add('category',function($item){
                return $item->category->name ?? '-';
            })
            ->add('supplier_id')
            ->add('supplier',function($item){
                return $item->supplier->name ?? '-';
            })
            ->add('initial_stock')
            ->add('current_stock')
            ->add('reorder_level')
            ->add('is_sale_item');
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Category', 'category')
                ->sortable()
                ->searchable(),
            Column::make('Supplier', 'supplier')
                ->sortable()
                ->searchable(),

            Column::make('Initial stock', 'initial_stock')
                ->sortable()
                ->searchable(),

            Column::make('Current stock', 'current_stock')
                ->sortable()
                ->searchable(),

            Column::make('Reorder level', 'reorder_level')
                ->sortable()
                ->searchable(),

            Column::make('Is sale item', 'is_sale_item')
                ->sortable()
                ->searchable(),

            Column::action('Action')
        ];
    }

    public function header(): array
    {
        return [
            Button::add('bulk-assign-category')
                ->slot('<i class="fa-solid fa-folder"></i> Bulk Assign Category')
                ->class('btn btn-primary btn-sm')
                ->dispatch('bulkAssignCategory.' . $this->tableName, ['table' => $this->tableName]),
            Button::add('bulk-assign-supplier')
                ->slot('<i class="fa-solid fa-folder"></i> Bulk Assign Supplier')
                ->class('btn btn-primary btn-sm')
                ->dispatch('bulkAssignSupplier.' . $this->tableName, ['table' => $this->tableName]),
            Button::add('bulk-toggle-active')
                ->slot('<i class="fa-solid fa-toggle-on"></i> Bulk Toggle Active')
                ->class('btn btn-primary btn-sm')
                ->dispatch('bulkToggleActive.' . $this->tableName, ['table' => $this->tableName]),
            Button::add('bulk-delete')
                ->slot('<i class="fa-solid fa-trash"></i> Bulk Delete')
                ->class('btn btn-danger btn-sm')
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

    public function actions(Item $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit: '.$row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('edit', ['rowId' => $row->id])
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
