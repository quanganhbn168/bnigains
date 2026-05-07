<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessGainsWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleGoogleForm(Request $request): JsonResponse
    {
        $secretKey = (string) config('services.google_forms.webhook_secret');
        $clientKey = (string) $request->header('X-Secret-Key', '');

        if ($secretKey === '') {
            Log::error('Google Forms webhook secret is not configured.');

            return response()->json([
                'trang_thai' => 'Lỗi',
                'thong_bao' => 'Webhook chưa được cấu hình bảo mật.',
            ], 500);
        }

        if ($clientKey === '' || ! hash_equals($secretKey, $clientKey)) {
            Log::warning('Unauthorized Google Forms webhook request.', ['ip' => $request->ip()]);

            return response()->json([
                'trang_thai' => 'Lỗi',
                'thong_bao' => 'Truy cập bị từ chối.',
            ], 403);
        }

        $payload = $request->all();

        ProcessGainsWebhookJob::dispatch($payload);

        Log::info('Queued Google Forms webhook for GAINS profile import.', [
            'keys' => array_keys($payload),
        ]);

        return response()->json([
            'trang_thai' => 'Thành công',
            'thong_bao' => 'Đã nhận dữ liệu và đưa vào hàng đợi xử lý hồ sơ GAINS.',
        ], 202);
    }
}
