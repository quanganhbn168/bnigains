<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Models\GainsProfile;
use Illuminate\Validation\ValidationException;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use SensitiveParameter;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('Tên đăng nhập / SĐT / Email')
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        $login = trim((string) ($data['login'] ?? ''));

        // Tìm user theo username hoặc email trước
        $user = User::where('username', $login)
            ->orWhere('email', $login)
            ->orWhere('phone', $login)
            ->first();

        // Nếu không tìm thấy, tìm theo SĐT trong gains_profiles (có 2 SĐT)
        if (!$user) {
            $profile = GainsProfile::where('phone_cv', $login)
                ->orWhere('phone_personal', $login)
                ->first();

            if ($profile) {
                $user = $profile->user;
            }
        }

        if ($user) {
            return [
                'id' => $user->getAuthIdentifier(),
                'password' => $data['password'],
            ];
        }

        return [
            // fallback: vẫn cho phép thử theo email như flow mặc định
            'email' => $login,
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}
