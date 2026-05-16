{{-- EMPTY STATE --}}
@if(count($groupedSoldItems) == 0)

    <div class="erp-page-card p-5 text-center">

        <i class="bi bi-inbox display-5 text-muted"></i>

        <h4 class="fw-bold mt-3">
            No Sold Items Found
        </h4>

        <div class="text-muted">
            No sold items were found within the selected period.
        </div>

    </div>

@else

    {{-- REPORT HEADER --}}
    <div class="erp-page-card p-4 mb-4">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h4 class="fw-bold mb-1">
                    Sold Items Report
                </h4>

                <div class="text-muted">
                    Departmental item sales profitability analysis
                </div>

            </div>

            <span class="badge bg-dark rounded-pill px-4 py-3">
                SOLD ITEMS
            </span>

        </div>

    </div>

    {{-- GLOBAL KPI --}}
    <div class="row g-4 mb-4">

        <div class="col-md-3">

            <div class="erp-page-card p-4 h-100">

                <div class="small text-muted mb-2">
                    Total Quantity Sold
                </div>

                <h3 class="fw-bold mb-0">
                    {{ number_format($summary->qty ?? 0) }}
                </h3>

            </div>

        </div>

        <div class="col-md-3">

            <div class="erp-page-card p-4 h-100">

                <div class="small text-muted mb-2">
                    Total Sales
                </div>

                <h3 class="fw-bold text-success mb-0">
                    {{ number_format($summary->sales ?? 0,2) }}
                </h3>

            </div>

        </div>

        <div class="col-md-3">

            <div class="erp-page-card p-4 h-100">

                <div class="small text-muted mb-2">
                    Total Cost
                </div>

                <h3 class="fw-bold text-danger mb-0">
                    {{ number_format($summary->cost ?? 0,2) }}
                </h3>

            </div>

        </div>

        <div class="col-md-3">

            <div class="erp-page-card p-4 h-100">

                <div class="small text-muted mb-2">
                    Gross Profit
                </div>

                <h3 class="fw-bold text-primary mb-0">
                    {{ number_format($summary->profit ?? 0,2) }}
                </h3>

            </div>

        </div>

    </div>

    {{-- REPORT PERIOD --}}
    <div class="erp-page-card p-3 mb-4">

        <div class="d-flex justify-content-between align-items-center">

            <div class="small text-muted">
                Report Period
            </div>

            <div class="fw-semibold">

                {{ \Carbon\Carbon::parse($fromDate)->format('d M Y H:i') }}

                -

                {{ \Carbon\Carbon::parse($toDate)->format('d M Y H:i') }}

            </div>

        </div>

    </div>

    {{-- DEPARTMENTS --}}
    @foreach($groupedSoldItems as $department => $items)

        @php

            $departmentQty = $items->sum('total_quantity');

            $departmentSales = $items->sum('total_sales');

            $departmentCost = $items->sum('total_cost');

            $departmentProfit = $items->sum('total_profit');

        @endphp

        <div class="erp-page-card p-4 mb-4">

            {{-- HEADER --}}
            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h4 class="fw-bold mb-1">
                        {{ $department }}
                    </h4>

                    <div class="text-muted">
                        Department sales analysis
                    </div>

                </div>

                <span class="badge bg-dark rounded-pill px-4 py-3">
                    {{ number_format($departmentQty) }} Sold
                </span>

            </div>

            {{-- KPI --}}
            <div class="row g-3 mb-4">

                <div class="col-md-3">
                    <div class="border rounded-4 p-3">
                        <div class="small text-muted mb-2">
                            Quantity
                        </div>

                        <h5 class="fw-bold mb-0">
                            {{ number_format($departmentQty) }}
                        </h5>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="border rounded-4 p-3">
                        <div class="small text-muted mb-2">
                            Sales
                        </div>

                        <h5 class="fw-bold text-success mb-0">
                            {{ number_format($departmentSales,2) }}
                        </h5>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="border rounded-4 p-3">
                        <div class="small text-muted mb-2">
                            Cost
                        </div>

                        <h5 class="fw-bold text-danger mb-0">
                            {{ number_format($departmentCost,2) }}
                        </h5>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="border rounded-4 p-3">
                        <div class="small text-muted mb-2">
                            Profit
                        </div>

                        <h5 class="fw-bold text-primary mb-0">
                            {{ number_format($departmentProfit,2) }}
                        </h5>
                    </div>
                </div>

            </div>

            {{-- TABLE --}}
            <div class="table-responsive">

                <table class="table align-middle">

                    <thead class="table-light">

                        <tr>
                            <th>Item</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Sales</th>
                            <th class="text-end">Cost</th>
                            <th class="text-end">Profit</th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach($items as $row)

                            <tr>

                                <td class="fw-semibold">
                                    {{ $row->item_name }}
                                </td>

                                <td class="text-end">
                                    {{ number_format($row->total_quantity) }}
                                </td>

                                <td class="text-end text-success fw-semibold">
                                    {{ number_format($row->total_sales,2) }}
                                </td>

                                <td class="text-end text-danger fw-semibold">
                                    {{ number_format($row->total_cost,2) }}
                                </td>

                                <td class="text-end text-primary fw-bold">
                                    {{ number_format($row->total_profit,2) }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                    <tfoot class="table-light">

                        <tr class="fw-bold">

                            <td>TOTAL</td>

                            <td class="text-end">
                                {{ number_format($departmentQty) }}
                            </td>

                            <td class="text-end text-success">
                                {{ number_format($departmentSales,2) }}
                            </td>

                            <td class="text-end text-danger">
                                {{ number_format($departmentCost,2) }}
                            </td>

                            <td class="text-end text-primary">
                                {{ number_format($departmentProfit,2) }}
                            </td>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    @endforeach

@endif
