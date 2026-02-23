<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central (Landlord) Web Routes
|--------------------------------------------------------------------------
| Loaded only for central_domains. Tenant routes are in routes/tenant.php.
*/

Route::get('/', function () {
    $host = parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost();
    return 'Central application. Use a tenant subdomain (e.g. school1.' . $host . ') to access a school.';
});

Route::get('/debug-host', function () {
    return request()->getHost();
});
