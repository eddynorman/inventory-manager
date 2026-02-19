<?php

namespace App\Livewire\Tables;

use App\Models\Requisition;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class RequisitionsTable extends PowerGridComponent
{
    public string $tableName = 'requisitions-table-tg7rxg-table';

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
        return Requisition::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('requested_by_id')
            ->add('approved_by_id')
            ->add('reviewed_by')
            ->add('funded_by')
            ->add('rejected_by')
            ->add('cost')
            ->add('status')
            ->add('description')
            ->add('date_requested_formatted', fn (Requisition $model) => Carbon::parse($model->date_requested)->format('d/m/Y'))
            ->add('date_approved_formatted', fn (Requisition $model) => Carbon::parse($model->date_approved)->format('d/m/Y'))
            ->add('funded_on_formatted', fn (Requisition $model) => Carbon::parse($model->funded_on)->format('d/m/Y'))
            ->add('fund_amount')
            ->add('rejected_at')
            ->add('rejection_reason')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Requested by id', 'requested_by_id'),
            Column::make('Approved by id', 'approved_by_id'),
            Column::make('Reviewed by', 'reviewed_by'),
            Column::make('Funded by', 'funded_by'),
            Column::make('Rejected by', 'rejected_by'),
            Column::make('Cost', 'cost')
                ->sortable()
                ->searchable(),

            Column::make('Status', 'status')
                ->sortable()
                ->searchable(),

            Column::make('Description', 'description')
                ->sortable()
                ->searchable(),

            Column::make('Date requested', 'date_requested_formatted', 'date_requested')
                ->sortable(),

            Column::make('Date approved', 'date_approved_formatted', 'date_approved')
                ->sortable(),

            Column::make('Funded on', 'funded_on_formatted', 'funded_on')
                ->sortable(),

            Column::make('Fund amount', 'fund_amount')
                ->sortable()
                ->searchable(),

            Column::make('Rejected at', 'rejected_at_formatted', 'rejected_at')
                ->sortable(),

            Column::make('Rejected at', 'rejected_at')
                ->sortable()
                ->searchable(),

            Column::make('Rejection reason', 'rejection_reason')
                ->sortable()
                ->searchable(),

            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make('Created at', 'created_at')
                ->sortable()
                ->searchable(),

            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
            Filter::datepicker('date_requested'),
            Filter::datepicker('date_approved'),
            Filter::datepicker('funded_on'),
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
    }

    public function actions(Requisition $row): array
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
