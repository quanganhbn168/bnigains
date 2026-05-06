<?php

namespace App\Jobs;

use App\Imports\GainsProfilesImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
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

        Excel::import($import, $this->path);

        Log::info('GAINS profiles import completed', [
            'path' => $this->path,
            'stats' => $import->stats(),
            'errors' => $import->errors(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('GAINS profiles import failed', [
            'path' => $this->path,
            'message' => $exception->getMessage(),
        ]);
    }
}