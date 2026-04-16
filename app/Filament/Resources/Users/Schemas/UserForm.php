<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

                Section::make('Phân quyền')
                    ->schema([
                        Select::make('roles')
                            ->label('Vai trò hệ thống')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->columnSpanFull(),
                    ])->columns(12),

                Section::make('Hồ sơ GAINS cơ bản')
                    ->description('Hồ sơ sẽ được tạo/sync tự động theo user (mỗi user chỉ có 1 profile).')
                    ->schema([
                        TextInput::make('profile_full_name')
                            ->label('Tên hiển thị profile')
                            ->required()
                            ->columnSpan(6),
                        TextInput::make('profile_chapter_name')
                            ->label('Tên chapter')
                            ->columnSpan(6),
                        Toggle::make('profile_is_public')
                            ->label('Cho phép hiển thị public')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(6),
                    ])->columns(12),
            ]);
    }
}
