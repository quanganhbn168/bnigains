<?php

namespace App\Filament\Resources\GainsProfiles\Pages;

use App\Filament\Resources\GainsProfiles\GainsProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListGainsProfiles extends ListRecords
{
    protected static string $resource = GainsProfileResource::class;

    protected function getHeaderActions(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return [
            CreateAction::make()
                ->visible($user?->hasRole('super_admin') === true),
        ];
    }
}
