<?php

namespace App\Support;

use App\Models\Setting;

class FrontThemeManager
{
    public static function all(): array
    {
        return config('front_themes.themes', []);
    }

    public static function fallbackKey(): string
    {
        return (string) config('front_themes.fallback', 'premium_slate');
    }

    public static function activeKey(?Setting $setting = null): string
    {
        $candidate = (string) ($setting?->front_theme ?? '');
        $themes = static::all();

        if ($candidate !== '' && isset($themes[$candidate])) {
            return $candidate;
        }

        $fallback = static::fallbackKey();

        return isset($themes[$fallback]) ? $fallback : (array_key_first($themes) ?? 'premium_slate');
    }

    public static function activeTheme(?Setting $setting = null): array
    {
        $key = static::activeKey($setting);

        return static::all()[$key] ?? [
            'name' => 'Default Theme',
            'description' => 'Fallback front theme.',
            'tokens' => [],
        ];
    }
}
