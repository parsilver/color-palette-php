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

        $clone = clone $imagick;

        // Pixel channels are read as r/g/b below, which is only meaningful in
        // sRGB: a CMYK image would otherwise yield its C/M/Y values.
        if ($clone->getImageColorspace() !== \Imagick::COLORSPACE_SRGB) {
            $clone->transformImageColorspace(\Imagick::COLORSPACE_SRGB);
        }

        // Resize image for faster processing
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
