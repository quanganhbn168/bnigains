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

class GainsProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\SpatieMediaLibraryImageColumn::make('avatar')
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
                    ->label('Chapter')
                    ->searchable(),
                TextColumn::make('is_public')
                    ->label('Public')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Public' : 'Private')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('slug')
                    ->label('Link Public')
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
                    ->modalHeading('QR Link Public')
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
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => GainsProfileResource::canDeleteAny()),
                ]),
            ]);
    }
}
