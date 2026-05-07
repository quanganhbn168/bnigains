<?php

use App\Http\Controllers\Api\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function () {
    return response()->json([
        'trang_thai' => 'Thành công',
        'loi_nhan' => 'Hệ thống API đã sẵn sàng hoạt động!',
        'du_lieu' => [
            'phien_ban' => '1.0.0',
            'dich_vu' => 'Chăm sóc khách hàng'
        ]
    ]);
});

Route::post('/webhook/google-forms', [WebhookController::class, 'handleGoogleForm'])
    ->name('webhook.google-forms');
