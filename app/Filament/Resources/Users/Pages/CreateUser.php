<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\GainsProfile;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?int $selectedGainsProfileId = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedGainsProfileId = isset($data['gains_profile_id']) ? (int) $data['gains_profile_id'] : null;

        unset($data['gains_profile_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (!$this->selectedGainsProfileId) {
            return;
        }

        GainsProfile::query()
            ->where('user_id', $this->record->getKey())
            ->whereKeyNot($this->selectedGainsProfileId)
            ->update(['user_id' => null]);

        GainsProfile::whereKey($this->selectedGainsProfileId)->update([
            'user_id' => $this->record->getKey(),
        ]);
    }
}
