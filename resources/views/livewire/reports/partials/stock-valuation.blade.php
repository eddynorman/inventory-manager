<div class="erp-page-card p-4">

    <div class="table-responsive">

        <table class="table align-middle">

            <thead>

                <tr>

                    <th>Item</th>

                    <th>Department</th>

                    <th>Location</th>

                    <th class="text-end">
                        Current Stock
                    </th>

                    <th class="text-end">
                        Cost Price
                    </th>

                    <th class="text-end">
                        Stock Value
                    </th>

                </tr>

            </thead>

            <tbody>

                @php
                    $grandTotal = 0;
                @endphp

                @forelse($stockValuation as $row)

                    @php
                        $grandTotal += $row->stock_value;
                    @endphp

                    <tr>

                        <td>
                            {{ $row->item_name }}
                        </td>

                        <td>
                            {{ $row->department_name }}
                        </td>

                        <td>
                            {{ $row->location_name }}
                        </td>

                        <td class="text-end">
                            {{ number_format($row->current_stock,2) }}
                        </td>

                        <td class="text-end">
                            {{ number_format($row->cost_price,2) }}
                        </td>

                        <td class="text-end fw-bold">
                            {{ number_format($row->stock_value,2) }}
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6" class="text-center py-5">

                            No valuation data found

                        </td>

                    </tr>

                @endforelse

            </tbody>

            <tfoot>

                <tr>

                    <th colspan="5" class="text-end">
                        Total Inventory Value
                    </th>

                    <th class="text-end text-success">

                        {{ number_format($grandTotal,2) }}

                    </th>

                </tr>

            </tfoot>

        </table>

    </div>

</div>
