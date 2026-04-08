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

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsedPath = parse_url($path, PHP_URL_PATH);
            $path = is_string($parsedPath) ? $parsedPath : $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        return ltrim($path, '/');
    }
}
