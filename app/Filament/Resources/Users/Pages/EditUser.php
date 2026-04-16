<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\GainsProfile;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?int $selectedGainsProfileId = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['gains_profile_id'] = $this->record->gainsProfile?->getKey();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedGainsProfileId = isset($data['gains_profile_id']) ? (int) $data['gains_profile_id'] : null;

        unset($data['gains_profile_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        GainsProfile::query()
            ->where('user_id', $this->record->getKey())
            ->when($this->selectedGainsProfileId, fn ($query) => $query->whereKeyNot($this->selectedGainsProfileId))
            ->update(['user_id' => null]);

        if (!$this->selectedGainsProfileId) {
            return;
        }

        GainsProfile::whereKey($this->selectedGainsProfileId)->update([
            'user_id' => $this->record->getKey(),
        ]);
    }
}
