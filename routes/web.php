<?php

use App\Http\Controllers\Central\LandlordController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central (Landlord) Web Routes
|--------------------------------------------------------------------------
| Loaded only for central_domains with 'web' middleware (session + CSRF).
| Tenant routes are in routes/tenant.php.
*/

Route::middleware('web')->group(function () {
    // Root: redirect to login or landlord dashboard if already logged in
    Route::get('/', function () {
        if (Auth::check()) {
            return redirect()->route('landlord.dashboard');
        }
        return redirect()->route('login');
    });

    Route::get('/welcome', function () {
        return view('central.welcome');
    })->name('central.welcome');

    // Auth routes for central (login, register, password reset)
    Auth::routes();

    // Landlord dashboard (protected) — POST must be in same web group for session/CSRF
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [LandlordController::class, 'index'])->name('landlord.dashboard');
        Route::get('/admin', [LandlordController::class, 'index'])->name('landlord.dashboard.alias');
        Route::post('/dashboard/schools', [LandlordController::class, 'store'])->name('landlord.store');
        Route::get('/dashboard/schools/{school}', [LandlordController::class, 'show'])->name('landlord.schools.show');
        Route::put('/dashboard/schools/{school}', [LandlordController::class, 'update'])->name('landlord.schools.update');
        Route::post('/dashboard/schools/{school}/toggle', [LandlordController::class, 'toggleStatus'])->name('landlord.schools.toggle');
        Route::delete('/dashboard/schools/{school}', [LandlordController::class, 'destroy'])->name('landlord.schools.destroy');
        Route::post('/dashboard/schools/{school}/reset-password', [LandlordController::class, 'resetPassword'])->name('landlord.schools.reset-password');
    });
});
