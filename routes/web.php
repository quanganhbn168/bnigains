<?php

use App\Http\Controllers\GainsProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route hiển thị hồ sơ public
Route::get('/p/{slug}', [GainsProfileController::class, 'show'])->name('profile.show');
Route::get('/qr/{qrToken}', [GainsProfileController::class, 'showByToken'])->name('profile.show.by-token');
