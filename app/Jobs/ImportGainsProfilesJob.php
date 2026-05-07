<?php

namespace App\Jobs;

use App\Imports\GainsProfilesImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportGainsProfilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(
        public string $path,
        public bool $updateExisting = true,
        public bool $importDriveImages = true,
    ) {}

    public function handle(): void
    {
        $import = new GainsProfilesImport(
            updateExisting: $this->updateExisting,
            importDriveImages: $this->importDriveImages,
        );

        try {
            $absolutePath = $this->absolutePath();

            Excel::import($import, $absolutePath);

            Log::info('GAINS profiles import completed', [
                'path' => $this->path,
                'stats' => $import->stats(),
                'errors' => $import->errors(),
            ]);
        } finally {
            $this->deleteTemporaryFile();
        }
    }

    protected function deleteTemporaryFile(): void
    {
        if (Storage::disk('local')->exists($this->path)) {
            Storage::disk('local')->delete($this->path);
            Log::info('Đã dọn dẹp file import: ' . $this->path);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('GAINS profiles import failed', [
            'path' => $this->path,
            'message' => $exception->getMessage(),
        ]);
    }

    protected function absolutePath(): string
    {
        if (is_file($this->path)) {
            return $this->path;
        }

        return Storage::disk('local')->path($this->path);
    }
}
