<?php

namespace App\Services\Reports;

use App\Models\Sale;
use Carbon\Carbon;
use App\Models\SaleItem;
use App\Models\SaleItemKit;
use App\Models\SaleItemKitItem;
use App\Models\SalePayment;
use App\Models\UsedItems;
use Faker\Provider\Payment;

class SalesReportService
{
    public function soldItemsReport(array $filters = [])
    {
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        return SaleItem::query()

            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')

            ->join('items', 'sale_items.item_id', '=', 'items.id')

            ->join('categories', 'items.category_id', '=', 'categories.id')

            ->join('departments', 'categories.department_id', '=', 'departments.id')

            ->whereBetween('sales.created_at', [$from, $to])

            ->selectRaw('
                departments.id as department_id,
                departments.name as department_name,

                items.id as item_id,
                items.name as item_name,

                SUM(sale_items.quantity) as total_quantity,

                SUM(
                    sale_items.quantity * sale_items.unit_price
                ) as total_sales,

                SUM(
                    sale_items.cost_at_sale
                ) as total_cost,

                SUM(
                    (
                        sale_items.unit_price
                        *
                        sale_items.quantity
                    )
                    -
                    sale_items.cost_at_sale
                ) as total_profit
            ')

            ->groupBy(
                'departments.id',
                'departments.name',
                'items.id',
                'items.name'
            )

            ->orderBy('departments.name')

            ->get();
    }

    public function soldKitsReport(array $filters = [])
    {
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        return SaleItemKit::query()

            ->join('sales', 'sale_item_kits.sale_id', '=', 'sales.id')

            ->join('item_kits', 'sale_item_kits.item_kit_id', '=', 'item_kits.id')

            ->join('categories', 'item_kits.category_id', '=', 'categories.id')

            ->join('departments', 'categories.department_id', '=', 'departments.id')

            ->whereBetween('sales.created_at', [$from, $to])

            ->selectRaw('
                departments.id as department_id,
                departments.name as department_name,

                item_kits.id as kit_id,
                item_kits.name as kit_name,

                SUM(sale_item_kits.quantity) as total_quantity,

                SUM(
                    sale_item_kits.quantity * sale_item_kits.selling_price
                ) as total_sales,

                SUM(
                    sale_item_kits.cost_at_sale
                ) as total_cost,

                SUM(
                    (
                        sale_item_kits.selling_price
                        *
                        sale_item_kits.quantity
                    )
                    -
                    sale_item_kits.cost_at_sale
                ) as total_profit
            ')

            ->groupBy(
                'departments.id',
                'departments.name',
                'item_kits.id',
                'item_kits.name'
            )

            ->orderBy('departments.name')

            ->get();
    }

    public function soldItemsSummary(array $filters = [])
    {
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        $items = SaleItem::query()

            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')

            ->whereBetween('sales.created_at', [$from, $to])

            ->selectRaw('
                SUM(quantity) as qty,

                SUM(
                    quantity * unit_price
                ) as sales,

                SUM(
                    cost_at_sale
                ) as cost,

                SUM(
                    (
                        unit_price * quantity
                    )
                    -
                    cost_at_sale
                ) as profit
            ')

        ->first();

        return (object)[

            'qty' => ($items->qty ?? 0),

            'sales' => ($items->sales ?? 0),

            'cost' => ($items->cost ?? 0),

            'profit' => ($items->profit ?? 0),
        ];
    }

    public function soldKitsSummary(array $filters = []){
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        $kits = SaleItemKit::query()

            ->join('sales', 'sale_item_kits.sale_id', '=', 'sales.id')

            ->whereBetween('sales.created_at', [$from, $to])

            ->selectRaw('
                SUM(quantity) as qty,

                SUM(
                    quantity * selling_price
                ) as sales,

                SUM(
                    cost_at_sale
                ) as cost,

                SUM(
                    (
                        selling_price * quantity
                    )
                    -
                    cost_at_sale
                ) as profit
            ')

        ->first();

        return (object)[

            'qty' => ($kits->qty ?? 0),

            'sales' => ($kits->sales ?? 0),

            'cost' => ($kits->cost ?? 0),

            'profit' => ($kits->profit ?? 0),
        ];

    }

    public function usedItemsReport(array $filters = [])
    {
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        /*
        |--------------------------------------------------------------------------
        | KIT ITEM CONSUMPTION
        |--------------------------------------------------------------------------
        */

        $kitUsage = SaleItemKitItem::query()

            ->join(
                'sale_item_kits',
                'sale_item_kit_items.sale_item_kit_id',
                '=',
                'sale_item_kits.id'
            )

            ->join(
                'sales',
                'sale_item_kits.sale_id',
                '=',
                'sales.id'
            )

            ->join(
                'items',
                'sale_item_kit_items.item_id',
                '=',
                'items.id'
            )

            ->join(
                'categories',
                'items.category_id',
                '=',
                'categories.id'
            )

            ->join(
                'departments',
                'categories.department_id',
                '=',
                'departments.id'
            )

            ->whereBetween(
                'sales.created_at',
                [$from, $to]
            )

            ->selectRaw('
                departments.id as department_id,
                departments.name as department_name,

                items.id as item_id,
                items.name as item_name,

                SUM(
                    sale_item_kit_items.quantity
                ) as total_quantity,

                SUM(
                    sale_item_kit_items.cost_at_sale
                ) as total_cost,

                "Kit Consumption" as source
            ')

            ->groupBy(
                'departments.id',
                'departments.name',
                'items.id',
                'items.name'
            )

            ->get();

        /*
        |--------------------------------------------------------------------------
        | OPERATIONAL / MANUAL USED ITEMS
        |--------------------------------------------------------------------------
        */

        $manualUsage = UsedItems::query()

            ->join(
                'closing_stock_sessions',
                'used_items.closing_stock_session_id',
                '=',
                'closing_stock_sessions.id'
            )

            ->join(
                'items',
                'used_items.item_id',
                '=',
                'items.id'
            )

            ->join(
                'categories',
                'items.category_id',
                '=',
                'categories.id'
            )

            ->join(
                'departments',
                'categories.department_id',
                '=',
                'departments.id'
            )

            ->whereBetween(
                'closing_stock_sessions.created_at',
                [$from, $to]
            )

            ->selectRaw('
                departments.id as department_id,
                departments.name as department_name,

                items.id as item_id,
                items.name as item_name,

                SUM(
                    used_items.quantity
                ) as total_quantity,

                SUM(
                    used_items.total_cost
                ) as total_cost,

                "Operational Usage" as source
            ')

            ->groupBy(
                'departments.id',
                'departments.name',
                'items.id',
                'items.name'
            )

            ->get();

        /*
        |--------------------------------------------------------------------------
        | NORMALIZE DATA
        |--------------------------------------------------------------------------
        */

        $kitUsage = $kitUsage->map(function ($row) {

            return (object)[

                'department_id' => $row->department_id,

                'department_name' => $row->department_name,

                'item_id' => $row->item_id,

                'item_name' => $row->item_name,

                'total_quantity' => (float) $row->total_quantity,

                'total_cost' => (float) $row->total_cost,

                'source' => $row->source,
            ];
        });

        $manualUsage = $manualUsage->map(function ($row) {

            return (object)[

                'department_id' => $row->department_id,

                'department_name' => $row->department_name,

                'item_id' => $row->item_id,

                'item_name' => $row->item_name,

                'total_quantity' => (float) $row->total_quantity,

                'total_cost' => (float) $row->total_cost,

                'source' => $row->source,
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | MERGE & SORT
        |--------------------------------------------------------------------------
        */

        return collect()

            ->concat($kitUsage)

            ->concat($manualUsage)

            ->sortBy([
                ['department_name', 'asc'],
                ['source', 'asc'],
                ['item_name', 'asc']
            ])

            ->values();
    }

    public function usedItemsSummary(array $filters = [])
    {
        $report = $this->usedItemsReport($filters);

        return (object)[

            'quantity' => $report->sum('total_quantity'),

            'cost' => $report->sum('total_cost'),

        ];
    }

    public function generalSalesSummary(array $filters = [])
    {
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        /*
        |--------------------------------------------------------------------------
        | TOTAL SALES
        |--------------------------------------------------------------------------
        */

        $sales = Sale::query()

            ->whereBetween('created_at', [$from, $to])

            ->selectRaw('
                COUNT(*) as total_sales_count,

                SUM(total_amount) as total_sales,

                SUM(total_paid) as total_paid,

                SUM(balance) as total_pending
            ')

            ->first();

        /*
        |--------------------------------------------------------------------------
        | SOLD ITEMS COST
        |--------------------------------------------------------------------------
        */

        $soldItemsCost = SaleItem::query()

            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')

            ->whereBetween('sales.created_at', [$from, $to])

            ->sum('sale_items.cost_at_sale');

        /*
        |--------------------------------------------------------------------------
        | SOLD KITS COST
        |--------------------------------------------------------------------------
        */

        // $soldKitsCost = SaleItemKit::query()

        //     ->join('sales', 'sale_item_kits.sale_id', '=', 'sales.id')

        //     ->whereBetween('sales.created_at', [$from, $to])

        //     ->sum('sale_item_kits.cost_at_sale');

        /*
        |--------------------------------------------------------------------------
        | USED ITEMS COST
        |--------------------------------------------------------------------------
        */

        $usedItemsCost = $this
            ->usedItemsSummary($filters)
            ->cost;

        $totalCost =
            $soldItemsCost
            +
            $usedItemsCost;

        $profit =
            ($sales->total_sales ?? 0)
            -
            $totalCost;

        $profitPercentage =
            ($sales->total_sales ?? 0) > 0
                ? ($profit / $sales->total_sales) * 100
                : 0;

        return (object)[

            'sales_count' => $sales->total_sales_count ?? 0,

            'total_sales' => $sales->total_sales ?? 0,

            'total_paid' => $sales->total_paid ?? 0,

            'total_pending' => $sales->total_pending ?? 0,

            'sold_items_cost' => $soldItemsCost,

            'used_items_cost' => $usedItemsCost,

            'total_cost' => $totalCost,

            'profit' => $profit,

            'profit_percentage' => $profitPercentage,
        ];
    }

    public function paymentSummary(array $filters = [])
    {
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        return SalePayment::query()

            ->join(
                'payment_methods',
                'sale_payments.payment_method',
                '=',
                'payment_methods.id'
            )

            ->whereBetween('sale_payments.created_at', [$from, $to])

            ->selectRaw('
                payment_methods.id,
                payment_methods.name,

                SUM(sale_payments.amount) as total_amount
            ')

            ->groupBy(
                'payment_methods.id',
                'payment_methods.name'
            )

            ->orderByDesc('total_amount')

            ->get();
    }

    public function departmentSalesSummary(array $filters = [])
    {
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        $soldItems = SaleItem::query()

            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')

            ->join('items', 'sale_items.item_id', '=', 'items.id')

            ->join('categories', 'items.category_id', '=', 'categories.id')

            ->join('departments', 'categories.department_id', '=', 'departments.id')

            ->whereBetween('sales.created_at', [$from, $to])

            ->selectRaw('
                departments.id as department_id,
                departments.name as department_name,

                SUM(
                    sale_items.quantity * sale_items.unit_price
                ) as total_sales,

                SUM(
                    sale_items.cost_at_sale
                ) as total_cost
            ')

            ->groupBy(
                'departments.id',
                'departments.name'
            )

            ->get();

        // $soldKits = SaleItemKit::query()

        //     ->join('sales', 'sale_item_kits.sale_id', '=', 'sales.id')

        //     ->join('item_kits', 'sale_item_kits.item_kit_id', '=', 'item_kits.id')

        //     ->join('categories', 'item_kits.category_id', '=', 'categories.id')

        //     ->join('departments', 'categories.department_id', '=', 'departments.id')

        //     ->whereBetween('sales.created_at', [$from, $to])

        //     ->selectRaw('
        //         departments.id as department_id,
        //         departments.name as department_name,

        //         SUM(
        //             sale_item_kits.quantity * sale_item_kits.selling_price
        //         ) as total_sales,

        //         SUM(
        //             sale_item_kits.cost_at_sale
        //         ) as total_cost
        //     ')

        //     ->groupBy(
        //         'departments.id',
        //         'departments.name'
        //     )

        //     ->get();

        $merged = collect()

            ->concat($soldItems)

            ->groupBy('department_id')

            ->map(function ($rows) {

                $sales = $rows->sum('total_sales');

                $cost = $rows->sum('total_cost');

                $profit = $sales - $cost;

                return (object)[

                    'department_name' =>
                        $rows->first()->department_name,

                    'total_sales' => $sales,

                    'total_cost' => $cost,

                    'profit' => $profit,

                    'profit_percentage' =>
                        $sales > 0
                            ? ($profit / $sales) * 100
                            : 0,
                ];
            })

            ->values();

        return $merged;
    }

    public function salesList(array $filters = [])
    {
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        return Sale::query()

            ->with([
                'customer',
                'payments.method',
                'createdBy'
            ])

            ->whereBetween('created_at', [$from, $to])

            ->latest()

            ->get();
    }

    public function individualSalesReport(array $filters = [])
    {
        $from = Carbon::parse($filters['from_date']);

        $to = Carbon::parse($filters['to_date']);

        $sales = Sale::query()

            ->with('servedBy')

            ->whereBetween('created_at', [$from, $to])

            ->get();

        $staffSales = [];

        foreach ($sales as $sale) {

            $servers = $sale->servedBy;

            if ($servers->count() <= 0) {
                continue;
            }

            $shareAmount = $sale->total_amount / $servers->count();

            foreach ($servers as $user) {

                if (!isset($staffSales[$user->id])) {

                    $staffSales[$user->id] = [

                        'user_id' => $user->id,

                        'name' => $user->name,

                        'sales_count' => 0,

                        'total_sales' => 0,
                    ];
                }

                $staffSales[$user->id]['sales_count']++;

                $staffSales[$user->id]['total_sales'] += $shareAmount;
            }
        }

        return collect($staffSales)

            ->map(function ($row) {

                return (object)$row;
            })

            ->sortByDesc('total_sales')

            ->values();
    }
}
