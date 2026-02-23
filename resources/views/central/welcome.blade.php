<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="ABQO Technology">
    <title>e-maaree – ABQO Technology</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/brand.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; margin: 0; min-height: 100vh; display: flex; flex-direction: column; background: #f5f5f5; }
        .hero { background: linear-gradient(135deg, #1A1A1A 0%, #333 100%); color: #fff; padding: 4rem 2rem; text-align: center; }
        .hero h1 { font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem; }
        .hero .tagline { color: #D32F2F; font-size: 1.25rem; font-weight: 500; margin-bottom: 1.5rem; }
        .hero p { color: rgba(255,255,255,0.9); max-width: 560px; margin: 0 auto 2rem; }
        .btn-hero { background: #D32F2F; color: #fff; border: none; padding: 0.75rem 1.5rem; border-radius: 4px; font-weight: 500; }
        .btn-hero:hover { background: #b71c1c; color: #fff; }
        .footer-central { background: #1A1A1A; color: rgba(255,255,255,0.8); padding: 1.5rem; text-align: center; margin-top: auto; font-size: 0.9rem; }
        .footer-central a { color: #D32F2F; }
    </style>
</head>
<body>
    <div class="hero">
        <h1>e-maaree</h1>
        <p class="tagline">School Management System</p>
        <p>Multi-tenant SaaS for schools. Each school runs on its own subdomain with full data isolation.</p>
        <p><small>Use your school subdomain to sign in (e.g. school1.{{ parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost() }})</small></p>
    </div>
    <div class="container py-5 text-center">
        <p class="text-muted">Powered by <strong>ABQO Technology</strong></p>
    </div>
    <footer class="footer-central">
        &copy; 2026 e-maaree by ABQO Technology. Developed by <a href="https://web.facebook.com/timocadaan" target="_blank" rel="noopener">Cumar Timocade</a>.
    </footer>
</body>
</html>
