<?php

namespace App\Http\Controllers;

use App\Models\GainsProfile;
use Illuminate\Http\Request;

class GainsProfileController extends Controller
{
    public function show($slug)
    {
        $profile = GainsProfile::where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        return view('profile.show', compact('profile'));
    }
}
