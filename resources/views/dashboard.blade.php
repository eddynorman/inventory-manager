<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="row g-3">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted">Items</h6>
                    <h3 class="card-title">{{ \App\Models\Item::count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted">Sales</h6>
                    <h3 class="card-title">{{ \App\Models\Sale::count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted">Purchases</h6>
                    <h3 class="card-title">{{ \App\Models\Purchase::count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted">Low Stock</h6>
                    <h3 class="card-title">{{ \App\Models\Item::whereColumn('current_stock','<=','reorder_level')->where('is_stock_item',true)->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header">Recent Sales</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\Sale::with('locations')->orderByDesc('id')->limit(5)->get() as $sale)
                                    <tr>
                                        <td>{{ optional($sale->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>{{ optional($sale->locations->first())->name ?? '-' }}</td>
                                        <td>{{ number_format($sale->total_amount,2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header">Recent Purchases</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\Purchase::with('supplier')->orderByDesc('id')->limit(5)->get() as $purchase)
                                    <tr>
                                        <td>{{ optional($purchase->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>{{ optional($purchase->supplier)->name ?? '-' }}</td>
                                        <td>{{ number_format($purchase->total_amount,2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
