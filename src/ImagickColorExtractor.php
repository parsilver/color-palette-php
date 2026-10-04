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
        // ImageInterface guarantees getResource(); an ImagickImage yields the
        // \Imagick handle used below. A mismatched image type surfaces as a
        // TypeError, which extract() turns into the grayscale fallback.
        $imagick = $image->getResource();

        // Resize image for faster processing
        $clone = clone $imagick;
        $clone->resizeImage(
            self::SAMPLE_SIZE,
            self::SAMPLE_SIZE,
            \Imagick::FILTER_BOX,
            1
        );

        // Pixel channels are read as r/g/b below, which is only meaningful in
        // sRGB: a CMYK image would otherwise yield its C/M/Y values. Converting
        // after the resize touches a 50x50 image instead of the full one, and
        // grayscale already reads as r = g = b, so it is left alone.
        $colorspace = $clone->getImageColorspace();
        if ($colorspace !== \Imagick::COLORSPACE_SRGB && $colorspace !== \Imagick::COLORSPACE_GRAY) {
            $clone->transformImageColorspace(\Imagick::COLORSPACE_SRGB);
        }

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
