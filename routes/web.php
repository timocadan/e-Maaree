<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central (Landlord) Web Routes
|--------------------------------------------------------------------------
| Loaded only for central_domains. Tenant routes are in routes/tenant.php.
*/

Route::get('/', function () {
    return view('central.welcome');
});

Route::get('/debug-host', function () {
    return request()->getHost();
});
