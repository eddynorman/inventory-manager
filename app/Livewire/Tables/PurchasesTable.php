<?php

namespace App\Livewire\Tables;

use App\Models\Purchase;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Facades\Rule;

final class PurchasesTable extends PowerGridComponent
{
    public string $tableName = 'purchases-table-bzk4f0-table';
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
        return Purchase::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('purchase_no',fn (Purchase $model) => str_pad($model->id, 5, "0", STR_PAD_LEFT))
            ->add('requisition_id')
            ->add('req_no', fn (Purchase $model) => str_pad($model->requisition_id, 5, "0", STR_PAD_LEFT))
            ->add('purchased_by_id')
            ->add('purchased_by',fn (Purchase $model) => $model->purchaser()->get('name')[0]->name)
            ->add('total_amount')
            ->add('total', fn (Purchase $model) => number_format($model->total_amount,2))
            ->add('is_received')
            ->add('is_received_formatted', function (Purchase $model){
                if($model->is_received == true){
                    return '
                        <div>
                            <span class="badge bg-success text-white">Yes</span>
                        </div>
                    ';
                }else{
                    return '
                        <div>
                            <span class="badge bg-danger text-white">No</span>
                        </div>
                    ';
                }
            })
            ->add('created_at')
            ->add('created_at_formatted',fn (Purchase $model)=>
                optional($model->created_at)->format('d/m/Y H:i')
            );
    }

    public function columns(): array
    {
        return [
            Column::make('Purchase #', 'purchase_no','id'),
            Column::make('Requisition', 'req_no','requisition_id'),
            Column::make('Purchased by', 'purchased_by','purchased_by_id'),
            Column::make('Total amount', 'total','total_amount')
                ->sortable()
                ->searchable(),

            Column::make('Received', 'is_received_formatted','is_received')
                ->sortable()
                ->searchable(),

            Column::make('Date', 'created_at_formatted','created_at')
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



    public function actions(Purchase $row): array
    {
        return [
            Button::add('view')
                ->slot('<i class="fa fa-eye"></i>')
                ->class('btn btn-sm btn-info')
                ->dispatch('viewPurchase', ['id' => $row->id]),

            Button::add('edit')
                ->slot('<i class="fa fa-edit"></i>')
                ->class('btn btn-sm btn-primary')
                ->dispatch('editPurchase', ['id' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fa fa-trash"></i>')
                ->class('btn btn-sm btn-danger')
                ->dispatch('deletePurchase', ['id' => $row->id])
        ];
    }

    public function actionRules(): array
    {
        // assign to variables first to satisfy static analysis of fluent methods
        /** @var \PowerComponents\LivewirePowerGrid\Rules\Rule $editRule */
        $editRule = Rule::button('edit')
            ->when(fn($row) => isset($row->is_received) ? ($row->is_received == true) : false);
        $editRule->hide();

        /** @var \PowerComponents\LivewirePowerGrid\Rules\Rule $deleteRule */
        $deleteRule = Rule::button('delete')
            ->when(fn($row) => isset($row->is_received) ? ($row->is_received == true) : false);
        $deleteRule->hide();

        return [$editRule, $deleteRule];
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
