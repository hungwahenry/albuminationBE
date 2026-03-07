<?php

namespace App\Services;

class CoverArtService
{
    private const BASE_URL = 'https://coverartarchive.org/release-group';

    public static function url(string $mbid, string $size = 'small'): string
    {
        return match ($size) {
            'small' => self::BASE_URL . "/{$mbid}/front-250",
            'medium' => self::BASE_URL . "/{$mbid}/front-500",
            'large' => self::BASE_URL . "/{$mbid}/front-1200",
            'original' => self::BASE_URL . "/{$mbid}/front",
            default => self::BASE_URL . "/{$mbid}/front-250",
        };
    }
}
