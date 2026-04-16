<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class GainsProfile extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    protected $casts = [
        'social_links' => 'array',
        'is_public' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (GainsProfile $profile) {
            if (empty($profile->slug) && !empty($profile->full_name)) {
                $profile->slug = static::generateUniqueSlug($profile->full_name);
            }

            if (empty($profile->qr_token)) {
                $profile->qr_token = (string) Str::uuid();
            }
        });

        static::updating(function (GainsProfile $profile) {
            if ($profile->isDirty('full_name') && !empty($profile->full_name)) {
                $profile->slug = static::generateUniqueSlug($profile->full_name, $profile->id);
            }
        });
    }

    protected static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getThemeStyleAttribute(): string
    {
        $themeColors = [
            'red' => '--primary-color: #CC0000; --secondary-color: #990000;',
            'black' => '--primary-color: #333333; --secondary-color: #111111;',
            'beige' => '--primary-color: #D4AF37; --secondary-color: #AA8C2C;',
        ];

        return $themeColors[$this->theme_color ?? 'beige'] ?? $themeColors['beige'];
    }

    public function getPermanentUrlAttribute(): string
    {
        return route('profile.show.by-token', ['qrToken' => $this->qr_token]);
    }
}
