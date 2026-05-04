<?php

namespace App\Filament\Resources\GainsProfiles\Tables;

use App\Filament\Resources\GainsProfiles\GainsProfileResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Imports\GainsProfilesImport;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
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
                TextColumn::make('user.username')
                    ->label('Tài khoản')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email đăng nhập')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),
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
                TextColumn::make('company_name')
                    ->label('Công ty')
                    ->searchable(),
                TextColumn::make('job_title')
                    ->label('Chức danh')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

            \Filament\Forms\Components\Toggle::make('update_existing')
                ->label('Cập nhật hồ sơ đã tồn tại')
                ->helperText('Nếu bật, hệ thống sẽ bổ sung/cập nhật dữ liệu theo email hoặc số điện thoại.')
                ->default(true),

            \Filament\Forms\Components\Toggle::make('import_drive_images')
                ->label('Import ảnh từ Google Drive')
                ->helperText('Nếu bật, hệ thống sẽ tải ảnh Drive vào Media Library.')
                ->default(true),
        ])
        ->action(function (array $data): void {
            $path = Storage::disk('local')->path($data['file']);

            $import = new GainsProfilesImport(
                updateExisting: (bool) ($data['update_existing'] ?? true),
                importDriveImages: (bool) ($data['import_drive_images'] ?? true),
            );

            Excel::import($import, $path);

            $stats = $import->stats();

            $body = <<<HTML
Tổng dòng: {$stats['total']}<br>
Tạo mới: {$stats['created']}<br>
Cập nhật: {$stats['updated']}<br>
Trùng bỏ qua: {$stats['duplicates']}<br>
Lỗi: {$stats['errors']}<br>
Thành công: {$stats['success']}
HTML;

            Notification::make()
                ->title('Import hoàn tất')
                ->body($body)
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
