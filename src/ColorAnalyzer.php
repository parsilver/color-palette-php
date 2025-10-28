<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Constants\AccessibilityConstants;
use Farzai\ColorPalette\Constants\ValidationConstants;
use Farzai\ColorPalette\Contracts\ColorInterface;

/**
 * Handles color analysis operations
 *
 * Provides methods to analyze color properties such as brightness, luminance,
 * contrast ratios, and accessibility.
 */
class ColorAnalyzer
{
    /**
     * Calculate the perceived brightness of a color
     *
     * Uses the ITU-R BT.601 (NTSC) formula which weights RGB components based
     * on human eye sensitivity. This is a simpler calculation than luminance
     * and doesn't account for gamma correction.
     *
     * @param  ColorInterface  $color  The color to analyze
     * @return float Brightness value (0-255)
     *
     * @link https://en.wikipedia.org/wiki/Luma_(video)#Rec._601_luma_versus_Rec._709_luma_coefficients
     */
    public static function getBrightness(ColorInterface $color): float
    {
        return ($color->getRed() * AccessibilityConstants::BRIGHTNESS_RED_COEFFICIENT +
                $color->getGreen() * AccessibilityConstants::BRIGHTNESS_GREEN_COEFFICIENT +
                $color->getBlue() * AccessibilityConstants::BRIGHTNESS_BLUE_COEFFICIENT) /
               AccessibilityConstants::BRIGHTNESS_DIVISOR;
    }

    /**
     * Determine if a color is considered "light"
     *
     * A color is light if its brightness exceeds the threshold (128).
     * Useful for determining text color on colored backgrounds.
     *
     * @param  ColorInterface  $color  The color to check
     * @return bool True if the color is light, false if dark
     */
    public static function isLight(ColorInterface $color): bool
    {
        return self::getBrightness($color) > AccessibilityConstants::BRIGHTNESS_THRESHOLD;
    }

    /**
     * Determine if a color is considered "dark"
     *
     * A color is dark if its brightness is at or below the threshold (128).
     * Inverse of isLight().
     *
     * @param  ColorInterface  $color  The color to check
     * @return bool True if the color is dark, false if light
     */
    public static function isDark(ColorInterface $color): bool
    {
        return ! self::isLight($color);
    }

    /**
     * Calculate the relative luminance of a color
     *
     * Uses the ITU-R BT.709 standard with gamma correction as specified by WCAG.
     * This is the accurate method for calculating contrast ratios for accessibility.
     *
     * @param  ColorInterface  $color  The color to analyze
     * @return float Relative luminance value (0.0 to 1.0)
     *
     * @link https://www.w3.org/TR/WCAG20/#relativeluminancedef
     */
    public static function getLuminance(ColorInterface $color): float
    {
        $rgb = [$color->getRed(), $color->getGreen(), $color->getBlue()];
        $rgb = array_map(function ($value) {
            $value = $value / ValidationConstants::MAX_RGB_VALUE;

            return $value <= AccessibilityConstants::LUMINANCE_GAMMA_THRESHOLD
                ? $value / AccessibilityConstants::LUMINANCE_GAMMA_DIVISOR
                : pow(
                    ($value + AccessibilityConstants::LUMINANCE_GAMMA_OFFSET) / AccessibilityConstants::LUMINANCE_GAMMA_MULTIPLIER,
                    AccessibilityConstants::LUMINANCE_GAMMA_POWER
                );
        }, $rgb);

        return $rgb[0] * AccessibilityConstants::LUMINANCE_RED_COEFFICIENT +
               $rgb[1] * AccessibilityConstants::LUMINANCE_GREEN_COEFFICIENT +
               $rgb[2] * AccessibilityConstants::LUMINANCE_BLUE_COEFFICIENT;
    }

    /**
     * Calculate the WCAG contrast ratio between two colors
     *
     * The contrast ratio is defined by WCAG 2.0 as:
     * (L1 + 0.05) / (L2 + 0.05)
     * where L1 is the relative luminance of the lighter color
     * and L2 is the relative luminance of the darker color.
     *
     * WCAG 2.0 Level AA requires:
     * - 4.5:1 for normal text
     * - 3:1 for large text (18pt+)
     *
     * WCAG 2.0 Level AAA requires:
     * - 7:1 for normal text
     * - 4.5:1 for large text
     *
     * @param  ColorInterface  $color1  First color
     * @param  ColorInterface  $color2  Second color
     * @return float Contrast ratio (1.0 to 21.0)
     *
     * @link https://www.w3.org/TR/WCAG20/#contrast-ratiodef
     */
    public static function getContrastRatio(ColorInterface $color1, ColorInterface $color2): float
    {
        $l1 = self::getLuminance($color1) + AccessibilityConstants::CONTRAST_LUMINANCE_OFFSET;
        $l2 = self::getLuminance($color2) + AccessibilityConstants::CONTRAST_LUMINANCE_OFFSET;

        return $l1 > $l2 ? $l1 / $l2 : $l2 / $l1;
    }

