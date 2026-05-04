<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Họ tên')
                    ->searchable(),
                TextColumn::make('username')
                    ->label('Tên đăng nhập')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('SĐT')
                    ->searchable(),
                TextColumn::make('gainsProfile.full_name')
                    ->label('Profile đã liên kết')
                    ->placeholder('Chưa có profile')
                    ->searchable(),
                TextColumn::make('gainsProfile.slug')
                    ->label('Link hồ sơ')
                    ->placeholder('-')
                    ->url(fn ($record) => $record->gainsProfile?->public_url)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('roles.name')
                    ->label('Vai trò')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y')
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
                    ->visible(fn ($record): bool => filled($record->gainsProfile?->qr_token))
                    ->modalHeading('QR - Link công khai')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Đóng')
                    ->modalContent(fn ($record): HtmlString => new HtmlString(
                        '<div class="flex flex-col items-center gap-3">'
                        . QrCode::size(280)->margin(1)->generate($record->gainsProfile->public_url)
                        . '<p class="text-sm text-gray-600 break-all text-center">' . e($record->gainsProfile->public_url) . '</p>'
                        . '</div>'
                    )),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => UserResource::canDeleteAny()),
                ]),
            ]);
    }
}
