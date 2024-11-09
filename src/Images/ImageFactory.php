<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Images;

use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Exceptions\ImageException;

/**
 * Factory class for creating appropriate image instances
 */
class ImageFactory
{
    /**
     * Create an image instance from a file path
     *
     * @param  string  $preferredDriver  'gd' or 'imagick'
     *
     * @throws ImageException
     */
    public static function createFromPath(string $path, string $preferredDriver = 'gd'): ImageInterface
    {
        if ($preferredDriver === 'imagick' && extension_loaded('imagick')) {
            return ImagickImage::createFromPath($path);
        }

        if (! extension_loaded('gd')) {
            throw new ImageException('Neither GD nor Imagick extensions are available');
        }

        return GdImage::createFromPath($path);
    }
}
