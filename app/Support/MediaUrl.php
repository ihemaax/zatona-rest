<?php

namespace App\Support;

class MediaUrl
{
    public static function fromPath(?string $path): ?string
    {
        $value = trim((string) ($path ?? ''));
        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        $normalized = ltrim(str_replace('\\', '/', $value), '/');
        foreach (['storage/app/public/', 'app/public/', 'public/', 'storage/'] as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = substr($normalized, strlen($prefix));
            }
        }

        return route('media.show', ['path' => ltrim($normalized, '/')]);
    }
}
