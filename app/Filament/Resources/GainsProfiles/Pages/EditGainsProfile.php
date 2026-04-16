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

        unset($data['user_email']);

        return $data;
    }
}
