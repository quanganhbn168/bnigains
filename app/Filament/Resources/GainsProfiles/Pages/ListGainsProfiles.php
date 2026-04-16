<?php

namespace App\Filament\Resources\GainsProfiles\Pages;

use App\Filament\Resources\GainsProfiles\GainsProfileResource;
use App\Services\QuickCreateMemberService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListGainsProfiles extends ListRecords
{
    protected static string $resource = GainsProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => GainsProfileResource::canCreate()),
            Action::make('quickCreateMembers')
                ->label('Nhập nhanh thành viên')
                ->icon('heroicon-o-bolt')
                ->visible(fn (): bool => GainsProfileResource::canCreate())
                ->modalHeading('Nhập nhanh thành viên')
                ->modalDescription('Mỗi dòng là 1 thành viên. Hệ thống sẽ tự tạo User + Gains Profile.')
                ->form([
                    Textarea::make('member_names')
                        ->label('Danh sách thành viên')
                        ->rows(10)
                        ->required()
                        ->placeholder("Trần Quang Anh\nNguyễn Văn A\nPhạm Thị B"),
                ])
                ->action(function (array $data): void {
                    $names = preg_split('/\r\n|\r|\n/', (string) ($data['member_names'] ?? '')) ?: [];
                    $created = 0;
                    $failed = [];
                    $service = app(QuickCreateMemberService::class);

                    foreach ($names as $name) {
                        $name = trim($name);

                        if ($name === '') {
                            continue;
                        }

                        try {
                            $service->createFromFullName($name);
                            $created++;
                        } catch (Throwable $exception) {
                            $failed[] = $name;
                        }
                    }

                    if ($created > 0) {
                        Notification::make()
                            ->title("Đã tạo {$created} thành viên")
                            ->success()
                            ->send();
                    }

                    if (!empty($failed)) {
                        Notification::make()
                            ->title('Một số thành viên tạo thất bại')
                            ->body(implode(', ', $failed))
                            ->warning()
                            ->send();
                    }
                }),
        ];
    }
}
