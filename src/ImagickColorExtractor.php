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
        if (!($image instanceof ImagickImage)) {
            throw new \InvalidArgumentException('ImagickColorExtractor requires ImagickImage instance');
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
            
            if (!isset($colors[$key])) {
                $colors[$key] = [
                    'r' => $rgb['r'],
                    'g' => $rgb['g'],
                    'b' => $rgb['b'],
                    'count' => 0
                ];
            }
            $colors[$key]['count'] += $pixel->getColorCount();
        }

        $clone->clear();
        return array_values($colors);
    }
}