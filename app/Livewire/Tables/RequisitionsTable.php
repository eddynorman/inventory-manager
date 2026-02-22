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
    public string $sortField = 'date_requested';
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
        return Requisition::query()
            ->with(['requestedBy', 'department'])
            ;
    }

    public function relationSearch(): array
    {
        return [
            'requestedBy' => ['name'],
            'department'  => ['name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('req_no', fn (Requisition $model) => str_pad($model->id, 5, "0", STR_PAD_LEFT))
            ->add('cost', fn (Requisition $model) =>
                number_format($model->cost, 2)
            )

            ->add('status', fn (Requisition $model) =>
                $this->statusWithNextStep($model->status)
            )
            ->add('fund_amount', fn (Requisition $model) =>
                number_format($model->fund_amount, 2)
            )

            ->add('requested_by', fn (Requisition $model) => $model->requestedBy?->name)
            ->add('department', fn (Requisition $model) => $model->department?->name)

            ->add('date_requested_formatted', fn (Requisition $model) =>
                optional($model->date_requested)->format('d/m/Y H:i')
    );
    }

    public function columns(): array
    {
        return [
            Column::make('Req No', 'req_no', 'id')
            ->sortable()
            ->searchable(),

            Column::make('Department', 'department')
                ->sortable()
                ->searchable(),

            Column::make('Requested By', 'requested_by')
                ->sortable()
                ->searchable(),

            Column::make('Cost', 'cost')
                ->sortable()
                ->searchable(),

            Column::make('Fund Amount', 'fund_amount')
                ->sortable()
                ->searchable(),

            Column::make('Status', 'status')
                ->sortable()
                ->searchable(),

            Column::make('Date Requested', 'date_requested_formatted', 'date_requested')
                ->sortable(),

            Column::action('Actions'),
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    public function actions(Requisition $row): array
    {
        return [

            Button::add('view')
                ->slot('<i class="fa fa-eye"></i>')
                ->class('btn btn-sm btn-info')
                ->dispatch('view', ['id' => $row->id]),

            Button::add('edit')
                ->slot('<i class="fa fa-edit"></i>')
                ->class('btn btn-sm btn-primary')
                ->dispatch('edit', ['id' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fa fa-trash"></i>')
                ->class('btn btn-sm btn-danger')
                ->dispatch('delete', ['id' => $row->id]),
        ];
    }

   private function statusWithNextStep(string $status): string
    {
        $next = match ($status) {
            'pending'   => 'Awaiting Review',
            'reviewed'  => 'Awaiting Approval',
            'approved'  => 'Awaiting Funding',
            'funded'    => 'Completed',
            'rejected'  => 'Process Terminated',
            default     => '',
        };

        $color = match ($status) {
            'pending'   => 'warning',
            'reviewed'  => 'info',
            'approved'  => 'primary',
            'funded'    => 'success',
            'rejected'  => 'danger',
            default     => 'secondary',
        };

        return '
            <div>
                <span class="badge bg-'.$color.'">'.ucfirst($status).'</span>
                <small class="text-muted d-block">'.$next.'</small>
            </div>
        ';
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
