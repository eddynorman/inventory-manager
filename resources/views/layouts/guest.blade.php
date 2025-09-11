<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Inventory Manager') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .login-card {
            max-width: 40rem;
            width: 100%;
            border-radius: 0.5rem;
        }

        .login-header i {
            font-size: 4rem;
        }

        .login-header h2 {
            font-weight: 700;
            margin-top: 0.5rem;
        }

        .login-header p {
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light container">
    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div style="max-width: 30rem; width: 100%;">
            {{-- <div class="text-center mb-4 login-header">
                <i class="bi bi-person-circle text-primary"></i>
                <h2>{{ config('app.name', 'Inventory Manager') }}</h2>
                <p>Please log in to your account</p>
            </div> --}}

            {{ $slot }}
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
