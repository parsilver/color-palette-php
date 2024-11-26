<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Images\ImagickImage;
use Farzai\ColorPalette\Exceptions\ExtensionNotLoadedException;

/**
 * Imagick implementation of ColorExtractor
 */
class ImagickColorExtractor extends AbstractColorExtractor
{
    public function __construct()
    {
        if (!extension_loaded('imagick')) {
            throw new ExtensionNotLoadedException('The Imagick extension is required to use ImagickColorExtractor');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function extractColors(ImageInterface $image): array
    {
        if (! ($image instanceof ImagickImage)) {
            throw new \InvalidArgumentException('ImagickColorExtractor requires ImagickImage instance');
        }

        /** @var \Imagick $imagick */
        $imagick = $image->getResource();
        
        // Validate image dimensions
        if ($imagick->getImageWidth() === 0 || $imagick->getImageHeight() === 0) {
            return [];
        }

        try {
            // Resize image for faster processing
            /** @var \Imagick $clone */
            $clone = clone $imagick;
            $clone->resizeImage(
                self::SAMPLE_SIZE,
                self::SAMPLE_SIZE,
                \Imagick::FILTER_BOX,
                1
            );

            // Get color histogram
            $colors = [];
            /** @var \ImagickPixel[] $pixels */
            $pixels = $clone->getImageHistogram();

            foreach ($pixels as $pixel) {
                $rgb = $pixel->getColor();
                
                // Skip fully transparent pixels
                if (isset($rgb['a']) && $rgb['a'] === 0) {
                    continue;
                }
                
                $key = "{$rgb['r']},{$rgb['g']},{$rgb['b']}";

                if (! isset($colors[$key])) {
                    $colors[$key] = [
                        'r' => $rgb['r'],
                        'g' => $rgb['g'],
                        'b' => $rgb['b'],
                        'count' => 0,
                    ];
                }
                $colors[$key]['count'] += $pixel->getColorCount();
                
                // Clean up pixel object
                $pixel->destroy();
            }

            return array_values($colors);
        } finally {
            // Ensure clone is always destroyed
            if (isset($clone)) {
                $clone->clear();
                $clone->destroy();
            }
        }
    }
}
