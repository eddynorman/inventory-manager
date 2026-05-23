<?php

namespace App\Services\Reports;

use App\Enums\StockBatchType;
use Carbon\Carbon;

use App\Models\Item;
use App\Models\ItemLocation;
use App\Models\ReceivingItem;
use App\Models\SaleItemBatch;
use App\Models\StockAdjustmentItem;
use App\Models\StockMovement;
use App\Models\UsedItems;

class StockReportService
{
    /*
    |--------------------------------------------------------------------------
    | STOCK MOVEMENT REPORT
    |--------------------------------------------------------------------------
    */

    public function stockMovementReport(array $filters = [])
    {
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        $departmentId = $filters['department_id'] ?? null;
        $locationId = $filters['location_id'] ?? null;

        $items = Item::query()

            ->with([
                'category.department',
                'locationItems.location'
            ])

            ->where('is_stock_item', true)

            ->when($departmentId, function ($q) use ($departmentId) {

                $q->whereHas(
                    'category',
                    fn ($c) =>
                    $c->where('department_id', $departmentId)
                );
            })
            ->when($locationId, function ($q) use ($locationId) {

                $q->whereHas('locationItems', function ($locationQuery) use ($locationId) {

                    $locationQuery
                        ->where('location_id', $locationId);
                });
            })

            ->get();

        return $items->map(function ($item) use (
            $from,
            $to,
            $locationId
        ) {

            /*
            |--------------------------------------------------------------------------
            | OPENING STOCK
            |--------------------------------------------------------------------------
            */
            $itemCreatedBeforePeriod =
            $item->created_at <= $from;
            $opening = 0;

            if (!$itemCreatedBeforePeriod) {

                /*
                |--------------------------------------------------------------------------
                | Item created inside selected period
                |--------------------------------------------------------------------------
                |
                | Opening stock should include the first NEW_ITEM movement
                | because the item did not exist before the report period.
                |
                */

                $opening = StockMovement::query()
                    ->where('item_id', $item->id)
                    ->where('reference_type', StockBatchType::NEW_ITEM->value)
                    ->when(
                        $locationId,
                        fn ($q) =>
                        $q->where('from_location', $locationId)
                    )
                    ->sum('quantity');

            } else {

                $opening = StockMovement::query()
                ->where('item_id', $item->id)
                ->where('created_at', '<', $from)
                ->when($locationId, function ($q) use ($locationId) {
                    $q->where(function ($sub) use ($locationId) {

                        // 1. Standard movements OR things leaving this location
                        $sub->where(function ($query) use ($locationId) {
                            $query->where('from_location', $locationId)
                                ->where('type', '!=', 'transfer receiving');
                                // ^ Ignores the +20 row when we are looking at the sender side
                        })

                        // 2. Things arriving at this location
                        ->orWhere(function ($query) use ($locationId) {
                            $query->where('to_location', $locationId)
                                ->where('type', 'transfer receiving');
                                // ^ Only catches the +20 row when we are looking at the receiver side
                        });

                    });
                })
                ->sum('quantity');
            }

            /*
            |--------------------------------------------------------------------------
            | RECEIVED
            |--------------------------------------------------------------------------
            */

            $received = StockMovement::query()

                ->where('item_id', $item->id)

                ->where('type', 'receiving')

                ->whereBetween('created_at', [$from, $to])

                ->when(
                    $locationId,
                    fn ($q) =>
                    $q->where('from_location', $locationId)
                )


                ->sum('quantity');

            /*
            |--------------------------------------------------------------------------
            | ADJUSTMENTS
            |--------------------------------------------------------------------------
            */

            $adjustments = StockMovement::query()

                ->where('item_id', $item->id)

                ->where('type', 'adjustment')

                ->when(
                    $locationId,
                    fn ($q) =>
                    $q->where('from_location', $locationId)
                )

                ->whereBetween('created_at', [$from, $to])

                ->sum('quantity');

            /*
            |--------------------------------------------------------------------------
            | USED / SOLD
            |--------------------------------------------------------------------------
            */

            $used = abs(
                StockMovement::query()

                    ->where('item_id', $item->id)

                    ->whereIn('type', [
                        'Item Sale',
                        'closing-stock',
                    ])
                    ->whereBetween('created_at', [$from, $to])
                    ->when(
                        $locationId,
                        fn ($q) =>
                        $q->where('from_location', $locationId)
                    )

                    ->sum('quantity')
            );

            /*
            |--------------------------------------------------------------------------
            | TRANSFER IN
            |--------------------------------------------------------------------------
            */

            $transferIn = StockMovement::query()

                ->where('item_id', $item->id)

                ->where('type', 'transfer receiving')

                ->whereBetween('created_at', [$from, $to])

                ->when(
                    $locationId,
                    fn ($q) =>
                    $q->where('to_location', $locationId)
                )

            ->sum('quantity');

            /*
            |--------------------------------------------------------------------------
            | TRANSFER OUT
            |--------------------------------------------------------------------------
            */

            $transferOut = abs(
                StockMovement::query()

                    ->where('item_id', $item->id)

                    ->where('type', 'transfer')

                    ->when(
                        $locationId,
                        fn ($q) =>
                        $q->where('from_location', $locationId)
                    )

                    ->whereBetween('created_at', [$from, $to])

                    ->sum('quantity')
            );
            /*
            |--------------------------------------------------------------------------
            | CLOSING
            |--------------------------------------------------------------------------
            */

            $closing =
                $opening
                +
                $received
                +
                $transferIn
                +
                $adjustments
                -
                $used
                -
                $transferOut;

            return (object)[

                'item_id' => $item->id,

                'item_name' => $item->name,

                'department_name' =>
                    optional(
                        optional($item->category)
                            ->department
                    )->name,

                'opening_stock' => $opening,

                'received_quantity' => $received,

                'adjustment_quantity' => $adjustments,

                'transfer_in_quantity' => $transferIn,

                'transfer_out_quantity' => $transferOut,

                'total_available' =>
                    $opening
                    +
                    $received
                    +
                    $transferIn
                    +
                    $adjustments,

                'used_quantity' => $used,

                'closing_stock' => $closing,
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | STOCK VALUATION REPORT
    |--------------------------------------------------------------------------
    */

    public function stockValuationReport(array $filters = [])
    {
        $departmentId = $filters['department_id'] ?? null;
        $locationId = $filters['location_id'] ?? null;

        return ItemLocation::query()

            ->join('items', 'item_locations.item_id', '=', 'items.id')

            ->leftJoin(
                'categories',
                'items.category_id',
                '=',
                'categories.id'
            )

            ->leftJoin(
                'departments',
                'categories.department_id',
                '=',
                'departments.id'
            )

            ->leftJoin(
                'locations',
                'item_locations.location_id',
                '=',
                'locations.id'
            )

            ->leftJoin('units', function ($join) {

                $join->on('items.id', '=', 'units.item_id')
                    ->where('units.is_smallest_unit', true);
            })

            ->where('items.is_stock_item', true)

            ->when($departmentId, function ($q) use ($departmentId) {

                $q->where(
                    'departments.id',
                    $departmentId
                );
            })

            ->when($locationId, function ($q) use ($locationId) {

                $q->where(
                    'locations.id',
                    $locationId
                );
            })

            ->selectRaw('
                items.id as item_id,
                items.name as item_name,

                departments.name as department_name,

                locations.name as location_name,

                item_locations.quantity as current_stock,

                units.buying_price as cost_price,

                (
                    item_locations.quantity
                    *
                    units.buying_price
                ) as stock_value
            ')

            ->orderBy('items.name')

            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | LOW STOCK REPORT
    |--------------------------------------------------------------------------
    */

    public function lowStockReport()
    {
        return Item::query()

            ->lowStock()

            ->with([
                'category.department'
            ])

            ->orderBy('current_stock')

            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | NEGATIVE STOCK REPORT
    |--------------------------------------------------------------------------
    */

    public function negativeStockReport()
    {
        return ItemLocation::query()

            ->join('items', 'item_locations.item_id', '=', 'items.id')

            ->join(
                'locations',
                'item_locations.location_id',
                '=',
                'locations.id'
            )

            ->where('item_locations.quantity', '<', 0)

            ->selectRaw('
                items.name as item_name,
                locations.name as location_name,
                item_locations.quantity
            ')

            ->orderBy('item_locations.quantity')

            ->get();
    }
}
