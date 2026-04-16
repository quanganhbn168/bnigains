<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
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
                Section::make('Thông tin đăng nhập & Phân quyền')
                    ->description('Lưu ý: Nếu tài khoản này là Hội viên BNI, vui lòng ưu tiên cập nhật Tên, Email, SĐT tại trang "Hồ sơ GAINS". Trang này chủ yếu dùng để quản lý Mật khẩu và Vai trò (Role) của Quản trị viên/Trợ lý.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Họ tên')
                            ->required(),
                        TextInput::make('username')
                            ->label('Tên đăng nhập')
                            ->unique(ignoreRecord: true),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone')
                            ->label('Số điện thoại')
                            ->tel()
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Mật khẩu')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->helperText(fn(string $operation): string => $operation === 'edit' ? 'Để trống nếu không muốn đổi mật khẩu' : ''),
                        Select::make('roles')
                            ->label('Vai trò hệ thống')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload(),
                    ])->columns(2),
            ]);
    }
}
