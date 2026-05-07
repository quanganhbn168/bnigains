<?php

namespace App\Jobs;

use App\Actions\SaveGainsProfileAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessGainsWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800; // Cho thời gian tải ảnh thoải mái
    public int $tries = 1;

    // Nhận trực tiếp mảng JSON từ Controller
    public function __construct(
        public array $payload,
    ) {}

    public function handle(SaveGainsProfileAction $action): void
    {
        $status = $action->execute($this->payload, true, true);

        Log::info('Processed GAINS Google Forms webhook.', [
            'status' => $status,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Lỗi khi xử lý Webhook GAINS ngầm', [
            'message' => $exception->getMessage(),
            'payload' => $this->payload, // Log lại cục data để dễ debug
        ]);
    }
}
