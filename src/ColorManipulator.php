<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Constants\ValidationConstants;
use Farzai\ColorPalette\Contracts\ColorInterface;

/**
 * Handles color manipulation operations
 *
 * Provides methods to lighten, darken, saturate, desaturate, and rotate colors
 * in the HSL color space.
 */
class ColorManipulator
{
    /**
     * Lighten a color by a given amount
     *
     * Increases the lightness component in HSL color space.
     *
     * @param  ColorInterface  $color  The color to lighten
     * @param  float  $amount  Amount to lighten (0.0 to 1.0, where 0.2 = 20%)
     * @return Color New color instance with increased lightness
     */
    public static function lighten(ColorInterface $color, float $amount): Color
    {
        $hsl = ColorSpaceConverter::toHsl($color);
        $hsl['l'] = min(100, $hsl['l'] + $amount * 100);

        return ColorSpaceConverter::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);
    }

    /**
     * Darken a color by a given amount
     *
     * Decreases the lightness component in HSL color space.
     *
     * @param  ColorInterface  $color  The color to darken
     * @param  float  $amount  Amount to darken (0.0 to 1.0, where 0.2 = 20%)
     * @return Color New color instance with decreased lightness
     */
    public static function darken(ColorInterface $color, float $amount): Color
    {
        $hsl = ColorSpaceConverter::toHsl($color);
        $hsl['l'] = max(0, $hsl['l'] - $amount * 100);

        return ColorSpaceConverter::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);
    }

    /**
     * Increase the saturation of a color
     *
     * Makes the color more vibrant by increasing the saturation component
     * in HSL color space.
     *
     * @param  ColorInterface  $color  The color to saturate
     * @param  float  $amount  Amount to saturate (0.0 to 1.0, where 0.2 = 20%)
     * @return Color New color instance with increased saturation
     */
    public static function saturate(ColorInterface $color, float $amount): Color
    {
        $hsl = ColorSpaceConverter::toHsl($color);
        $hsl['s'] = min(100, $hsl['s'] + $amount * 100);

        return ColorSpaceConverter::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);
    }

    /**
     * Decrease the saturation of a color
     *
     * Makes the color less vibrant (more gray) by decreasing the saturation
     * component in HSL color space.
     *
     * @param  ColorInterface  $color  The color to desaturate
     * @param  float  $amount  Amount to desaturate (0.0 to 1.0, where 0.2 = 20%)
     * @return Color New color instance with decreased saturation
     */
    public static function desaturate(ColorInterface $color, float $amount): Color
    {
        $hsl = ColorSpaceConverter::toHsl($color);
        $hsl['s'] = max(0, $hsl['s'] - $amount * 100);

        return ColorSpaceConverter::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);
    }

    /**
     * Rotate the hue of a color
     *
     * Shifts the color around the color wheel by the specified number of degrees.
     *
     * @param  ColorInterface  $color  The color to rotate
     * @param  float  $degrees  Degrees to rotate (can be negative for counter-clockwise rotation)
     * @return Color New color instance with rotated hue
     */
    public static function rotate(ColorInterface $color, float $degrees): Color
    {
        $hsl = ColorSpaceConverter::toHsl($color);
        $hsl['h'] = fmod(($hsl['h'] + $degrees + 360), 360);

        return ColorSpaceConverter::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);
    }

    /**
     * Set the lightness of a color to a specific value
     *
     * Replaces the lightness component in HSL color space with the given value.
     *
     * @param  ColorInterface  $color  The color to modify
     * @param  float  $lightness  New lightness value (0.0 to 1.0, where 0.5 = 50%)
     * @return Color New color instance with specified lightness
     */
    public static function withLightness(ColorInterface $color, float $lightness): Color
    {
        $hsl = ColorSpaceConverter::toHsl($color);

        return ColorSpaceConverter::fromHsl($hsl['h'], $hsl['s'], $lightness * 100);
    }

    /**
     * Set the saturation of a color to a specific value
     *
     * Replaces the saturation component in HSL color space with the given value.
     *
     * @param  ColorInterface  $color  The color to modify
     * @param  float  $saturation  New saturation value (0.0 to 1.0, where 0.5 = 50%)
     * @return Color New color instance with specified saturation
     */
    public static function withSaturation(ColorInterface $color, float $saturation): Color
    {
        $hsl = ColorSpaceConverter::toHsl($color);

        return ColorSpaceConverter::fromHsl($hsl['h'], $saturation * 100, $hsl['l']);
    }

    /**
     * Set the hue of a color to a specific value
     *
     * Replaces the hue component in HSL color space with the given value.
     *
     * @param  ColorInterface  $color  The color to modify
     * @param  float  $hue  New hue value (0-360 degrees)
     * @return Color New color instance with specified hue
     */
    public static function withHue(ColorInterface $color, float $hue): Color
    {
        $hsl = ColorSpaceConverter::toHsl($color);

        return ColorSpaceConverter::fromHsl($hue, $hsl['s'], $hsl['l']);
    }

    /**
     * Mix two colors together
     *
     * Creates a new color by blending two colors in the specified proportion.
     * Uses RGB color space for mixing.
     *
     * @param  ColorInterface  $color1  First color
     * @param  ColorInterface  $color2  Second color
     * @param  float  $weight  Weight of first color (0.0 to 1.0, where 0.5 = 50/50 mix)
     * @return Color New color instance representing the mix
     */
    public static function mix(ColorInterface $color1, ColorInterface $color2, float $weight = 0.5): Color
    {
        $weight = max(0, min(1, $weight)); // Clamp to 0-1 range

        $r = (int) round($color1->getRed() * $weight + $color2->getRed() * (1 - $weight));
        $g = (int) round($color1->getGreen() * $weight + $color2->getGreen() * (1 - $weight));
        $b = (int) round($color1->getBlue() * $weight + $color2->getBlue() * (1 - $weight));

        return new Color($r, $g, $b);
    }

    /**
     * Invert a color
     *
     * Creates the inverse/negative of a color by subtracting each RGB component
     * from the maximum value.
     *
     * @param  ColorInterface  $color  The color to invert
     * @return Color New color instance representing the inverse
     */
    public static function invert(ColorInterface $color): Color
    {
        return new Color(
            ValidationConstants::MAX_RGB_VALUE - $color->getRed(),
            ValidationConstants::MAX_RGB_VALUE - $color->getGreen(),
            ValidationConstants::MAX_RGB_VALUE - $color->getBlue()
        );
    }

    /**
     * Convert a color to grayscale
     *
     * Creates a grayscale version of the color by setting saturation to 0
     * in HSL color space.
     *
     * @param  ColorInterface  $color  The color to convert
     * @return Color New grayscale color instance
     */
    public static function grayscale(ColorInterface $color): Color
    {
        $hsl = ColorSpaceConverter::toHsl($color);

        return ColorSpaceConverter::fromHsl($hsl['h'], 0, $hsl['l']);
    }
}
