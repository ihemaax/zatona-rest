<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class MediaController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        $normalizedPath = ltrim(str_replace('\\', '/', urldecode($path)), '/');
        $disk = Storage::disk('public');
        $placeholderPath = public_path('images/placeholders/image-placeholder.svg');

        if ($normalizedPath === '' || str_contains($normalizedPath, '..')) {
            Log::warning('media.invalid_path', [
                'requested_path' => $path,
                'normalized_path' => $normalizedPath,
                'disk' => 'public',
            ]);

            return response()->file($placeholderPath, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'public, max-age=300',
            ]);
        }

        $absolutePath = $disk->path($normalizedPath);
        $exists = $disk->exists($normalizedPath);

        if (!$exists) {
            Log::warning('media.not_found', [
                'requested_path' => $path,
                'normalized_path' => $normalizedPath,
                'absolute_path' => $absolutePath,
                'disk' => 'public',
                'exists' => false,
            ]);

            return response()->file($placeholderPath, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'public, max-age=300',
            ]);
        }

        try {
            $mimeType = $disk->mimeType($normalizedPath) ?: 'application/octet-stream';

            return response()->file($absolutePath, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=604800',
            ]);
        } catch (Throwable $exception) {
            Log::error('media.serve_failed', [
                'requested_path' => $path,
                'normalized_path' => $normalizedPath,
                'absolute_path' => $absolutePath,
                'disk' => 'public',
                'exists' => $exists,
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->file($placeholderPath, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'public, max-age=60',
            ]);
        }
    }
}
