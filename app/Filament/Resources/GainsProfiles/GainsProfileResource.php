<?php

namespace App\Filament\Resources\GainsProfiles;

use App\Filament\Resources\GainsProfiles\Pages\CreateGainsProfile;
use App\Filament\Resources\GainsProfiles\Pages\EditGainsProfile;
use App\Filament\Resources\GainsProfiles\Pages\ListGainsProfiles;
use App\Filament\Resources\GainsProfiles\Schemas\GainsProfileForm;
use App\Filament\Resources\GainsProfiles\Tables\GainsProfilesTable;
use App\Models\GainsProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class GainsProfileResource extends Resource
{
    protected static ?string $model = GainsProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'full_name';

    protected static function isSuperAdmin(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user
            && $user->hasRole('super_admin');
    }

    public static function canCreate(): bool
    {
        return static::isSuperAdmin();
    }

    public static function canDelete($record): bool
    {
        return static::isSuperAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return static::isSuperAdmin();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        /** @var \App\Models\User $user */
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public static function form(Schema $schema): Schema
    {
        return GainsProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GainsProfilesTable::configure($table);
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
            'index' => ListGainsProfiles::route('/'),
            'create' => CreateGainsProfile::route('/create'),
            'edit' => EditGainsProfile::route('/{record}/edit'),
        ];
    }
}
