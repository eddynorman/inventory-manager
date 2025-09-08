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

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name')
            ->add('description')
            ->add('created_at_formatted', function ($cat) {
                return Carbon::parse($cat->created_at)->format('d/m/Y H:i');
            });
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

            Column::make('Created at', 'created_at_formatted')
                ->sortable(),

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
            Button::add('edit')
                ->slot('Edit')
                ->class('btn btn-sm btn-primary')
                ->dispatch('edit', ['id' => $row->id]),

            Button::add('view')
                ->slot('View')
                ->class('btn btn-sm btn-info')
                ->dispatch('view', ['id' => $row->id]),

            Button::add('delete')
                ->slot('Delete')
                ->class('btn btn-sm btn-danger')
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
