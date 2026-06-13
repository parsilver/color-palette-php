<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Enums;

/**
 * Supported image-processing drivers.
 */
enum Driver: string
{
    case Gd = 'gd';

    case Imagick = 'imagick';

    /**
     * Normalize a Driver|string driver identifier to its string value.
     */
    public static function normalize(self|string $driver): string
    {
        return $driver instanceof self ? $driver->value : $driver;
    }
}
