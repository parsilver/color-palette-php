<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Images\ImagickImage;

/**
 * Imagick implementation of ColorExtractor
 */
class ImagickColorExtractor extends AbstractColorExtractor
{
    /**
     * {@inheritdoc}
     */
    protected function extractColors(ImageInterface $image): array
    {
        /**
         * Note: This extractor is designed to work with ImagickImage instances.
         * The type check has been removed to reduce coupling, but proper
         * image/extractor pairing should be ensured by the factory.
         */
        if (! method_exists($image, 'getResource')) {
            throw new \InvalidArgumentException(
                'Image must provide getResource() method. '.
                'ImagickColorExtractor works with ImagickImage instances.'
            );
        }

        $imagick = $image->getResource();

        // Resize image for faster processing
        $clone = clone $imagick;
        $clone->resizeImage(
            self::SAMPLE_SIZE,
            self::SAMPLE_SIZE,
            \Imagick::FILTER_BOX,
            1
        );

        // Get color histogram
        $colors = [];
        $pixels = $clone->getImageHistogram();

        foreach ($pixels as $pixel) {
            $rgb = $pixel->getColor();
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
        }

        $clone->clear();

        return array_values($colors);
    }
}
