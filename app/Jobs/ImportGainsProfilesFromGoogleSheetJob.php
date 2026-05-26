<?php

namespace App\Jobs;

use App\Imports\GainsProfilesImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Throwable;

class ImportGainsProfilesFromGoogleSheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(
        public string $sheetUrl,
        public bool $updateExisting = true,
        public bool $importDriveImages = true,
    ) {}

    public function handle(): void
    {
        $path = null;
        $import = new GainsProfilesImport(
            updateExisting: $this->updateExisting,
            importDriveImages: $this->importDriveImages,
        );

        try {
            $path = $this->downloadSheetCsv();

            Excel::import($import, Storage::disk('local')->path($path));

            Log::info('GAINS profiles Google Sheet sync completed', [
                'sheet_url' => $this->sheetUrl,
                'stats' => $import->stats(),
                'errors' => $import->errors(),
            ]);
        } finally {
            if ($path && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('GAINS profiles Google Sheet sync failed', [
            'sheet_url' => $this->sheetUrl,
            'message' => $exception->getMessage(),
        ]);
    }

    protected function downloadSheetCsv(): string
    {
        $errors = [];

        foreach ($this->csvExportUrls($this->sheetUrl) as $csvUrl) {
            $response = Http::timeout(120)
                ->retry(2, 500, throw: false)
                ->withOptions(['allow_redirects' => true])
                ->get($csvUrl);

            if (! $response->successful()) {
                $errors[] = $this->formatDownloadError($csvUrl, 'HTTP ' . $response->status());

                continue;
            }

            $body = $response->body();

            if ($body === '') {
                $errors[] = $this->formatDownloadError($csvUrl, 'file rỗng');

                continue;
            }

            if ($this->looksLikeHtml($body, (string) $response->header('Content-Type'))) {
                $errors[] = $this->formatDownloadError($csvUrl, 'trả HTML thay vì CSV');

                continue;
            }

            $path = 'imports/gains-profiles/google-sheets/' . (string) Str::uuid() . '.csv';

            Storage::disk('local')->put($path, $body);

            return $path;
        }

        throw new RuntimeException(
            'Không tải được CSV từ Google Sheet. Kiểm tra quyền chia sẻ public/publish CSV. Chi tiết: '
            . implode('; ', $errors)
        );
    }

    protected function formatDownloadError(string $url, string $message): string
    {
        return $message . ' tại ' . $url;
    }

    protected function csvExportUrls(string $url): array
    {
        $url = trim($url);

        if ($url === '') {
            throw new RuntimeException('Chưa nhập link Google Sheet.');
        }

        if (str_contains($url, 'output=csv') || str_contains($url, 'format=csv') || str_contains($url, 'tqx=out:csv')) {
            return [$url];
        }

        if (preg_match('~/spreadsheets/d/e/([^/]+)/(pubhtml|pub)~', $url, $matches)) {
            return [
                'https://docs.google.com/spreadsheets/d/e/' . $matches[1] . '/pub?output=csv',
            ];
        }

        if (! preg_match('~/spreadsheets/d/([^/]+)~', $url, $matches)) {
            return [$url];
        }

        $spreadsheetId = $matches[1];
        $gid = $this->queryValue($url, 'gid') ?? $this->fragmentValue($url, 'gid');

        $urls = [];

        if (filled($gid)) {
            $urls[] = 'https://docs.google.com/spreadsheets/d/' . $spreadsheetId . '/export?format=csv&gid=' . urlencode((string) $gid);
            $urls[] = 'https://docs.google.com/spreadsheets/d/' . $spreadsheetId . '/gviz/tq?tqx=out:csv&gid=' . urlencode((string) $gid);
        }

        $urls[] = 'https://docs.google.com/spreadsheets/d/' . $spreadsheetId . '/gviz/tq?tqx=out:csv';
        $urls[] = 'https://docs.google.com/spreadsheets/d/' . $spreadsheetId . '/export?format=csv';

        return array_values(array_unique($urls));
    }

    /**
     * @deprecated Use csvExportUrls() so the sync can fall back between Google CSV endpoints.
     */
    protected function toCsvExportUrl(string $url): string
    {
        return $this->csvExportUrls($url)[0];
    }

    protected function queryValue(string $url, string $key): ?string
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return filled($query[$key] ?? null) ? (string) $query[$key] : null;
    }

    protected function fragmentValue(string $url, string $key): ?string
    {
        parse_str((string) parse_url($url, PHP_URL_FRAGMENT), $fragment);

        return filled($fragment[$key] ?? null) ? (string) $fragment[$key] : null;
    }

    protected function looksLikeHtml(string $body, string $contentType): bool
    {
        $contentType = strtolower($contentType);
        $sample = strtolower(substr(ltrim($body), 0, 500));

        return str_contains($contentType, 'text/html')
            || str_starts_with($sample, '<!doctype html')
            || str_starts_with($sample, '<html')
            || str_contains($sample, 'accounts.google.com')
            || str_contains($sample, 'servicelogin');
    }
}
