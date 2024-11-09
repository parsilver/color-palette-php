<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorExtractorInterface;
use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Images\GdImage;
use Farzai\ColorPalette\Images\ImagickImage;

/**
 * Factory for creating appropriate color extractor instances
 */
class ColorExtractorFactory
{
    /**
     * Create color extractor based on image type
     */
    public static function createForImage(ImageInterface $image): ColorExtractorInterface
    {
        return match (true) {
            $image instanceof GdImage => new GdColorExtractor,
            $image instanceof ImagickImage => new ImagickColorExtractor,
            default => throw new \InvalidArgumentException('Unsupported image type'),
        };
    }
}
