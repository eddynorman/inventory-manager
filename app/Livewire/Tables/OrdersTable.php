<?php

namespace App\Livewire\Tables;

use App\Models\SupplierOrder;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class OrdersTable extends PowerGridComponent
{
    public string $tableName = 'orders-table-t2dk1o-table';
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
        return SupplierOrder::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('order_no',fn (SupplierOrder $model) => str_pad($model->id, 5, "0", STR_PAD_LEFT))
            ->add('requisition_id')
            ->add('req_no', fn (SupplierOrder $model) => str_pad($model->requisition_id, 5, "0", STR_PAD_LEFT))
            ->add('supplier_id')
            ->add('supplier', fn (SupplierOrder $model) => $model->supplier()->get('name')[0]->name)
            ->add('total_amount')
            ->add('total', fn (SupplierOrder $model) => number_format($model->total_amount,2))
            ->add('amount_paid')
            ->add('paid', fn (SupplierOrder $model) => number_format($model->amount_paid,2))
            ->add('amount_pending')
            ->add('pending', fn (SupplierOrder $model) => number_format($model->amount_pending,2))
            ->add('created_by')
            ->add('created_by_name',fn (SupplierOrder $model) => $model->createdBy()->get('name')[0]->name)
            ->add('is_received')
            ->add('is_received_formatted', function (SupplierOrder $model){
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
            ->add('created_at_formatted',fn (SupplierOrder $model)=>
                optional($model->created_at)->format('d/m/Y H:i')
            );
    }

    public function columns(): array
    {
        return [
            Column::make('Order #','order_no' ,'id'),
            Column::make('Requisition', 'req_no','requisition_id'),
            Column::make('Supplier', 'supplier' ,'supplier_id'),
            Column::make('Total amount', 'total' ,'total_amount')
                ->sortable()
                ->searchable(),

            Column::make('Amount paid', 'paid','amount_paid')
                ->sortable()
                ->searchable(),

            Column::make('Amount pending', 'pending','amount_pending')
                ->sortable()
                ->searchable(),

            Column::make('Ordered By','created_by_name' ,'created_by'),
            Column::make('Received','is_received_formatted' ,'is_received')
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

    public function actions(SupplierOrder $row): array
    {
        return [
            Button::add('pay')
                ->slot('<i class="fa fa-coins"></i>')
                ->class('btn btn-sm btn-success')
                ->dispatch('addPayment', ['orderId' => $row->id]),

            Button::add('view')
                ->slot('<i class="fa fa-eye"></i>')
                ->class('btn btn-sm btn-info')
                ->dispatch('viewOrder', ['id' => $row->id]),

            Button::add('edit')
                ->slot('<i class="fa fa-edit"></i>')
                ->class('btn btn-sm btn-primary')
                ->dispatch('editOrder', ['id' => $row->id]),

            Button::add('delete')
                ->slot('<i class="fa fa-trash"></i>')
                ->class('btn btn-sm btn-danger')
                ->dispatch('deleteOrder', ['id' => $row->id])
        ];
    }

    public function actionRules(): array
    {
        // create Rule instances first to help static analyzers recognize methods like hide()
        /** @var \PowerComponents\LivewirePowerGrid\Rules\Rule $payRule */
        $payRule = Rule::button('pay')
            ->when(fn($row) => isset($row->amount_pending) ? ($row->amount_pending <= 0) : false);
        $payRule->hide();

        /** @var \PowerComponents\LivewirePowerGrid\Rules\Rule $editRule */
        $editRule = Rule::button('edit')
            ->when(fn($row) => isset($row->is_received) ? ($row->is_received == true) : false);
        $editRule->hide();

        /** @var \PowerComponents\LivewirePowerGrid\Rules\Rule $deleteRule */
        $deleteRule = Rule::button('delete')
            ->when(fn($row) => isset($row->is_received) ? ($row->is_received == true) : false);
        $deleteRule->hide();

        return [
            $payRule,
            $editRule,
            $deleteRule,
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
