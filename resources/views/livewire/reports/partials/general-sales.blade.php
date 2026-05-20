<div>
    <div class="row g-4 mb-4">

        <div class="col-md-2">
            <div class="erp-page-card p-4 h-100">
                <div class="text-muted small mb-2">
                    Total Sales
                </div>

                <h3 class="fw-bold text-success">
                    {{ number_format($generalSummary->total_sales, 2) }}
                </h3>
            </div>
        </div>

        <div class="col-md-2">
            <div class="erp-page-card p-4 h-100">

                <div class="text-muted small mb-2">
                    Total Paid
                </div>

                <h3 class="fw-bold text-primary">
                    {{ number_format($generalSummary->total_paid, 2) }}
                </h3>

            </div>
        </div>
        <div class="col-md-2">
            <div class="erp-page-card p-4 h-100">

                <div class="text-muted small mb-2">
                    Unpaid Sales
                </div>

                <h3 class="fw-bold text-warning">
                    {{ number_format($generalSummary->total_pending, 2) }}
                </h3>

            </div>
        </div>

        <div class="col-md-2">
            <div class="erp-page-card p-4 h-100">
                <div class="text-muted small mb-2">
                    Total Cost
                </div>

                <h3 class="fw-bold text-danger">
                    {{ number_format($generalSummary->total_cost, 2) }}
                </h3>
            </div>
        </div>

        <div class="col-md-2">
            <div class="erp-page-card p-4 h-100">
                <div class="text-muted small mb-2">
                    Gross Profit
                </div>

                <h3 class="fw-bold text-primary">
                    {{ number_format($generalSummary->profit, 2) }}
                </h3>
            </div>
        </div>

        <div class="col-md-2">
            <div class="erp-page-card p-4 h-100">
                <div class="text-muted small mb-2">
                    Profit Margin
                </div>

                <h3 class="fw-bold text-dark">
                    {{ number_format($generalSummary->profit_percentage, 2) }}%
                </h3>
            </div>
        </div>

    </div>
    <div class="erp-page-card p-4 mb-4">

        <h5 class="fw-bold mb-4">
            Cost Breakdown
        </h5>

        <div class="table-responsive">

            <table class="table align-middle">

                <thead>
                    <tr>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td>Sold Items Cost</td>

                        <td class="text-end fw-semibold">
                            {{ number_format($generalSummary->sold_items_cost, 2) }}
                        </td>
                    </tr>

                    {{-- <tr>
                        <td>Sold Kits Cost</td>

                        <td class="text-end fw-semibold">
                            {{ number_format($generalSummary->sold_kits_cost, 2) }}
                        </td>
                    </tr> --}}

                    <tr>
                        <td>Used Items Cost</td>

                        <td class="text-end fw-semibold">
                            {{ number_format($generalSummary->used_items_cost, 2) }}
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>
    <div class="erp-page-card p-4 mb-4">

        <h5 class="fw-bold mb-4">
            Payment Methods
        </h5>

        <div class="row g-4">

            @foreach($paymentSummary as $payment)

                <div class="col-md-3">

                    <div class="border rounded-4 p-4 h-100">

                        <div class="text-muted small mb-2">
                            {{ $payment->name }}
                        </div>

                        <h4 class="fw-bold mb-0">
                            {{ number_format($payment->total_amount, 2) }}
                        </h4>

                    </div>

                </div>

            @endforeach

        </div>

    </div>
    <div class="erp-page-card p-4 mb-4">

        <h5 class="fw-bold mb-4">
            Department Performance
        </h5>

        <div class="table-responsive">

            <table class="table align-middle">

                <thead>
                    <tr>
                        <th>Department</th>
                        <th class="text-end">Sales</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end">Profit</th>
                        <th class="text-end">Margin</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($departmentSummaries as $department)

                        <tr>

                            <td class="fw-semibold">
                                {{ $department->department_name }}
                            </td>

                            <td class="text-end">
                                {{ number_format($department->total_sales, 2) }}
                            </td>

                            <td class="text-end">
                                {{ number_format($department->total_cost, 2) }}
                            </td>

                            <td class="text-end fw-bold text-primary">
                                {{ number_format($department->profit, 2) }}
                            </td>

                            <td class="text-end">
                                {{ number_format($department->profit_percentage, 2) }}%
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>
    <div class="erp-page-card p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h5 class="fw-bold mb-0">
                Sales Transactions
            </h5>

        </div>

        <div class="table-responsive">

            <table class="table align-middle">

                <thead>
                    <tr>
                        <th>Sale #</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Pending</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($salesList as $sale)

                        <tr>

                            <td class="fw-semibold">
                                #{{ str_pad($sale->id,5,'0',STR_PAD_LEFT) }}
                            </td>

                            <td>
                                {{ $sale->customer?->name ?? 'Walk In Customer' }}
                            </td>

                            <td>
                                {{ number_format($sale->total_amount, 2) }}
                            </td>

                            <td class="text-success">
                                {{ number_format($sale->total_paid, 2) }}
                            </td>

                            <td class="text-danger">
                                {{ number_format($sale->balance, 2) }}
                            </td>

                            <td>
                                {{ $sale->created_at->format('d M Y H:i') }}
                            </td>

                            <td class="text-end">

                                <div class="d-flex justify-content-end gap-2">

                                    <a
                                        href="#"
                                        class="btn btn-sm btn-light rounded-3">

                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a
                                        href="#"
                                        target="_blank"
                                        class="btn btn-sm btn-dark rounded-3">

                                        <i class="bi bi-printer"></i>
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7" class="text-center py-5 text-muted">

                                No sales found for selected period.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>
</div>
