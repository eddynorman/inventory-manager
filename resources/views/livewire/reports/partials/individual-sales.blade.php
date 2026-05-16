<div class="card border-0 shadow-sm rounded-4">

    <div class="card-body mb-3 p-0">

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th>#</th>

                        <th>Staff</th>

                        <th>Sales Count</th>

                        <th class="text-end">
                            Total Sales
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($individualSales as $index => $sale)

                        <tr>

                            <td>
                                {{ $index + 1 }}
                            </td>

                            <td>

                                <div class="fw-semibold">

                                    {{ $sale->name }}

                                </div>

                            </td>

                            <td>

                                {{ number_format($sale->sales_count) }}

                            </td>

                            <td class="text-end fw-bold">

                                {{ number_format($sale->total_sales,2) }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="4" class="text-center py-5 text-muted">

                                No sales data found

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
