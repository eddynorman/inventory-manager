<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- Bootstrap 5 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    </head>
    <body class="font-sans antialiased">
        <div class="min-vh-100 bg-light d-flex flex-column">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
                <div class="container-fluid">
                    <a class="navbar-brand" href="{{ route('dashboard') }}">Inventory Manager</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav" aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="topNav">
                        <ul class="navbar-nav me-auto"></ul>
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="me-2 bi bi-person-circle"></span>
                                    <span>{{ auth()->user()->name ?? 'User' }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('profile') }}">Profile</a></li>
                                    <li><a class="dropdown-item" href="{{ route('profile') }}#password">Change Password</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button class="dropdown-item" type="submit">Logout</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container-fluid flex-fill">
                <div class="row">
                    <aside class="col-12 col-md-3 col-lg-2 bg-white border-end min-vh-100 p-0">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                            <a href="{{ route('items') }}" class="list-group-item list-group-item-action {{ request()->routeIs('items') ? 'active' : '' }}">Items</a>
                            <a href="#" class="list-group-item list-group-item-action">Sales</a>
                            <a href="#" class="list-group-item list-group-item-action">Purchases</a>
                            <a href="#" class="list-group-item list-group-item-action">Receivings</a>
                            <a href="#" class="list-group-item list-group-item-action">Transfers</a>
                            <a href="#" class="list-group-item list-group-item-action">Issues</a>
                            <a href="#" class="list-group-item list-group-item-action">Requisitions</a>
                            <a href="{{ route('suppliers') }}" class="list-group-item list-group-item-action {{ request()->routeIs('suppliers') ? 'active' : '' }}">Suppliers</a>
                            <a href="{{ route('customers') }}" class="list-group-item list-group-item-action {{ request()->routeIs('customers') ? 'active' : '' }}">Customers</a>
                            <a href="#" class="list-group-item list-group-item-action">Stock Adjustments</a>
                            <a href="{{ route('categories') }}" class="list-group-item list-group-item-action {{ request()->routeIs('categories') ? 'active' : '' }}">Categories</a>
                            <a href="#" class="list-group-item list-group-item-action">Notifications</a>
                            <a href="#" class="list-group-item list-group-item-action">Settings</a>
                        </div>
                    </aside>
                    <main class="col-12 col-md-9 col-lg-10 py-3">
                        @if (isset($header))
                            <div class="mb-3">
                                {{ $header }}
                            </div>
                        @endif

                        @hasSection('content')
                            @yield('content')
                        @else
                            {{ $slot }}
                        @endif
                    </main>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tables = document.querySelectorAll('table.datatable');
                tables.forEach((t) => {
                    // Initialize via jQuery DataTables
                    window.jQuery && window.jQuery(t).DataTable();
                });
            });
        </script>
    </body>
</html>
