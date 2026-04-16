<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\GainsProfile;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected array $profileData = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $profile = $this->record->gainsProfile;
        $data['profile_full_name'] = $profile?->full_name ?? $data['name'] ?? '';
        $data['profile_chapter_name'] = $profile?->chapter_name;
        $data['profile_is_public'] = $profile?->is_public ?? true;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->profileData = [
            'full_name' => $data['profile_full_name'] ?? $data['name'] ?? null,
            'chapter_name' => $data['profile_chapter_name'] ?? null,
            'is_public' => (bool) ($data['profile_is_public'] ?? true),
        ];

        unset($data['profile_full_name'], $data['profile_chapter_name'], $data['profile_is_public']);

        return $data;
    }

    protected function afterSave(): void
    {
        GainsProfile::updateOrCreate(
            ['user_id' => $this->record->getKey()],
            $this->profileData
        );
    }
}
