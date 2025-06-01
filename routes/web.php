<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopeeAuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/shopee/auth/redirect', [ShopeeAuthController::class, 'redirect'])->name('shopee.auth.redirect');
Route::get('/shopee/auth/callback', [ShopeeAuthController::class, 'shopeeCallback'])->name('shopee.auth.callback');
