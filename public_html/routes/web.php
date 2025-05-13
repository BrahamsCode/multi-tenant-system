<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {

    });
}
