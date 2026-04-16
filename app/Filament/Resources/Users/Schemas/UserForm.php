<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\GainsProfile;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tài khoản đăng nhập')
                    ->description('Thiết lập thông tin để user đăng nhập hệ thống.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Họ tên')
                            ->required()
                            ->columnSpan(6),
                        TextInput::make('username')
                            ->label('Tên đăng nhập')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(6),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(6),
                        TextInput::make('phone')
                            ->label('Số điện thoại')
                            ->tel()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(6),
                        TextInput::make('password')
                            ->label('Mật khẩu')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->helperText(fn(string $operation): string => $operation === 'edit' ? 'Để trống nếu không muốn đổi mật khẩu' : '')
                            ->columnSpan(6),
                    ])->columns(12),

                Section::make('Phân quyền & Liên kết hồ sơ')
                    ->description('Admin có thể gán user vào một hồ sơ GAINS để đồng bộ quản trị dễ hơn.')
                    ->schema([
                        Select::make('roles')
                            ->label('Vai trò hệ thống')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->columnSpan(6),
                        Select::make('gains_profile_id')
                            ->label('Hồ sơ GAINS liên kết')
                            ->searchable()
                            ->preload()
                            ->options(fn (?User $record) => GainsProfile::query()
                                ->when(
                                    $record,
                                    fn ($query) => $query->where(fn ($innerQuery) => $innerQuery
                                        ->whereNull('user_id')
                                        ->orWhere('user_id', $record->getKey())),
                                    fn ($query) => $query->whereNull('user_id')
                                )
                                ->orderByRaw('COALESCE(full_name, company_name, slug) ASC')
                                ->get()
                                ->mapWithKeys(fn (GainsProfile $profile) => [
                                    $profile->getKey() => $profile->full_name ?: ($profile->company_name ?: $profile->slug),
                                ])
                                ->toArray())
                            ->helperText('Mỗi hồ sơ GAINS chỉ nên liên kết với 1 tài khoản user.')
                            ->columnSpan(6),
                    ])->columns(12),
            ]);
    }
}
