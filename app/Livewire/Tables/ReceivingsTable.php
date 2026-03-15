<?php

namespace App\Livewire\Tables;

use App\Models\Receiving;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Rules\Rule;

final class ReceivingsTable extends PowerGridComponent
{
    public string $tableName = 'receivings-table-rcv-table';

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
        return Receiving::query()->with(['purchase','supplierOrder','receiver','location']);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('source', fn(Receiving $m) => $m->purchase_id ? 'Purchase' : 'Order')
            ->add('source_no', fn(Receiving $m) => $m->purchase_id ? str_pad($m->purchase_id,5,'0',STR_PAD_LEFT) : (isset($m->supplierOrder) ? str_pad($m->supplierOrder->id,5,'0',STR_PAD_LEFT) : ''))
            ->add('receiver', fn(Receiving $m) => optional($m->receiver)->name)
            ->add('location', fn(Receiving $m) => optional($m->location)->name)
            ->add('created_at')
            ->add('created_at_formatted', fn(Receiving $m) => optional($m->created_at)->format('d/m/Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('Receiving #','id','id'),
            Column::make('Source','source_no','purchase_id'),
            Column::make('Type','source','purchase_id'),
            Column::make('Receiver','receiver','received_by_id'),
            Column::make('Location','location','location_id'),
            Column::make('Date','created_at_formatted','created_at')->sortable()->searchable(),
            Column::action('Action')
        ];
    }

    public function actions($row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa fa-eye"></i>')
                ->class('btn btn-sm btn-info')
                ->dispatch('viewReceiving', ['id' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fa fa-trash"></i>')
                ->class('btn btn-sm btn-danger')
                ->dispatch('deleteReceiving', ['id' => $row->id]),
        ];
    }

    public function actionRules(): array
    {
        // hide delete for non-admins
        /** @var \PowerComponents\LivewirePowerGrid\Rules\Rule $deleteRule */
        $deleteRule = Rule::button('delete')
            ->when(fn($row) => !auth()->user()->hasAnyRole(['super','admin']));
        $deleteRule->hide();

        return [$deleteRule];
    }
}
