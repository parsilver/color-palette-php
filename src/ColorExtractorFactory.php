<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use InvalidArgumentException;
use RuntimeException;

class ColorExtractorFactory
{
    /**
     * Create a new color extractor instance
     *
     * @throws InvalidArgumentException If an unsupported driver is specified
     * @throws RuntimeException If the required PHP extension is not available
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
     *
     * @throws RuntimeException If GD extension is not available
     */
    private function createGdExtractor(): GdColorExtractor
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('GD extension is not available. Please install or enable the GD extension.');
        }

        return new GdColorExtractor;
    }

    /**
     * Create Imagick color extractor
     *
     * @throws RuntimeException If Imagick extension is not available
     */
    private function createImagickExtractor(): ImagickColorExtractor
    {
        if (! extension_loaded('imagick')) {
            throw new RuntimeException('Imagick extension is not available. Please install or enable the Imagick extension.');
        }

        return new ImagickColorExtractor;
    }
}
