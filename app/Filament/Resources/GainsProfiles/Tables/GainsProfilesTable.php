<?php

namespace App\Filament\Resources\GainsProfiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                TextColumn::make('slug')
                    ->label('Link Public')
                    ->url(fn ($record) => url('/p/' . $record->slug))
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
