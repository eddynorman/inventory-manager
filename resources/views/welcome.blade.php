<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Welcome | Inventory & POS System</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .hero {
      background: url('https://source.unsplash.com/1600x900/?warehouse,store,inventory') no-repeat center center/cover;
      min-height: 100vh;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
    }
    .hero::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.65);
    }
    .hero-content {
      position: relative;
      text-align: center;
      z-index: 2;
    }
    .features .card {
      transition: all 0.3s ease-in-out;
    }
    .features .card:hover {
      transform: translateY(-5px);
      box-shadow: 0px 8px 20px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <h1 class="display-4 fw-bold">Inventory & POS System</h1>
      <p class="lead mb-4">Smart stock control and seamless sales management — all in one place.</p>
      <a href="{{ route('login') }}" class="btn btn-lg btn-primary">
        <i class="fas fa-sign-in-alt me-2"></i> Login
      </a>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features py-5 bg-light">
    <div class="container">
      <h2 class="text-center mb-5 fw-bold">System Features</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 text-center p-4">
            <i class="fas fa-boxes-stacked fa-3x text-primary mb-3"></i>
            <h5 class="fw-bold">Inventory Tracking</h5>
            <p class="text-muted">Keep track of stock levels, monitor usage, and get alerts for low inventory.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 text-center p-4">
            <i class="fas fa-cash-register fa-3x text-success mb-3"></i>
            <h5 class="fw-bold">Point of Sale (POS)</h5>
            <p class="text-muted">Process sales quickly with an integrated POS module for smooth transactions.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 text-center p-4">
            <i class="fas fa-chart-pie fa-3x text-warning mb-3"></i>
            <h5 class="fw-bold">Reports & Analytics</h5>
            <p class="text-muted">Generate detailed reports on sales, purchases, and inventory performance.</p>
          </div>
        </div>
      </div>
      <div class="row g-4 mt-1">
        <div class="col-md-4">
          <div class="card h-100 text-center p-4">
            <i class="fas fa-users fa-3x text-danger mb-3"></i>
            <h5 class="fw-bold">User Management</h5>
            <p class="text-muted">Manage staff roles, permissions, and access levels with ease.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 text-center p-4">
            <i class="fas fa-truck-loading fa-3x text-info mb-3"></i>
            <h5 class="fw-bold">Purchasing & Suppliers</h5>
            <p class="text-muted">Track purchase orders and supplier information for better procurement control.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 text-center p-4">
            <i class="fas fa-lock fa-3x text-secondary mb-3"></i>
            <h5 class="fw-bold">Secure & Reliable</h5>
            <p class="text-muted">Built with modern security practices and user authentication controls.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="erp-auth-page">

        {{-- HERO SECTION --}}
        <section class="erp-hero-section">

            <div class="container-fluid">

                <div class="row min-vh-100">

                    {{-- LEFT CONTENT --}}
                    <div class="col-lg-7 d-none d-lg-flex">

                        <div class="erp-hero-content w-100">

                            <div class="erp-brand-badge mb-4">
                                <i class="bi bi-boxes me-2"></i>
                                ERP INVENTORY PLATFORM
                            </div>

                            <h1 class="erp-hero-title">
                                Modern ERP & Inventory Management System
                            </h1>

                            <p class="erp-hero-subtitle">
                                Professional multi-location inventory, purchasing,
                                sales, requisitions, transfers, assets and ERP operations
                                management platform built for real-world business workflows.
                            </p>

                            {{-- LIVE STATS --}}
                            <div class="row g-4 mt-2">

                                <div class="col-md-4">

                                    <div class="erp-mini-stat">

                                        <div class="erp-mini-icon bg-primary-subtle text-primary">
                                            <i class="bi bi-box-seam"></i>
                                        </div>

                                        <div>
                                            <h4 class="fw-bold mb-1">
                                                {{ \App\Models\Item::count() }}
                                            </h4>

                                            <div class="small text-muted">
                                                Inventory Items
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-4">

                                    <div class="erp-mini-stat">

                                        <div class="erp-mini-icon bg-success-subtle text-success">
                                            <i class="bi bi-cart-check"></i>
                                        </div>

                                        <div>
                                            <h4 class="fw-bold mb-1">
                                                {{ \App\Models\Sale::count() }}
                                            </h4>

                                            <div class="small text-muted">
                                                Sales Transactions
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-4">

                                    <div class="erp-mini-stat">

                                        <div class="erp-mini-icon bg-warning-subtle text-warning">
                                            <i class="bi bi-bag-check"></i>
                                        </div>

                                        <div>
                                            <h4 class="fw-bold mb-1">
                                                {{ \App\Models\Purchase::count() }}
                                            </h4>

                                            <div class="small text-muted">
                                                Purchases
                                            </div>
                                        </div>

                                    </div>

                                </div>

                            </div>

                            {{-- FEATURES --}}
                            <div class="row g-4 mt-4">

                                <div class="col-md-6">

                                    <div class="erp-feature-card">

                                        <div class="erp-feature-icon text-primary">
                                            <i class="bi bi-diagram-3"></i>
                                        </div>

                                        <div>

                                            <h6 class="fw-bold mb-2">
                                                Multi-Location Inventory
                                            </h6>

                                            <p class="text-muted small mb-0">
                                                Manage inventory across multiple
                                                locations with real-time stock tracking.
                                            </p>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="erp-feature-card">

                                        <div class="erp-feature-icon text-success">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </div>

                                        <div>

                                            <h6 class="fw-bold mb-2">
                                                Transfers & Requisitions
                                            </h6>

                                            <p class="text-muted small mb-0">
                                                Internal requests, approvals,
                                                transfers and ERP operational workflows.
                                            </p>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="erp-feature-card">

                                        <div class="erp-feature-icon text-danger">
                                            <i class="bi bi-shield-lock"></i>
                                        </div>

                                        <div>

                                            <h6 class="fw-bold mb-2">
                                                Roles & Permissions
                                            </h6>

                                            <p class="text-muted small mb-0">
                                                Advanced user groups and
                                                permission-aware ERP navigation.
                                            </p>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="erp-feature-card">

                                        <div class="erp-feature-icon text-warning">
                                            <i class="bi bi-pc-display"></i>
                                        </div>

                                        <div>

                                            <h6 class="fw-bold mb-2">
                                                Asset Management
                                            </h6>

                                            <p class="text-muted small mb-0">
                                                Track business assets, costs,
                                                quantities and asset valuation.
                                            </p>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    {{-- LOGIN SIDE --}}
                    <div class="col-lg-5">

                        <div class="erp-login-wrapper">

                            <div class="erp-login-card">

                                {{-- LOGO --}}
                                <div class="text-center mb-4">

                                    <div class="erp-logo mx-auto mb-4">
                                        <i class="bi bi-boxes"></i>
                                    </div>

                                    <h2 class="fw-bold mb-2">
                                        Inventory Manager
                                    </h2>

                                    <p class="text-muted mb-0">
                                        Sign in to continue to the ERP platform
                                    </p>

                                </div>

                                {{-- SESSION STATUS --}}
                                @if (session('status'))

                                    <div class="alert alert-success rounded-4 border-0">

                                        {{ session('status') }}

                                    </div>

                                @endif

                                {{-- LOGIN FORM --}}
                                <form wire:submit="login">

                                    {{-- EMAIL --}}
                                    <div class="mb-4">

                                        <label class="form-label fw-semibold">
                                            Email Address
                                        </label>

                                        <div class="erp-input-group">

                                            <span>
                                                <i class="bi bi-envelope"></i>
                                            </span>

                                            <input
                                                wire:model="form.email"
                                                type="email"
                                                class="form-control erp-input"
                                                placeholder="Enter your email"
                                                required
                                                autofocus>

                                        </div>

                                        @error('form.email')

                                            <small class="text-danger">
                                                {{ $message }}
                                            </small>

                                        @enderror

                                    </div>

                                    {{-- PASSWORD --}}
                                    <div class="mb-4">

                                        <label class="form-label fw-semibold">
                                            Password
                                        </label>

                                        <div class="erp-input-group">

                                            <span>
                                                <i class="bi bi-lock"></i>
                                            </span>

                                            <input
                                                wire:model="form.password"
                                                type="password"
                                                class="form-control erp-input"
                                                placeholder="Enter your password"
                                                required>

                                        </div>

                                        @error('form.password')

                                            <small class="text-danger">
                                                {{ $message }}
                                            </small>

                                        @enderror

                                    </div>

                                    {{-- OPTIONS --}}
                                    <div class="d-flex justify-content-between align-items-center mb-4">

                                        <div class="form-check">

                                            <input
                                                wire:model="form.remember"
                                                class="form-check-input"
                                                type="checkbox"
                                                id="remember">

                                            <label class="form-check-label small">
                                                Remember me
                                            </label>

                                        </div>

                                        @if (Route::has('password.request'))

                                            <a href="{{ route('password.request') }}"
                                            class="small text-decoration-none fw-semibold">

                                                Forgot password?
                                            </a>

                                        @endif

                                    </div>

                                    {{-- LOGIN BUTTON --}}
                                    <button type="submit"
                                            class="btn erp-login-btn w-100">

                                        <i class="bi bi-box-arrow-in-right me-2"></i>

                                        Login To ERP

                                    </button>

                                </form>

                                {{-- FOOTER --}}
                                <div class="text-center mt-4">

                                    <div class="small text-muted">

                                        Secure ERP platform with role-based access control

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </div>
  </section>

  <!-- Footer -->
  <footer class="py-4 bg-dark text-white text-center">
    <p class="mb-0">&copy; {{ date('Y') }} Inventory & POS System. All rights reserved.</p>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
