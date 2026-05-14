<?php

use Illuminate\Support\Facades\Route;
use LBHurtado\XRider\Http\Controllers\RiderRedirectController;
use LBHurtado\XRider\Http\Controllers\RiderSuccessPageController;

Route::get('{reference}/success', RiderSuccessPageController::class)->name('x-rider.success');
Route::get('{reference}/redirect', RiderRedirectController::class)->name('x-rider.redirect');
