<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use InvalidArgumentException;

class ColorExtractorFactory
{
    /**
     * Create a new color extractor instance
     *
     * @throws InvalidArgumentException
     */
    public function make(string $driver = 'gd'): AbstractColorExtractor
    {
        return match ($driver) {
            'gd' => $this->createGdExtractor(),
            'imagick' => $this->createImagickExtractor(),
            default => throw new InvalidArgumentException("Unsupported driver: {$driver}"),
        };
    }

    /**
     * Create GD color extractor
     */
    private function createGdExtractor(): GdColorExtractor
    {
        if (! extension_loaded('gd')) {
            throw new InvalidArgumentException('GD extension is not available');
        }

        return new GdColorExtractor;
    }

    /**
     * Create Imagick color extractor
     */
    private function createImagickExtractor(): ImagickColorExtractor
    {
        if (! extension_loaded('imagick')) {
            throw new InvalidArgumentException('Imagick extension is not available');
        }

        return new ImagickColorExtractor;
    }
}
