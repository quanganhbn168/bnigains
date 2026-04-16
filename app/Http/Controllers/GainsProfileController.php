<?php

namespace App\Http\Controllers;

use App\Models\GainsProfile;

class GainsProfileController extends Controller
{
    public function show(string $slug)
    {
        $profile = GainsProfile::where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        return view('profile.show', compact('profile'));
    }

    public function showByToken(string $qrToken)
    {
        $profile = GainsProfile::where('qr_token', $qrToken)
            ->where('is_public', true)
            ->firstOrFail();

        return view('profile.show', compact('profile'));
    }
}
