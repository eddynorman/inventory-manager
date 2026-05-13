@extends('layouts.app')
@section('title','Restricted')
@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">

        <div class="card border-0 shadow-lg rounded-4 p-5 text-center"
            style="max-width: 500px;">

            <div class="mb-4">

                <div class="mx-auto d-flex align-items-center justify-content-center rounded-circle bg-warning-subtle"
                    style="width:90px; height:90px;">

                    <i class="bi bi-shield-lock fs-1 text-warning"></i>

                </div>

            </div>

            <h2 class="fw-bold mb-3">
                Access Restricted
            </h2>

            <p class="text-muted mb-4">
                Your account does not currently have permission
                to access the ERP dashboard.
                Please contact your administrator.
            </p>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button class="btn btn-dark rounded-3 px-4">
                    Logout
                </button>
            </form>

        </div>

    </div>
@endsection
