<?php

namespace App\Filament\Resources\GainsProfiles\Pages;

use App\Filament\Resources\GainsProfiles\GainsProfileResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateGainsProfile extends CreateRecord
{
    protected static string $resource = GainsProfileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Tự sinh username từ full_name: "Trần Quang Anh" → "tranquanganh"
        $baseUsername = Str::slug($data['full_name'], '');
        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter++;
        }

        // Tạo User mới hoặc tìm User hiện có
        $user = User::firstOrCreate(
            ['email' => $data['user_email']],
            [
                'name' => $data['full_name'],
                'username' => $username,
                'password' => Hash::make('bnikinhbac'),
            ]
        );

        $data['user_id'] = $user->id;

        // Xoá trường ảo (không tồn tại trong DB)
        unset($data['user_email']);

        return $data;
    }
}