    /**
     * Check if two colors meet WCAG AA contrast requirements
     *
     * @param  ColorInterface  $color1  First color (e.g., text color)
     * @param  ColorInterface  $color2  Second color (e.g., background color)
     * @param  bool  $largeText  Whether the text is large (18pt+ or 14pt+ bold)
     * @return bool True if contrast meets WCAG AA requirements
     */
    public static function meetsWCAG_AA(ColorInterface $color1, ColorInterface $color2, bool $largeText = false): bool
    {
        $contrastRatio = self::getContrastRatio($color1, $color2);
        $requiredRatio = $largeText ? AccessibilityConstants::WCAG_AA_LARGE_TEXT_RATIO : AccessibilityConstants::WCAG_AA_NORMAL_TEXT_RATIO;

        return $contrastRatio >= $requiredRatio;
    }

    /**
     * Check if two colors meet WCAG AAA contrast requirements
     *
     * @param  ColorInterface  $color1  First color (e.g., text color)
     * @param  ColorInterface  $color2  Second color (e.g., background color)
     * @param  bool  $largeText  Whether the text is large (18pt+ or 14pt+ bold)
     * @return bool True if contrast meets WCAG AAA requirements
     */
    public static function meetsWCAG_AAA(ColorInterface $color1, ColorInterface $color2, bool $largeText = false): bool
    {
        $contrastRatio = self::getContrastRatio($color1, $color2);
        $requiredRatio = $largeText ? AccessibilityConstants::WCAG_AAA_LARGE_TEXT_RATIO : AccessibilityConstants::WCAG_AAA_NORMAL_TEXT_RATIO;

        return $contrastRatio >= $requiredRatio;
    }

    /**
     * Get the distance between two colors in RGB space
     *
     * Uses Euclidean distance in RGB color space. This is a simple metric
     * but doesn't accurately reflect human perception of color difference.
     * For perceptual color difference, consider using deltaE with LAB colors.
     *
     * @param  ColorInterface  $color1  First color
     * @param  ColorInterface  $color2  Second color
     * @return float Distance value (0 to ~441)
     */
    public static function getRgbDistance(ColorInterface $color1, ColorInterface $color2): float
    {
        $dr = $color1->getRed() - $color2->getRed();
        $dg = $color1->getGreen() - $color2->getGreen();
        $db = $color1->getBlue() - $color2->getBlue();

        return sqrt($dr * $dr + $dg * $dg + $db * $db);
    }

    /**
     * Calculate Delta E (CIE76) color difference
     *
     * Delta E represents perceptual color difference in the LAB color space.
     * A Delta E less than 1.0 is generally considered imperceptible to the human eye.
     *
     * @param  ColorInterface  $color1  First color
     * @param  ColorInterface  $color2  Second color
     * @return float Delta E value (lower = more similar)
     *
     * @link https://en.wikipedia.org/wiki/Color_difference#CIE76
     */
    public static function getDeltaE(ColorInterface $color1, ColorInterface $color2): float
    {
        $lab1 = ColorSpaceConverter::toLab($color1);
        $lab2 = ColorSpaceConverter::toLab($color2);

        $dl = $lab1['l'] - $lab2['l'];
        $da = $lab1['a'] - $lab2['a'];
        $db = $lab1['b'] - $lab2['b'];

        return sqrt($dl * $dl + $da * $da + $db * $db);
    }

    /**
     * Determine if a color is vibrant
     *
     * A color is considered vibrant if it has high saturation.
     *
     * @param  ColorInterface  $color  The color to check
     * @param  int  $threshold  Minimum saturation to be considered vibrant (0-100, default 70)
     * @return bool True if the color is vibrant
     */
    public static function isVibrant(ColorInterface $color, int $threshold = 70): bool
    {
        $hsl = ColorSpaceConverter::toHsl($color);

        return $hsl['s'] >= $threshold;
    }

    /**
     * Determine if a color is muted/dull
     *
     * A color is considered muted if it has low saturation.
     *
     * @param  ColorInterface  $color  The color to check
     * @param  int  $threshold  Maximum saturation to be considered muted (0-100, default 30)
     * @return bool True if the color is muted
     */
    public static function isMuted(ColorInterface $color, int $threshold = 30): bool
    {
        $hsl = ColorSpaceConverter::toHsl($color);

        return $hsl['s'] <= $threshold;
    }

    /**
     * Determine if a color is a warm color
     *
     * Warm colors are red, orange, and yellow hues (roughly 0-60 degrees on the color wheel).
     *
     * @param  ColorInterface  $color  The color to check
     * @return bool True if the color is warm
     */
    public static function isWarm(ColorInterface $color): bool
    {
        $hsl = ColorSpaceConverter::toHsl($color);
        $hue = $hsl['h'];

        // Red to yellow range (0-60 degrees)
        return $hue >= 0 && $hue <= 60;
    }

    /**
     * Determine if a color is a cool color
     *
     * Cool colors are green, blue, and purple hues (roughly 120-300 degrees on the color wheel).
     *
     * @param  ColorInterface  $color  The color to check
     * @return bool True if the color is cool
     */
    public static function isCool(ColorInterface $color): bool
    {
        $hsl = ColorSpaceConverter::toHsl($color);
        $hue = $hsl['h'];

        // Green to purple range (120-300 degrees)
        return $hue >= 120 && $hue <= 300;
    }
}
