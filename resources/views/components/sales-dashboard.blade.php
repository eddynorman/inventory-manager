<div class="row mb-3">

    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-success text-white">
            <div class="card-body">
                <small>Total Revenue (Today)</small>
                <h4 class="mb-0">TZS {{ number_format($totalRevenue, 2) }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-primary text-white">
            <div class="card-body">
                <small>Total Paid</small>
                <h4 class="mb-0">TZS {{ number_format($totalPaid, 2) }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-danger text-white">
            <div class="card-body">
                <small>Total Unpaid</small>
                <h4 class="mb-0">TZS {{ number_format($totalUnpaid, 2) }}</h4>
            </div>
        </div>
    </div>

</div>
