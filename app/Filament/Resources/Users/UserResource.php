<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Tài khoản hệ thống';

    protected static ?string $modelLabel = 'Tài khoản';

    protected static ?string $pluralModelLabel = 'Tài khoản hệ thống';

    protected static UnitEnum|string|null $navigationGroup = 'Hệ thống';

    protected static ?string $recordTitleAttribute = 'name';

    protected static function canManageUsers(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check();
    }

    public static function canViewAny(): bool
    {
        return Auth::check();
    }

    public static function canCreate(): bool
    {
        return static::canManageUsers();
    }

    public static function canEdit($record): bool
    {
        /** @var User|null $authUser */
        $authUser = Auth::user();

        if (!$authUser) {
            return false;
        }

        return static::canManageUsers() || (int) $record->getKey() === (int) $authUser->getKey();
    }

    public static function canDelete($record): bool
    {
        return static::canManageUsers();
    }

    public static function canDeleteAny(): bool
    {
        return static::canManageUsers();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var User|null $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $query->whereRaw('1 = 0');
        }

        if (static::canManageUsers()) {
            return $query;
        }

        return $query->whereKey($authUser->getKey());
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
