<?php

namespace App\Livewire\Tables;

use App\Models\Category;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class CategoryTable extends PowerGridComponent
{
    public string $tableName = 'category-table-1uitsn-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [

            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()->showPerPage()->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Category::query();
    }

    public function relationSearch(): array
    {
        return [];
    }
     public function header(): array
    {
        return [
            Button::add('bulk-delete')
                ->slot('<i class="fa-solid fa-trash"></i> Bulk Delete')
                ->class('btn btn-danger btn-sm')
                ->dispatch('bulkDelete.Categories', ['ids' => $this->checkboxValues]),
        ];
    }
    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name')
            ->add('description')
            ->add('department_id')
            ->add('department', function($cat){
                return e(strtoupper($cat->department->name));
            })
            ->add('created_at_formatted', function ($cat) {
                return Carbon::parse($cat->created_at)->format('d/m/Y H:i');
            })
            ->add('created_at');
        // ❌ no ->add('actions') here
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

            Column::make('Department', 'department','department_id')
                ->sortable()
                ->searchable(),

            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable()
                ->searchable(),

            Column::action('Actions') // ✅ required for actions()
        ];
    }

    #[\Livewire\Attributes\On('refresh-table')]
    public function refreshTable(): void
    {
        $this->refresh();
    }

    public function actions($row): array
    {
        return [

            Button::add('view')
                ->slot('<i class="fa-solid fa-eye"></i>')
                ->class('btn btn-sm btn-info text-white me-1')
                ->dispatch('view', ['id' => $row->id]),

            Button::add('edit')
                ->slot('<i class="fa-solid fa-pen-to-square"></i>')
                ->class('btn btn-sm btn-primary text-white me-1')
                ->dispatch('edit', ['id' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fa-solid fa-trash"></i>')
                ->class('btn btn-sm btn-danger me-1')
                ->dispatch('confirmDelete', ['id' => $row->id]),
        ];
    }

    public function actionRules($row): array
    {
        return [
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
}
