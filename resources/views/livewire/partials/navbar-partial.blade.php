<div>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Inventory Manager</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav"
                    aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list text-white fs-3"></i>
            </button>

            <div class="collapse navbar-collapse" id="topNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-2"></i>
                            <span>{{ $user->name ?? 'User' }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
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
</div>
