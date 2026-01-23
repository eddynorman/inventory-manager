<?php

namespace App\Livewire\Tables;

use App\Models\Department;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class DepartmentTable extends PowerGridComponent
{
    public string $tableName = 'department-table';

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
        return Department::query();
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
                ->dispatch('bulkDelete.Department', ['ids' => $this->checkboxValues]),
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name')
            ->add('description')
            ->add('created_at_formatted', function ($cat) {
                return Carbon::parse($cat->created_at)->format('d/m/Y H:i');
            })
            ->add('created_at');
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

    #[\Livewire\Attributes\On('refresh-table')]
    public function refreshTable(): void
    {
        $this->refresh();
    }

    public function actions(Department $row): array
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
