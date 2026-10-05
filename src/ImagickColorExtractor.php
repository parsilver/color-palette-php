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
        // already reads as r = g = b, so it is left alone. An RGB image whose
        // profile is not sRGB, such as Adobe RGB or Display P3, is reported as
        // sRGB too, but its values mean other colours.
        $colorspace = $clone->getImageColorspace();
        $useProfile = self::hasConvertibleProfile($clone, $colorspace);
        if ($useProfile || ($colorspace !== \Imagick::COLORSPACE_SRGB && $colorspace !== \Imagick::COLORSPACE_GRAY)) {
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

            // A profile says what the values really mean, which the formula
            // conversion ignores: a CMYK press profile can move a colour by
            // over 20 dE00, and Adobe RGB or Display P3 values read as sRGB by
            // about as much. A profile ImageMagick cannot apply (corrupt, or no
            // lcms delegate) leaves the values as they were.
            if ($useProfile) {
                try {
                    $clone->profileImage('icc', self::srgbProfile());
                } catch (\ImagickException) {
                    // Unconverted: CMYK goes through the formula below, and RGB
                    // is read as sRGB.
                }
            }

            // The formula conversion; a no-op for sRGB, including once the
            // profile has converted.
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

    /**
     * Whether the image embeds an ICC profile that converting to sRGB through
     * would change its colours.
     *
     * ImageMagick applies a profile to pixels of any colour space without
     * complaint and returns nonsense, so the profile has to describe the
     * pixels: a CMYK profile for CMYK, an RGB one for RGB. Converting through
     * a profile byte-identical to the sRGB one converted to changes nothing
     * (ImageMagick skips it), so such an image stays on the plain sRGB path.
     */
    private static function hasConvertibleProfile(\Imagick $image, int $colorspace): bool
    {
        $icc = $image->getImageProfiles('icc')['icc'] ?? '';
        // The colour space of the profile's data, from its header.
        $profileSpace = substr($icc, 16, 4);

        return match ($colorspace) {
            \Imagick::COLORSPACE_CMYK => $profileSpace === 'CMYK',
            \Imagick::COLORSPACE_SRGB => $profileSpace === 'RGB ' && $icc !== self::srgbProfile(),
            default => false,
        };
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
