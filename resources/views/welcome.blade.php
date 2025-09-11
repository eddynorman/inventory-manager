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
  </section>

  <!-- Footer -->
  <footer class="py-4 bg-dark text-white text-center">
    <p class="mb-0">&copy; {{ date('Y') }} Inventory & POS System. All rights reserved.</p>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
