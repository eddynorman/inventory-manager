<div class="erp-page-card p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h5 class="fw-bold mb-1">
                Low Stock Items
            </h5>

            <div class="text-muted">
                Items below configured reorder levels
            </div>

        </div>

        <div class="badge bg-warning text-dark px-3 py-2 rounded-pill">

            {{ count($lowStockItems) }} Items

        </div>

    </div>

    <div class="table-responsive">

        <table class="table align-middle">

            <thead>

                <tr>

                    <th>Item</th>

                    <th>Category</th>

                    <th>Current Stock</th>

                    <th>Reorder Level</th>

                    <th>Status</th>

                </tr>

            </thead>

            <tbody>

                @forelse($lowStockItems as $item)

                    <tr>

                        <td class="fw-semibold">
                            {{ $item->name }}
                        </td>

                        <td>
                            {{ $item->category?->name }}
                        </td>

                        <td>

                            <span class="badge bg-danger rounded-pill">

                                {{ number_format($item->current_stock,2) }}

                            </span>

                        </td>

                        <td>

                            {{ number_format($item->reorder_level,2) }}

                        </td>

                        <td>

                            <span class="badge bg-warning text-dark rounded-pill">

                                Reorder Required

                            </span>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5" class="text-center py-5">

                            No low stock items found

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>
