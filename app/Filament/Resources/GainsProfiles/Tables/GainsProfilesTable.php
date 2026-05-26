<?php

namespace App\Filament\Resources\GainsProfiles\Tables;

use App\Filament\Resources\GainsProfiles\GainsProfileResource;
use App\Jobs\ImportGainsProfilesFromGoogleSheetJob;
use App\Jobs\ImportGainsProfilesJob;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GainsProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->collection('avatar')->circular(),
                TextColumn::make('full_name')
                    ->label('Họ và tên')
                    ->searchable(),
                TextColumn::make('chapter_name')
                    ->label('Tên Chapter')
                    ->searchable(),
                TextColumn::make('is_public')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Công khai' : 'Riêng tư')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('slug')
                    ->label('Link công khai')
                    ->url(fn ($record) => $record->public_url)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('showPermanentQr')
                    ->label('QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->modalHeading('QR - Link công khai')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Đóng')
                    ->modalContent(fn ($record): HtmlString => new HtmlString(
                        '<div class="flex flex-col items-center gap-3">'
                        . QrCode::size(280)->margin(1)->generate($record->public_url)
                        . '<p class="text-sm text-gray-600 break-all text-center">' . e($record->public_url) . '</p>'
                        . '</div>'
                    )),
                EditAction::make(),
            ])
            ->toolbarActions([
                Action::make('importGainsProfiles')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->modalHeading('Import hồ sơ GAINS từ Excel')
                    ->form([
                        FileUpload::make('file')
                            ->label('File Excel')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                            ])
                            ->directory('imports/gains-profiles')
                            ->disk('local')
                            ->required(),

                        Toggle::make('update_existing')
                            ->label('Cập nhật hồ sơ đã tồn tại')
                            ->helperText('Nếu bật, hệ thống sẽ bổ sung/cập nhật dữ liệu theo email hoặc số điện thoại.')
                            ->default(true),

                        Toggle::make('import_drive_images')
                            ->label('Import ảnh từ Google Drive')
                            ->helperText('Nếu bật, hệ thống sẽ tải ảnh Drive vào Media Library.')
                            ->default(true),
                    ])
                    ->action(function (array $data): void {
                        $relativePath = is_array($data['file']) ? reset($data['file']) : $data['file'];

                        ImportGainsProfilesJob::dispatch(
                            path: $relativePath,
                            updateExisting: (bool) ($data['update_existing'] ?? true),
                            importDriveImages: (bool) ($data['import_drive_images'] ?? true),
                        );

                        Notification::make()
                            ->title('Đã đưa file vào hàng đợi import')
                            ->body('Hệ thống sẽ xử lý import ở nền. Anh có thể tải lại trang sau vài phút để kiểm tra kết quả.')
                            ->success()
                            ->send();
                    }),
                Action::make('sync')
                    ->label('Đồng bộ Google Sheet')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->modalHeading('Đồng bộ hồ sơ GAINS từ Google Sheet')
                    ->form([
                        TextInput::make('sheet_url')
                            ->label('Link Google Sheet')
                            ->placeholder('https://docs.google.com/spreadsheets/d/.../edit#gid=0')
                            ->helperText('Sheet cần được chia sẻ public hoặc publish/export CSV để hệ thống tải được dữ liệu.')
                            ->default((string) config('services.google_sheets.gains_profiles_url'))
                            ->url()
                            ->required()
                            ->columnSpanFull(),

                        Toggle::make('update_existing')
                            ->label('Cập nhật hồ sơ đã tồn tại')
                            ->helperText('Nếu bật, hệ thống sẽ bổ sung/cập nhật dữ liệu theo email hoặc số điện thoại.')
                            ->default(true),

                        Toggle::make('import_drive_images')
                            ->label('Import ảnh từ Google Drive')
                            ->helperText('Nếu bật, hệ thống sẽ tải ảnh Drive vào Media Library.')
                            ->default(true),
                    ])
                    ->action(function (array $data): void {
                        ImportGainsProfilesFromGoogleSheetJob::dispatch(
                            sheetUrl: (string) $data['sheet_url'],
                            updateExisting: (bool) ($data['update_existing'] ?? true),
                            importDriveImages: (bool) ($data['import_drive_images'] ?? true),
                        );

                        Notification::make()
                            ->title('Đã đưa Google Sheet vào hàng đợi đồng bộ')
                            ->body('Worker queue sẽ tải sheet và import dữ liệu ở nền. Anh có thể tải lại trang sau vài phút để kiểm tra kết quả.')
                            ->success()
                            ->send();
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => GainsProfileResource::canDeleteAny()),
                ]),
            ]);
    }
}
