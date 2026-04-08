<?php

namespace App\Models\Concerns;

trait NormalizesMediaPath
{
    protected function normalizeMediaPath(mixed $value): ?string
    {
        $path = trim((string) ($value ?? ''));
        if ($path === '') {
            return null;
        }

        $path = str_replace('\\', '/', $path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $mediaHost = parse_url($path, PHP_URL_HOST);
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

            if ($mediaHost && $appHost && strcasecmp($mediaHost, $appHost) !== 0) {
                return $path;
            }

            $parsedPath = parse_url($path, PHP_URL_PATH);
            $path = is_string($parsedPath) ? $parsedPath : $path;
        }

        $path = ltrim($path, '/');

        foreach (['storage/app/public/', 'app/public/', 'public/', 'storage/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
            }
        }

        return ltrim($path, '/');
    }
}
