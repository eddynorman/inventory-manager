{{-- EMPTY STATE --}}
@if(count($groupedUsedItems) == 0)

    <div class="erp-page-card p-5 text-center">

        <i class="bi bi-box-seam display-5 text-muted"></i>

        <h4 class="fw-bold mt-3">
            No Used Items Found
        </h4>

        <div class="text-muted">
            No inventory consumption records found for this period.
        </div>

    </div>

@else

    {{-- REPORT HEADER --}}
    <div class="erp-page-card p-4 mb-4">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h4 class="fw-bold mb-1">
                    Used Items Report
                </h4>

                <div class="text-muted">
                    Inventory usage and operational consumption analysis
                </div>

            </div>

            <span class="badge bg-warning text-dark rounded-pill px-4 py-3">
                USED ITEMS
            </span>

        </div>

    </div>

    {{-- GENERATED INFO --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div class="small text-muted">
            Generated at
            {{ now()->format('d M Y H:i') }}
        </div>

        <button class="btn btn-success rounded-4">

            <i class="bi bi-file-earmark-excel me-2"></i>

            Export Excel

        </button>

    </div>

    {{-- GLOBAL KPI --}}
    <div class="row g-4 mb-4">

        <div class="col-md-6">

            <div class="erp-page-card p-4 h-100">

                <div class="small text-muted mb-2">
                    Total Quantity Used
                </div>

                <h3 class="fw-bold mb-0">
                    {{ number_format($summary->quantity ?? 0) }}
                </h3>

            </div>

        </div>

        <div class="col-md-6">

            <div class="erp-page-card p-4 h-100">

                <div class="small text-muted mb-2">
                    Total Consumption Cost
                </div>

                <h3 class="fw-bold text-danger mb-0">
                    {{ number_format($summary->cost ?? 0,2) }}
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
    @foreach($groupedUsedItems as $department => $items)

        @php

            $departmentQty = $items->sum('total_quantity');

            $departmentCost = $items->sum('total_cost');

        @endphp

        <div class="erp-page-card p-4 mb-4">

            {{-- HEADER --}}
            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h4 class="fw-bold mb-1">
                        {{ $department }}
                    </h4>

                    <div class="text-muted">
                        Department inventory consumption
                    </div>

                </div>

                <span class="badge bg-warning text-dark rounded-pill px-4 py-3">
                    {{ number_format($departmentQty) }} Used
                </span>

            </div>

            {{-- KPI --}}
            <div class="row g-3 mb-4">

                <div class="col-md-6">

                    <div class="border rounded-4 p-3">

                        <div class="small text-muted mb-2">
                            Quantity Used
                        </div>

                        <h5 class="fw-bold mb-0">
                            {{ number_format($departmentQty) }}
                        </h5>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="border rounded-4 p-3">

                        <div class="small text-muted mb-2">
                            Consumption Cost
                        </div>

                        <h5 class="fw-bold text-danger mb-0">
                            {{ number_format($departmentCost,2) }}
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
                            <th>Source</th>
                            <th class="text-end">Qty Used</th>
                            <th class="text-end">Cost</th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach($items as $row)

                            <tr>

                                <td class="fw-semibold">
                                    {{ $row->item_name }}
                                </td>

                                <td>

                                    @if($row->source == 'Kit Consumption')

                                        <span class="badge bg-primary">
                                            Kit Consumption
                                        </span>

                                    @else

                                        <span class="badge bg-warning text-dark">
                                            Operational Usage
                                        </span>

                                    @endif

                                </td>

                                <td class="text-end">
                                    {{ number_format($row->total_quantity) }}
                                </td>

                                <td class="text-end text-danger fw-bold">
                                    {{ number_format($row->total_cost,2) }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                    <tfoot class="table-light">

                        <tr class="fw-bold">

                            <td colspan="2">
                                TOTAL
                            </td>

                            <td class="text-end">
                                {{ number_format($departmentQty) }}
                            </td>

                            <td class="text-end text-danger">
                                {{ number_format($departmentCost,2) }}
                            </td>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    @endforeach

@endif
