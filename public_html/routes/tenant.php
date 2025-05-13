<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Ruta principal del tenant
    Route::get('/', function () {
        return view('tenant.dashboard', [
            'tenant' => tenant(),
            'users' => \App\Models\User::count()
        ]);
    });

    // Rutas de autenticación para el tenant
    Auth::routes();

    // Ejemplo de rutas adicionales específicas del tenant
    Route::get('/profile', function() {
        return view('tenant.profile', [
            'user' => auth()->user()
        ]);
    })->middleware('auth')->name('profile');

    // Puedes agregar más rutas específicas del tenant aquí
});
