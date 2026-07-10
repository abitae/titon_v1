<?php

namespace App\Support;

class PublicStorageUrl
{
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $normalizedPath = str_replace('\\', '/', $path);

        return '/storage/'.ltrim($normalizedPath, '/');
    }
}
