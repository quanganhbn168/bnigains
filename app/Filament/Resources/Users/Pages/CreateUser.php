<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\GainsProfile;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected array $profileData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->profileData = [
            'full_name' => $data['profile_full_name'] ?? $data['name'] ?? null,
            'chapter_name' => $data['profile_chapter_name'] ?? null,
            'is_public' => (bool) ($data['profile_is_public'] ?? true),
        ];

        unset($data['profile_full_name'], $data['profile_chapter_name'], $data['profile_is_public']);

        return $data;
    }

    protected function afterCreate(): void
    {
        GainsProfile::updateOrCreate(
            ['user_id' => $this->record->getKey()],
            $this->profileData
        );
    }
}
