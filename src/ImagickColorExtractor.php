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
            // Each pixel counts by its opacity on GdColorExtractor's scale, 0
            // (fully transparent) to 127 (opaque), so both drivers weigh a
            // transparent image alike: an 8-bit alpha a rounds to a >> 1, the
            // value GD reads from the same PNG. Alpha is read normalised, from
            // 0.0 to 1.0 (1.0 without an alpha channel), because getColor()
            // truncates it to an integer, 0 for any partial transparency. A
            // pixel that rounds to 0 is skipped whatever RGB it holds; the
            // others add their pixel count times their opacity.
            $opacity = (int) round($pixel->getColor(1)['a'] * 127);
            if ($opacity === 0) {
                continue;
            }

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
            $colors[$key]['count'] += $pixel->getColorCount() * $opacity;
        }

        $clone->clear();

        return array_values($colors);
    }
}
