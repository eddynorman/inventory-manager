<!-- Flash Messages -->
<div id="flash-messages" style="position: fixed; top: 80px; right: 20px; z-index: 10000; max-width: 400px;">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show shadow" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>
<script>
    window.addEventListener('flash', () => {
        setTimeout(() => {
            const alerts = document.querySelectorAll('#flash-messages .alert');
            alerts.forEach(alert => {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            });
        }, 3000);
    });
</script>
