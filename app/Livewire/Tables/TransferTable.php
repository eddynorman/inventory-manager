<?php

namespace App\Livewire\Tables;

use App\Models\Transfer;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class TransferTable extends PowerGridComponent
{
    public string $tableName = 'transfer-table-olw5tk-table';
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
        return Transfer::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('transfer_no', fn ($transfer) => str_pad($transfer->id, 5, "0", STR_PAD_LEFT))
            ->add('from_location_id')
            ->add('from_location', fn ($transfer) => $transfer->fromLocation?->name)
            ->add('to_location_id')
            ->add('to_location', fn ($transfer) => $transfer->toLocation?->name)
            ->add('user_id')
            ->add('recorded_by_name', fn ($transfer) => $transfer->user?->name)
            ->add('description')
            ->add('status',function($transfer){
                $next = match ($transfer->status) {
                    'pending'   => 'Awaiting Receiving',
                    'received'  => 'completed',
                    default     => '',
                };
                $color = match ($transfer->status) {
                    'pending'   => 'warning',
                    'received'  => 'success',
                    default     => 'secondary',
                };

                return '
                    <div>
                        <span class="badge bg-'.$color.'">'.ucfirst($transfer->status).'</span>
                        <small class="text-muted d-block">'.$next.'</small>
                    </div>
                ';
            })
            ->add('created_at')
            ->add('created_at_formatted',fn ($transfer) => optional($transfer->created_at)->format('d/m/Y H:i') );
    }

    public function columns(): array
    {
        return [
            Column::make('#', 'transfer_no','id'),
            Column::make('Source', 'from_location', 'from_location_id'),
            Column::make('Destination', 'to_location','to_location_id'),
            Column::make('Recorded', 'recorded_by_name','user_id'),
            Column::make('Description', 'description')
                ->sortable()
                ->searchable(),

            Column::make('Status' ,'status')
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


    public function actions(Transfer $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa fa-eye"></i>')
                ->class('btn btn-sm btn-info')
                ->dispatch('viewTransfer', ['transferId' => $row->id]),
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
