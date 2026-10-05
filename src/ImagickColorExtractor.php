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
     * Largest side an image is shrunk to before it is converted to sRGB
     */
    private const CONVERSION_SIZE = 200;

    private const SRGB_PROFILE = __DIR__.'/../resources/icc/sRGB2014.icc';

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
        // sRGB: a CMYK image would otherwise yield its C/M/Y values. Grayscale
        // already reads as r = g = b, so it is left alone.
        $colorspace = $clone->getImageColorspace();
        if ($colorspace !== \Imagick::COLORSPACE_SRGB && $colorspace !== \Imagick::COLORSPACE_GRAY) {
            // Converting a 48 MP image at full size costs hundreds of megabytes
            // and many seconds, but pixels averaged before conversion can land
            // well away from the same pixels averaged after it. Shrink part way,
            // convert, and leave the rest of the shrink to the sRGB resize below.
            $width = $clone->getImageWidth();
            $height = $clone->getImageHeight();
            if ($width > self::CONVERSION_SIZE || $height > self::CONVERSION_SIZE) {
                $clone->resizeImage(
                    min($width, self::CONVERSION_SIZE),
                    min($height, self::CONVERSION_SIZE),
                    \Imagick::FILTER_BOX,
                    1
                );
            }

            // A CMYK profile says what its inks really print as, which the
            // formula conversion ignores and can miss by over 20 dE00.
            // ImageMagick applies any embedded profile to CMYK pixels without
            // complaint, so one that is not CMYK is skipped. A profile it
            // cannot apply (corrupt, or no lcms delegate) leaves the image CMYK.
            $icc = $clone->getImageProfiles('icc')['icc'] ?? '';
            if ($colorspace === \Imagick::COLORSPACE_CMYK && substr($icc, 16, 4) === 'CMYK') {
                try {
                    $clone->profileImage('icc', self::srgbProfile());
                } catch (\ImagickException) {
                    // Still CMYK: converted by the formula below.
                }
            }

            // The formula conversion; a no-op once the profile has converted.
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

    /**
     * The ICC's sRGB profile (sRGB2014.icc), shipped in resources/icc. It is
     * only missing or empty if the package itself is broken.
     */
    private static function srgbProfile(): string
    {
        return file_get_contents(self::SRGB_PROFILE) ?: throw new \RuntimeException('Cannot read '.self::SRGB_PROFILE);
    }
}
