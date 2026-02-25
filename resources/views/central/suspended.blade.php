<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Suspended | e-maaree</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/brand.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Roboto, sans-serif; background: #1A1A1A; min-height: 100vh; display: flex; align-items: center; justify-content: center; color: #fff; }
        .suspended-card { background: #252525; border: 1px solid #333; border-radius: 8px; max-width: 480px; }
        .suspended-card h1 { color: #D32F2F; font-size: 1.5rem; }
        .suspended-card p { color: rgba(255,255,255,0.85); }
    </style>
</head>
<body>
    <div class="suspended-card p-5 text-center">
        <h1 class="mb-3">Subscription Expired / Account Suspended</h1>
        <p class="mb-0">Your subscription has expired or your account is suspended. Please contact <strong>ABQO Technology</strong> to restore access.</p>
    </div>
</body>
</html>
