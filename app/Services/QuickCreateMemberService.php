<?php

namespace App\Services;

use App\Models\GainsProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class QuickCreateMemberService
{
    public function createFromFullName(string $fullName): GainsProfile
    {
        $fullName = trim($fullName);

        if ($fullName === '') {
            throw new \InvalidArgumentException('Member name cannot be empty.');
        }

        return DB::transaction(function () use ($fullName): GainsProfile {
            $username = $this->generateUniqueUsername($fullName);
            $email = $this->generateUniqueEmail($username);

            $user = User::create([
                'name' => $fullName,
                'username' => $username,
                'email' => $email,
                'password' => Hash::make('bnikinhbac'),
            ]);

            return GainsProfile::create([
                'user_id' => $user->id,
                'full_name' => $fullName,
                'chapter_name' => 'BNI KINHBAC CHAPTER',
                'is_public' => true,
            ]);
        });
    }

    protected function generateUniqueUsername(string $fullName): string
    {
        $baseUsername = Str::slug($fullName, '');

        if ($baseUsername === '') {
            $baseUsername = 'user';
        }

        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter++;
        }

        return $username;
    }

    protected function generateUniqueEmail(string $username): string
    {
        $base = $username;
        $domain = 'bnigains.local';
        $email = $base . '@' . $domain;
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = $base . $counter++ . '@' . $domain;
        }

        return $email;
    }
}
