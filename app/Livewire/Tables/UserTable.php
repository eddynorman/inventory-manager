<?php

namespace App\Livewire\Tables;

use App\Models\User;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class UserTable extends PowerGridComponent
{
    public string $tableName = 'users-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()->showPerPage()->showRecordCount(),
        ];
    }

    public function datasource()
    {
        return User::query()->where('role', '!=', 'super');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('email')
            ->add('type')
            ->add('role')
            ->add('is_active')
            ->add('is_active_formatted',function($user){
                return $user->is_active ? 'Yes' : 'No';
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name')->sortable()->searchable(),
            Column::make('Email', 'email')->sortable()->searchable(),
            Column::make('Type', 'type')->sortable()->searchable(),
            Column::make('Role', 'role')->sortable()->searchable(),
            Column::make('Active', 'is_active_formatted', 'is_active'),
            Column::make('Created', 'created_at')->sortable(),
            Column::action('Actions')
        ];
    }

    public function actions(User $row): array
    {
        return [
            Button::add('edit')
                ->slot('<i class="fa fa-edit"></i>')
                ->class('btn btn-primary')
                ->dispatch('edit', ['id' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fa fa-trash"></i>')
                ->class('btn btn-danger')
                ->dispatch('confirmDelete', ['id' => $row->id]),
        ];
    }
}
