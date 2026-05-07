<?php

namespace App\Imports;

use App\Actions\SaveGainsProfileAction;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class GainsProfilesImport implements ToCollection, WithHeadingRow
{
    protected array $stats = [
        'total' => 0,
        'created' => 0,
        'updated' => 0,
        'duplicates' => 0,
        'errors' => 0,
        'success' => 0,
    ];

    protected array $errors = [];
    protected SaveGainsProfileAction $action;

    public function __construct(
        protected bool $updateExisting = true,
        protected bool $importDriveImages = true,
        ?SaveGainsProfileAction $action = null
    ) {
        // Gắn "Bộ não" Action vào đây
        $this->action = $action ?? app(SaveGainsProfileAction::class);
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $this->stats['total']++;

            try {
                // Vứt nguyên dòng dữ liệu cho Action xử lý
                $status = $this->action->execute(
                    $row->toArray(), 
                    $this->updateExisting, 
                    $this->importDriveImages
                );

                // Cập nhật thống kê dựa trên kết quả Action trả về
                if ($status === 'duplicate') {
                    $this->stats['duplicates']++;
                } elseif ($status === 'updated') {
                    $this->stats['updated']++;
                    $this->stats['success']++;
                } elseif ($status === 'created') {
                    $this->stats['created']++;
                    $this->stats['success']++;
                }

            } catch (\Throwable $e) {
                $this->stats['errors']++;
                $this->errors[] = [
                    'row' => $index + 2, // +2 do dòng 1 là Header Excel
                    'message' => $e->getMessage(),
                ];
            }
        }
    }

    public function stats(): array
    {
        return $this->stats;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}