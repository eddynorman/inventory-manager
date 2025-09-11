<?php

namespace App\Livewire\Tables;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class LocationTable extends PowerGridComponent
{
    public string $tableName = 'location-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Location::query()->with('user');
    }

    public function relationSearch(): array
    {
        return [
            'staff' => ['name', 'email'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name')
            ->add('location_type')
            ->add('address')
            ->add('phone')
            ->add('email')
            ->add('staff_responsible', fn(Location $location) => optional($location->user)->name)
            ->add('description');
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Type', 'location_type')
                ->sortable()
                ->searchable(),

            Column::make('Address', 'address')
                ->sortable()
                ->searchable(),

            Column::make('Phone', 'phone')
                ->sortable()
                ->searchable(),

            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),

            Column::make('Staff', 'staff_responsible')
                ->sortable()
                ->searchable(),

            Column::action('Actions'),
        ];
    }

    public function actions(Location $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fas fa-eye"></i>')
                ->class('btn btn-sm btn-info mr-1')
                ->dispatch('view', ['id' => $row->id]),

            Button::add('edit')
                ->slot('<i class="fas fa-edit"></i>')
                ->class('btn btn-sm btn-primary mr-1')
                ->dispatch('edit', ['id' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fas fa-trash"></i>')
                ->class('btn btn-sm btn-danger')
                ->dispatch('confirmDelete', ['id' => $row->id]),
        ];
    }
}
