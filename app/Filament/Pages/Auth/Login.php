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
        $password = (string) ($data['password'] ?? '');
        $normalizedLogin = strtolower($login);

        // Tìm user theo username hoặc email trước (không phân biệt hoa thường)
        $user = User::whereRaw('LOWER(username) = ?', [$normalizedLogin])
            ->orWhereRaw('LOWER(email) = ?', [$normalizedLogin])
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
            if (!empty($user->email) && strtolower((string) $user->email) === $normalizedLogin) {
                return [
                    'email' => $user->email,
                    'password' => $password,
                ];
            }

            if (!empty($user->username) && strtolower((string) $user->username) === $normalizedLogin) {
                return [
                    'username' => $user->username,
                    'password' => $password,
                ];
            }

            if (!empty($user->phone) && (string) $user->phone === $login) {
                return [
                    'phone' => $user->phone,
                    'password' => $password,
                ];
            }

            return [
                // fallback cuối: ưu tiên email nếu có
                'email' => $user->email ?: $login,
                'password' => $password,
            ];
        }

        return [
            // fallback: vẫn cho phép thử theo email như flow mặc định
            'email' => $login,
            'password' => $password,
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}
