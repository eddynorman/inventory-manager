<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Inventory Manager')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Fontawesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        crossorigin="anonymous"
        referrerpolicy="no-referrer" />

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css"
        rel="stylesheet" />

    @livewireStyles

    <style>

        :root{
            --erp-dark: #111827;
            --erp-sidebar: #0f172a;
            --erp-sidebar-hover: #1e293b;
            --erp-sidebar-active: #2563eb;
            --erp-light: #f8fafc;
            --erp-border: #e2e8f0;
            --erp-text: #334155;
            --erp-muted: #94a3b8;
        }

        body{
            font-family: 'Figtree', sans-serif;
            background: #f1f5f9;
            overflow-x: hidden;
        }

        /*
        |--------------------------------------------------------------------------
        | POWERGRID TABLES
        |--------------------------------------------------------------------------
        */

        table.power-grid-table {
            width: 100%;
            border-collapse: separate !important;
            border-spacing: 0;
            font-size: 14px;
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
        }

        table.power-grid-table thead th {
            background: #f8fafc !important;
            color: #64748b !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: .5px;
            border-bottom: 1px solid #e2e8f0 !important;
            padding: 12px;
            white-space: nowrap;
        }

        table.power-grid-table thead th > div {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }

        table.power-grid-table tbody td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9 !important;
            vertical-align: middle;
        }

        table.power-grid-table tbody tr:hover {
            background-color: #f8fafc;
        }

        table.power-grid-table tbody tr:nth-child(even) {
            background-color: #fcfcfc;
        }

        table.power-grid-table th,
        table.power-grid-table td {
            border: none !important;
        }

        .pg-footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }

        .pg-search input {
            border-radius: 10px;
            border: 1px solid #dbeafe;
            padding: 8px 12px;
        }

        /*
        |--------------------------------------------------------------------------
        | ERP LAYOUT
        |--------------------------------------------------------------------------
        */

        .erp-navbar{
            height: 70px;
            background: linear-gradient(to right, #0f172a, #111827);
            border-bottom: 1px solid rgba(255,255,255,.08);
            z-index: 1050;
        }

        .erp-brand{
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: .3px;
        }

        .erp-sidebar{
            width: 280px;
            background: var(--erp-sidebar);
            min-height: calc(100vh - 70px);
            border-right: 1px solid rgba(255,255,255,.05);
            overflow-y: auto;
            transition: all .3s ease;
        }

        .erp-sidebar::-webkit-scrollbar{
            width: 6px;
        }

        .erp-sidebar::-webkit-scrollbar-thumb{
            background: rgba(255,255,255,.08);
            border-radius: 20px;
        }

        .erp-main{
            flex: 1;
            min-width: 0;
            background: #f1f5f9;
        }

        .erp-content{
            padding: 1.5rem;
        }

        .sidebar-section{
            padding: 1.25rem 1rem .5rem;
        }

        .sidebar-title{
            color: rgba(255,255,255,.45);
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: .8rem;
        }

        .sidebar-link{
            display: flex;
            align-items: center;
            gap: .9rem;
            color: rgba(255,255,255,.78);
            text-decoration: none;
            padding: .82rem 1rem;
            border-radius: 14px;
            margin-bottom: .35rem;
            transition: all .2s ease;
            font-size: .94rem;
            font-weight: 500;
        }

        .sidebar-link:hover{
            background: var(--erp-sidebar-hover);
            color: #fff;
            transform: translateX(2px);
        }

        .sidebar-link.active{
            background: linear-gradient(to right, #2563eb, #1d4ed8);
            color: white;
            box-shadow: 0 8px 20px rgba(37,99,235,.25);
        }

        .sidebar-link i{
            width: 18px;
            text-align: center;
            font-size: .95rem;
        }

        .erp-user-avatar{
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255,255,255,.12);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }

        .erp-page-card{
            background: white;
            border-radius: 22px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(15,23,42,.04);
        }

        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 991px){

            .erp-sidebar{
                position: fixed;
                top: 70px;
                left: -100%;
                width: 290px;
                z-index: 1040;
            }

            .erp-sidebar.show{
                left: 0;
            }

            .erp-overlay{
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.5);
                z-index: 1035;
                display: none;
            }

            .erp-overlay.show{
                display: block;
            }

            .erp-content{
                padding: 1rem;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | MODAL WIDTHS
        |--------------------------------------------------------------------------
        */

        .custom-modal-width-sm { max-width: 30rem; }
        .custom-modal-width-md { max-width: 40rem; }
        .custom-modal-width-lg { max-width: 50rem; }
        .custom-modal-width-xl { max-width: 60rem; }
        .custom-modal-width-2xl { max-width: 70rem; }

    </style>

    <link rel="icon" type="image/x-icon" href="{{ asset('icons/favicon.png') }}">
</head>

<body>

@php
    $user = auth()->user();
@endphp

<div class="min-vh-100 d-flex flex-column">

    <!-- TOP NAVBAR -->
    <nav class="navbar navbar-dark erp-navbar px-3 sticky-top">

        <div class="d-flex align-items-center gap-3">

            <button class="btn btn-dark d-lg-none border-0"
                    id="sidebarToggle">

                <i class="bi bi-list fs-3"></i>
            </button>

            <a href="{{ route('dashboard') }}"
               class="navbar-brand erp-brand mb-0">

                <i class="fa-solid fa-boxes-stacked me-2"></i>
                Inventory Manager
            </a>
        </div>

        <div class="dropdown">

            <a class="text-decoration-none dropdown-toggle d-flex align-items-center gap-3 text-white"
               href="#"
               role="button"
               data-bs-toggle="dropdown">

                <div class="erp-user-avatar">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </div>

                <div class="d-none d-md-block text-start">
                    <div class="fw-semibold small">
                        {{ $user->name ?? 'User' }}
                    </div>

                    <div class="text-white-50 small">
                        ERP User
                    </div>
                </div>
            </a>

            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2">

                <li>
                    <a class="dropdown-item rounded-3 py-2"
                       href="{{ route('profile') }}">

                        <i class="bi bi-person me-2"></i>
                        Profile
                    </a>
                </li>

                <li>
                    <a class="dropdown-item rounded-3 py-2"
                       href="{{ route('profile') }}#password">

                        <i class="bi bi-key me-2"></i>
                        Change Password
                    </a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button class="dropdown-item rounded-3 py-2 text-danger"
                                type="submit">

                            <i class="bi bi-box-arrow-right me-2"></i>
                            Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <div class="d-flex flex-grow-1 position-relative">

        <!-- MOBILE OVERLAY -->
        <div class="erp-overlay"
             id="sidebarOverlay"></div>

        <!-- SIDEBAR -->
        <aside class="erp-sidebar"
               id="erpSidebar">

            <!-- DASHBOARD -->
            <div class="sidebar-section">

                <div class="sidebar-title">
                    Main
                </div>

                @if($user->canAccess('dashboard.view'))

                    <a href="{{ route('dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">

                        <i class="bi bi-grid"></i>
                        <span>Dashboard</span>
                    </a>

                @endif
            </div>

            <!-- INVENTORY -->
            <div class="sidebar-section">

                <div class="sidebar-title">
                    Inventory
                </div>

                @if($user->canAccess('items.view'))
                    <a href="{{ route('items') }}"
                       class="sidebar-link {{ request()->routeIs('items') ? 'active' : '' }}">
                        <i class="bi bi-box-seam"></i>
                        <span>Items</span>
                    </a>
                @endif

                @if($user->canAccess('item_kits.view'))
                    <a href="{{ route('item-kits') }}"
                       class="sidebar-link {{ request()->routeIs('item-kits') ? 'active' : '' }}">
                        <i class="bi bi-collection"></i>
                        <span>Item Kits</span>
                    </a>
                @endif

                @if($user->canAccess('categories.view'))
                    <a href="{{ route('categories') }}"
                       class="sidebar-link {{ request()->routeIs('categories') ? 'active' : '' }}">
                        <i class="bi bi-tags"></i>
                        <span>Categories</span>
                    </a>
                @endif

                @if($user->canAccess('departments.view'))
                    <a href="{{ route('departments') }}"
                       class="sidebar-link {{ request()->routeIs('departments') ? 'active' : '' }}">
                        <i class="bi bi-diagram-3"></i>
                        <span>Departments</span>
                    </a>
                @endif

                @if($user->canAccess('locations.view'))
                    <a href="{{ route('locations') }}"
                       class="sidebar-link {{ request()->routeIs('locations') ? 'active' : '' }}">
                        <i class="bi bi-geo-alt"></i>
                        <span>Locations</span>
                    </a>
                @endif

                @if($user->canAccess('stock.view'))
                    <a href="{{ route('closing-stock') }}"
                       class="sidebar-link {{ request()->routeIs('closing-stock') ? 'active' : '' }}">
                        <i class="bi bi-boxes"></i>
                        <span>Closing Stock</span>
                    </a>
                @endif

                @if($user->canAccess('stock.adjust'))
                    <a href="{{ route('adjustments') }}"
                       class="sidebar-link {{ request()->routeIs('adjustments') ? 'active' : '' }}">
                        <i class="bi bi-sliders"></i>
                        <span>Stock Adjustments</span>
                    </a>
                @endif

                @if($user->canAccess('items.view'))
                    <a href="{{ route('assets') }}"
                       class="sidebar-link {{ request()->routeIs('assets') ? 'active' : '' }}">
                        <i class="bi bi-pc-display"></i>
                        <span>Assets</span>
                    </a>
                @endif
            </div>

            <!-- OPERATIONS -->
            <div class="sidebar-section">

                <div class="sidebar-title">
                    Operations
                </div>

                @if($user->canAccess('sales.view'))
                    <a href="{{ route('sales') }}"
                       class="sidebar-link {{ request()->routeIs('sales') ? 'active' : '' }}">
                        <i class="bi bi-cart-check"></i>
                        <span>Sales</span>
                    </a>
                @endif

                @if($user->canAccess('purchases.view'))
                    <a href="{{ route('purchases') }}"
                       class="sidebar-link {{ request()->routeIs('purchases') ? 'active' : '' }}">
                        <i class="bi bi-bag-check"></i>
                        <span>Purchases</span>
                    </a>
                @endif

                @if($user->canAccess('receivings.view'))
                    <a href="{{ route('receivings') }}"
                       class="sidebar-link {{ request()->routeIs('receivings') ? 'active' : '' }}">
                        <i class="bi bi-box-arrow-in-down"></i>
                        <span>Receivings</span>
                    </a>
                @endif

                @if($user->canAccess('issues.view'))
                    <a href="{{ route('issues') }}"
                       class="sidebar-link {{ request()->routeIs('issues') ? 'active' : '' }}">
                        <i class="bi bi-exclamation-diamond"></i>
                        <span>Issues</span>
                    </a>
                @endif

                @if($user->canAccess('transfers.view'))
                    <a href="{{ route('transfers') }}"
                       class="sidebar-link {{ request()->routeIs('transfers') ? 'active' : '' }}">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Transfers</span>
                    </a>
                @endif

                @if($user->canAccess('requisitions.view'))
                    <a href="{{ route('requisitions') }}"
                       class="sidebar-link {{ request()->routeIs('requisitions') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Requisitions</span>
                    </a>
                @endif
            </div>

            <!-- BUSINESS -->
            <div class="sidebar-section">

                <div class="sidebar-title">
                    Business
                </div>

                @if($user->canAccess('customers.view'))
                    <a href="{{ route('customers') }}"
                       class="sidebar-link {{ request()->routeIs('customers') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span>Customers</span>
                    </a>
                @endif

                @if($user->canAccess('suppliers.view'))
                    <a href="{{ route('suppliers') }}"
                       class="sidebar-link {{ request()->routeIs('suppliers') ? 'active' : '' }}">
                        <i class="bi bi-truck"></i>
                        <span>Suppliers</span>
                    </a>
                @endif

                @if($user->canAccess('expenses.view'))
                    <a href="{{ route('expenses') }}"
                       class="sidebar-link {{ request()->routeIs('expenses') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i>
                        <span>Expenses</span>
                    </a>
                @endif

                @if($user->canAccess('reports.view_financial'))
                    <a href="{{ route('banking') }}"
                       class="sidebar-link {{ request()->routeIs('banking') ? 'active' : '' }}">
                        <i class="bi bi-bank"></i>
                        <span>Banking</span>
                    </a>
                @endif
            </div>

            <!-- ADMIN -->
            <div class="sidebar-section pb-4">

                <div class="sidebar-title">
                    Administration
                </div>

                @if($user->canAccess('users.view'))
                    <a href="{{ route('users') }}"
                       class="sidebar-link {{ request()->routeIs('users') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span>Users</span>
                    </a>
                @endif

                @if($user->canAccess('groups.view'))
                    <a href="{{ route('groups') }}"
                       class="sidebar-link {{ request()->routeIs('groups') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock"></i>
                        <span>Groups & Permissions</span>
                    </a>
                @endif

                @if($user->canAccess('settings.view'))
                    <a href="{{ route('settings') }}"
                       class="sidebar-link {{ request()->routeIs('settings') ? 'active' : '' }}">
                        <i class="bi bi-gear"></i>
                        <span>Settings</span>
                    </a>
                @endif
            </div>
        </aside>

        <!-- MAIN -->
        <main class="erp-main">

            <div class="erp-content">

                @if (isset($header))
                    <div class="mb-4">
                        {{ $header }}
                    </div>
                @endif

                @hasSection('content')

                    @yield('content')

                @else

                    {{ $slot }}

                @endif

            </div>
        </main>
    </div>
</div>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

@yield('scripts')

<script>

    /*
    |--------------------------------------------------------------------------
    | MOBILE SIDEBAR
    |--------------------------------------------------------------------------
    */

    const sidebar = document.getElementById('erpSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggle = document.getElementById('sidebarToggle');

    toggle?.addEventListener('click', () => {

        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    });

    overlay?.addEventListener('click', () => {

        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    });

    /*
    |--------------------------------------------------------------------------
    | SCROLL TO TOP
    |--------------------------------------------------------------------------
    */

    window.addEventListener('scrollToTop', () => {

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

</script>

@livewireScripts

</body>
</html>
