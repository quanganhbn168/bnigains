<?php

namespace App\Filament\Resources\GainsProfiles\Pages;

use App\Filament\Resources\GainsProfiles\GainsProfileResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditGainsProfile extends EditRecord
{
    protected static string $resource = GainsProfileResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return;
        }

        if ((int) $this->record->user_id !== (int) $user->id) {
            abort(403);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = User::find($data['user_id']);
        if ($user) {
            $data['user_email'] = $user->email;
        }

        // Gộp DB `address_1/address_2` -> form `address`
        $address1 = $data['address_1'] ?? null;
        $address2 = $data['address_2'] ?? null;
        $data['address'] = trim((string) $address1 . ($address2 ? ', ' . $address2 : ''));

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->user) {
            $this->record->user->update([
                'name' => $data['full_name'],
                'email' => $data['user_email'],
            ]);
        }

        // Tách form `address` -> DB `address_1/address_2`
        if (array_key_exists('address', $data)) {
            $data['address_1'] = $data['address'];
            $data['address_2'] = null;
            unset($data['address']);
        }

        unset($data['user_email']);

        return $data;
    }
}
