<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/paypal/pay', [PaymentController::class, 'payWithPayPal']);
Route::get('/paypal/status', [PaymentController::class, 'payPalStatus']);
