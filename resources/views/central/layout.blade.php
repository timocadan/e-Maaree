<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="ABQO Technology">
    <title>@yield('title', 'Landlord') | {{ config('app.name') }}</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/brand.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; min-height: 100vh; }
        .navbar-central { background: #1A1A1A !important; }
        .navbar-central .navbar-brand, .navbar-central .nav-link { color: #fff !important; }
        .navbar-central .nav-link:hover { color: #D32F2F !important; }
        .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .table th { border-top: none; font-weight: 600; color: #555; }
        .btn-brand { background: #D32F2F; color: #fff; border: none; }
        .btn-brand:hover { background: #b71c1c; color: #fff; }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-central">
        <div class="container">
            <a class="navbar-brand font-weight-bold" href="{{ route('landlord.dashboard') }}">e-maaree</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarLandlord">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarLandlord">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('landlord.dashboard') }}">Schools</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">{{ Auth::user()->name ?? 'User' }}</a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif
        @yield('content')
    </main>

    <script src="{{ asset('global_assets/js/main/jquery.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/main/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
