<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function show(string $path): Response
    {
        $normalizedPath = ltrim(urldecode($path), '/');

        abort_if($normalizedPath === '' || str_contains($normalizedPath, '..'), 404);
        abort_unless(Storage::disk('public')->exists($normalizedPath), 404);

        $absolutePath = Storage::disk('public')->path($normalizedPath);
        $mimeType = Storage::disk('public')->mimeType($normalizedPath) ?: 'application/octet-stream';

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
