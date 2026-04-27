<?php

namespace App\Livewire\Tables;

use App\Models\Issue;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class IssueTable extends PowerGridComponent
{
    public string $tableName = 'issue-table-ffsf1p-table';
    public string $sortField = 'created_at';
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
        return Issue::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('issue_no', fn ($issue) => str_pad($issue->id, 5, "0", STR_PAD_LEFT))
            ->add('issue_date_formatted', fn (Issue $model) => Carbon::parse($model->issue_date)->format('d/m/Y'))
            ->add('from_location_id')
            ->add('from_location', fn ($issue) => $issue->fromLocation?->name)
            ->add('to_location_id')
            ->add('to_location', fn ($issue) => $issue->toLocation?->name)
            ->add('user_id')
            ->add('recorded_by_name', fn ($issue) => $issue->user?->name)
            ->add('description')
            ->add('status',function($issue){
                $next = match ($issue->status) {
                    'pending'   => 'Awaiting Processing',
                    'processed'  => 'completed',
                    'rejected'  => 'Process Terminated',
                    default     => '',
                };
                $color = match ($issue->status) {
                    'pending'   => 'warning',
                    'processed'  => 'success',
                    'rejected'  => 'danger',
                    default     => 'secondary',
                };

                return '
                    <div>
                        <span class="badge bg-'.$color.'">'.ucfirst($issue->status).'</span>
                        <small class="text-muted d-block">'.$next.'</small>
                    </div>
                ';
            })
            ->add('created_at')
            ->add('created_at_formatted',fn ($issue) => optional($issue->created_at)->format('d/m/Y H:i') );
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'issue_no', 'id'),
            Column::make('Issue date', 'issue_date_formatted', 'issue_date')
                ->sortable(),

            Column::make('Source', 'from_location','from_location_id'),
            Column::make('Destination', 'to_location','to_location_id'),
            Column::make('Recorded By', 'recorded_by_name','user_id'),
            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),
            Column::make('Status','status')
                ->sortable(),
            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Issue $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa fa-eye"></i>')
                ->class('btn btn-sm btn-info')
                ->dispatch('viewIssue', ['issueId' => $row->id]),
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
