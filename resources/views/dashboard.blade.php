<x-app-layout>

    @php

        $totalItems = \App\Models\Item::count();

        $totalSales = \App\Models\Sale::count();

        $totalPurchases = \App\Models\Purchase::count();

        $lowStock = \App\Models\Item::whereColumn('current_stock', '<=', 'reorder_level')
            ->where('is_stock_item', true)
            ->count();

        $todaySales = \App\Models\Sale::whereDate('created_at', today())
            ->sum('total_amount');

        $monthlySales = \App\Models\Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $monthlyPurchases = \App\Models\Purchase::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $inventoryValue = \App\Models\Item::where('is_stock_item', true)
            ->get()
            ->sum(fn($item) => $item->current_stock * $item->average_cost);

        $recentSales = \App\Models\Sale::with('locations')
            ->latest()
            ->limit(6)
            ->get();

        $recentPurchases = \App\Models\Purchase::with('supplier')
            ->latest()
            ->limit(6)
            ->get();

        $topLowStockItems = \App\Models\Item::whereColumn('current_stock', '<=', 'reorder_level')
            ->where('is_stock_item', true)
            ->limit(5)
            ->get();

    @endphp

    <x-slot name="header">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

            <div>
                <h2 class="fw-bold mb-1 text-dark">
                    Dashboard
                </h2>

                <div class="text-muted small">
                    Welcome back, {{ auth()->user()->name }} 👋
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">

                <div class="dashboard-date-pill">
                    <i class="bi bi-calendar3"></i>
                    {{ now()->format('D, d M Y') }}
                </div>

                <div class="dashboard-live-pill">
                    <span class="live-dot"></span>
                    ERP Live
                </div>
            </div>
        </div>

    </x-slot>

    <style>

        .dashboard-gradient-card{
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,.08);
            color: white;
            transition: all .3s ease;
            min-height: 170px;

            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .dashboard-gradient-card:hover{
            transform: translateY(-4px);
            box-shadow: 0 20px 45px rgba(15,23,42,.15);
        }

        .dashboard-gradient-card::before{
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom right, rgba(255,255,255,.08), transparent);
            pointer-events: none;
        }

        .gradient-primary{
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        .gradient-success{
            background: linear-gradient(135deg, #059669, #047857);
        }

        .gradient-warning{
            background: linear-gradient(135deg, #d97706, #b45309);
        }

        .gradient-danger{
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }

        .gradient-dark{
            background: linear-gradient(135deg, #0f172a, #111827);
        }

        .dashboard-stat-icon{
            width: 58px;
            height: 58px;
            border-radius: 18px;
            background: rgba(255,255,255,.14);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            backdrop-filter: blur(8px);
        }

        .dashboard-value{
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .dashboard-label{
            color: rgba(255,255,255,.75);
            text-transform: uppercase;
            font-size: .76rem;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .dashboard-card{
            background: white;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(15,23,42,.04);
            overflow: hidden;
            transition: all .3s ease;
        }

        .dashboard-card:hover{
            transform: translateY(-2px);
            box-shadow: 0 18px 35px rgba(15,23,42,.08);
        }

        .dashboard-card-header{
            padding: 1.2rem 1.4rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .dashboard-card-body{
            padding: 1.4rem;
        }

        .dashboard-table tbody tr{
            transition: all .2s ease;
        }

        .dashboard-table tbody tr:hover{
            background: #f8fafc;
        }

        .status-badge{
            padding: .35rem .7rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
        }

        .status-success{
            background: #dcfce7;
            color: #166534;
        }

        .status-danger{
            background: #fee2e2;
            color: #991b1b;
        }

        .quick-action-btn{
            border-radius: 18px;
            padding: 1rem;
            background: white;
            border: 1px solid #e2e8f0;
            transition: all .25s ease;
            text-decoration: none;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .quick-action-btn:hover{
            background: #0f172a;
            color: white;
            transform: translateY(-3px);
        }

        .quick-action-icon{
            width: 50px;
            height: 50px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            font-size: 1.2rem;
        }

        .dashboard-live-pill{
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            padding: .6rem 1rem;
            font-size: .8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .dashboard-date-pill{
            background: #0f172a;
            color: white;
            border-radius: 999px;
            padding: .6rem 1rem;
            font-size: .82rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .live-dot{
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #22c55e;
            animation: pulse 1.4s infinite;
        }

        @keyframes pulse{
            0%{
                opacity: .4;
                transform: scale(.9);
            }

            50%{
                opacity: 1;
                transform: scale(1.1);
            }

            100%{
                opacity: .4;
                transform: scale(.9);
            }
        }

        .mini-stat{
            padding: 1rem;
            border-radius: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

    </style>

    {{-- TOP KPI CARDS --}}
    <div class="row g-4">

        <div class="col-12 col-md-6 col-xl-3">

            <div class="dashboard-gradient-card gradient-primary p-4">

                <div class="d-flex justify-content-between align-items-start h-100">

                    <div>

                        <div class="dashboard-label mb-3">
                            Total Items
                        </div>

                        <div class="dashboard-value">
                            {{ number_format($totalItems) }}
                        </div>

                        <div class="small mt-3 text-white-50">
                            Inventory products & stock items
                        </div>

                    </div>

                    <div class="dashboard-stat-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>

                </div>

            </div>

        </div>

        <div class="col-12 col-md-6 col-xl-3">

            <div class="dashboard-gradient-card gradient-success p-4">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="dashboard-label mb-3">
                            Today's Sales
                        </div>

                        <div class="dashboard-value">
                            {{ number_format($todaySales, 2) }}
                        </div>

                        <div class="small mt-3 text-white-50">
                            Revenue generated today
                        </div>

                    </div>

                    <div class="dashboard-stat-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>

                </div>

            </div>

        </div>

        <div class="col-12 col-md-6 col-xl-3">

            <div class="dashboard-gradient-card gradient-warning p-4">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="dashboard-label mb-3">
                            Monthly Purchases
                        </div>

                        <div class="dashboard-value">
                            {{ number_format($monthlyPurchases, 2) }}
                        </div>

                        <div class="small mt-3 text-white-50">
                            Procurement this month
                        </div>

                    </div>

                    <div class="dashboard-stat-icon">
                        <i class="bi bi-bag-check"></i>
                    </div>

                </div>

            </div>

        </div>

        <div class="col-12 col-md-6 col-xl-3">

            <div class="dashboard-gradient-card gradient-danger p-4">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="dashboard-label mb-3">
                            Low Stock Alerts
                        </div>

                        <div class="dashboard-value">
                            {{ number_format($lowStock) }}
                        </div>

                        <div class="small mt-3 text-white-50">
                            Items below reorder level
                        </div>

                    </div>

                    <div class="dashboard-stat-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- SECOND ROW --}}
    <div class="row g-4 mt-1">

        {{-- BUSINESS SUMMARY --}}
        <div class="col-12 col-xl-8">

            <div class="dashboard-card h-100">

                <div class="dashboard-card-header d-flex justify-content-between align-items-center">

                    <div>

                        <h5 class="fw-bold mb-1">
                            Business Overview
                        </h5>

                        <div class="text-muted small">
                            ERP operational summary
                        </div>

                    </div>

                    <div class="text-success fw-semibold small">
                        System Operational
                    </div>

                </div>

                <div class="dashboard-card-body">

                    <div class="row g-3">

                        <div class="col-md-4">

                            <div class="mini-stat">

                                <div class="text-muted small mb-2">
                                    Monthly Sales
                                </div>

                                <h4 class="fw-bold mb-0">
                                    {{ number_format($monthlySales, 2) }}
                                </h4>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="mini-stat">

                                <div class="text-muted small mb-2">
                                    Inventory Value
                                </div>

                                <h4 class="fw-bold mb-0">
                                    {{ number_format($inventoryValue, 2) }}
                                </h4>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="mini-stat">

                                <div class="text-muted small mb-2">
                                    Total Purchases
                                </div>

                                <h4 class="fw-bold mb-0">
                                    {{ number_format($totalPurchases) }}
                                </h4>

                            </div>

                        </div>

                    </div>

                    <div class="mt-4">

                        <h6 class="fw-bold mb-3">
                            Quick Actions
                        </h6>

                        <div class="row g-3">

                            <div class="col-md-6">

                                <a href="{{ route('sales') }}"
                                   class="quick-action-btn">

                                    <div class="quick-action-icon">
                                        <i class="bi bi-cart-check"></i>
                                    </div>

                                    <div>
                                        <div class="fw-semibold">
                                            Create Sale
                                        </div>

                                        <div class="small text-muted">
                                            Process customer transactions
                                        </div>
                                    </div>

                                </a>

                            </div>

                            <div class="col-md-6">

                                <a href="{{ route('purchases') }}"
                                   class="quick-action-btn">

                                    <div class="quick-action-icon">
                                        <i class="bi bi-bag-plus"></i>
                                    </div>

                                    <div>
                                        <div class="fw-semibold">
                                            Record Purchase
                                        </div>

                                        <div class="small text-muted">
                                            Add supplier purchases
                                        </div>
                                    </div>

                                </a>

                            </div>

                            <div class="col-md-6">

                                <a href="{{ route('items') }}"
                                   class="quick-action-btn">

                                    <div class="quick-action-icon">
                                        <i class="bi bi-box-seam"></i>
                                    </div>

                                    <div>
                                        <div class="fw-semibold">
                                            Manage Items
                                        </div>

                                        <div class="small text-muted">
                                            Update inventory items
                                        </div>
                                    </div>

                                </a>

                            </div>

                            <div class="col-md-6">

                                <a href="{{'#' }}"
                                   class="quick-action-btn">

                                    <div class="quick-action-icon">
                                        <i class="bi bi-bar-chart"></i>
                                    </div>

                                    <div>
                                        <div class="fw-semibold">
                                            Reports
                                        </div>

                                        <div class="small text-muted">
                                            Analyze business performance
                                        </div>
                                    </div>

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- LOW STOCK --}}
        <div class="col-12 col-xl-4">

            <div class="dashboard-card h-100">

                <div class="dashboard-card-header">

                    <h5 class="fw-bold mb-1">
                        Low Stock Items
                    </h5>

                    <div class="text-muted small">
                        Immediate reorder attention required
                    </div>

                </div>

                <div class="dashboard-card-body">

                    @forelse($topLowStockItems as $item)

                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">

                            <div>

                                <div class="fw-semibold">
                                    {{ $item->name }}
                                </div>

                                <div class="small text-muted">
                                    Reorder Level:
                                    {{ $item->reorder_level }}
                                </div>

                            </div>

                            <span class="status-badge status-danger">
                                {{ $item->current_stock }}
                            </span>

                        </div>

                    @empty

                        <div class="text-center py-5">

                            <i class="bi bi-check-circle fs-1 text-success"></i>

                            <div class="fw-semibold mt-3">
                                No low stock items
                            </div>

                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

    {{-- TABLES --}}
    <div class="row g-4 mt-1">

        {{-- RECENT SALES --}}
        <div class="col-12 col-xl-6">

            <div class="dashboard-card">

                <div class="dashboard-card-header d-flex justify-content-between align-items-center">

                    <div>

                        <h5 class="fw-bold mb-1">
                            Recent Sales
                        </h5>

                        <div class="text-muted small">
                            Latest customer transactions
                        </div>

                    </div>

                    <i class="bi bi-cart-check text-success fs-4"></i>

                </div>

                <div class="table-responsive">

                    <table class="table dashboard-table align-middle mb-0">

                        <thead class="table-light">

                            <tr>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Total</th>
                            </tr>

                        </thead>

                        <tbody>

                            @forelse($recentSales as $sale)

                                <tr>

                                    <td>
                                        {{ optional($sale->created_at)->format('d M Y') }}

                                        <div class="small text-muted">
                                            {{ optional($sale->created_at)->format('H:i') }}
                                        </div>
                                    </td>

                                    <td>
                                        {{ optional($sale->locations->first())->name ?? '-' }}
                                    </td>

                                    <td class="fw-bold text-success">
                                        {{ number_format($sale->total_amount, 2) }}
                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="3" class="text-center py-4 text-muted">
                                        No sales available
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        {{-- RECENT PURCHASES --}}
        <div class="col-12 col-xl-6">

            <div class="dashboard-card">

                <div class="dashboard-card-header d-flex justify-content-between align-items-center">

                    <div>

                        <h5 class="fw-bold mb-1">
                            Recent Purchases
                        </h5>

                        <div class="text-muted small">
                            Latest procurement records
                        </div>

                    </div>

                    <i class="bi bi-bag-check text-primary fs-4"></i>

                </div>

                <div class="table-responsive">

                    <table class="table dashboard-table align-middle mb-0">

                        <thead class="table-light">

                            <tr>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>Total</th>
                            </tr>

                        </thead>

                        <tbody>

                            @forelse($recentPurchases as $purchase)

                                <tr>

                                    <td>
                                        {{ optional($purchase->created_at)->format('d M Y') }}

                                        <div class="small text-muted">
                                            {{ optional($purchase->created_at)->format('H:i') }}
                                        </div>
                                    </td>

                                    <td>
                                        {{ optional($purchase->supplier)->name ?? '-' }}
                                    </td>

                                    <td class="fw-bold text-primary">
                                        {{ number_format($purchase->total_amount, 2) }}
                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="3" class="text-center py-4 text-muted">
                                        No purchases available
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>
