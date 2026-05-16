    <div class="erp-page-card p-4">

        <div class="table-responsive">

            <table class="table align-middle">

                <thead>

                    <tr>

                        <th>Item</th>

                        <th>Department</th>

                        <th class="text-end">
                            Opening
                        </th>

                        <th class="text-end">
                            Received
                        </th>

                        <th class="text-end">
                            Adjustments
                        </th>

                        <th class="text-end">
                            Total
                        </th>

                        <th class="text-end">
                            Used/Sold
                        </th>

                        <th class="text-end">
                            Closing
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($stockMovement as $row)

                        <tr>

                            <td>
                                {{ $row->item_name }}
                            </td>

                            <td>
                                {{ $row->department_name }}
                            </td>

                            <td class="text-end">
                                {{ number_format($row->opening_stock,2) }}
                            </td>

                            <td class="text-end text-success">
                                {{ number_format($row->received_quantity,2) }}
                            </td>

                            <td class="text-end text-warning">
                                {{ number_format($row->adjustment_quantity,2) }}
                            </td>

                            <td class="text-end fw-bold">
                                {{ number_format($row->total_available,2) }}
                            </td>

                            <td class="text-end text-danger">
                                {{ number_format($row->used_quantity,2) }}
                            </td>

                            <td class="text-end fw-bold">
                                {{ number_format($row->closing_stock,2) }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="text-center py-5">

                                No stock movement found

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>
