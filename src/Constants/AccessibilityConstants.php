<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Constants;

/**
 * Accessibility and WCAG-related constants
 *
 * This class contains constants for color accessibility calculations,
 * including WCAG contrast ratios, luminance calculations, and brightness thresholds.
 */
class AccessibilityConstants
{
    /**
     * Brightness calculation coefficients (ITU-R BT.601 / NTSC standard)
     *
     * These coefficients represent the human eye's perception of RGB colors.
     * Green contributes most to perceived brightness, then red, then blue.
     *
     * @link https://en.wikipedia.org/wiki/Luma_(video)#Rec._601_luma_versus_Rec._709_luma_coefficients
     */
    public const BRIGHTNESS_RED_COEFFICIENT = 299;

    public const BRIGHTNESS_GREEN_COEFFICIENT = 587;

    public const BRIGHTNESS_BLUE_COEFFICIENT = 114;

    public const BRIGHTNESS_DIVISOR = 1000;

    /**
     * Brightness threshold for determining light vs dark colors
     *
     * Colors with brightness above this threshold are considered "light".
     * Value of 128 represents the midpoint of the 0-255 range.
     */
    public const BRIGHTNESS_THRESHOLD = 128;

    /**
     * Relative luminance coefficients (ITU-R BT.709 standard)
     *
     * Used for WCAG contrast ratio calculations. Different from brightness
     * as it accounts for gamma correction.
     *
     * @link https://www.w3.org/TR/WCAG20/#relativeluminancedef
     */
    public const LUMINANCE_RED_COEFFICIENT = 0.2126;

    public const LUMINANCE_GREEN_COEFFICIENT = 0.7152;

    public const LUMINANCE_BLUE_COEFFICIENT = 0.0722;

    /**
     * Gamma correction threshold for luminance calculation
     */
    public const LUMINANCE_GAMMA_THRESHOLD = 0.03928;

    public const LUMINANCE_GAMMA_DIVISOR = 12.92;

    public const LUMINANCE_GAMMA_OFFSET = 0.055;

    public const LUMINANCE_GAMMA_POWER = 2.4;

    public const LUMINANCE_GAMMA_MULTIPLIER = 1.055;

    /**
     * Contrast luminance offset for WCAG contrast ratio calculation
     *
     * This value is added to both luminance values before calculating the ratio
     * to avoid division by zero and to account for ambient light reflection.
     *
     * @link https://www.w3.org/TR/WCAG20/#contrast-ratiodef
     */
    public const CONTRAST_LUMINANCE_OFFSET = 0.05;

    /**
     * WCAG 2.0 Level AA contrast ratio requirements
     *
     * @link https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum.html
     */
    public const WCAG_AA_NORMAL_TEXT_RATIO = 4.5;

    public const WCAG_AA_LARGE_TEXT_RATIO = 3.0;

    /**
     * WCAG 2.0 Level AAA contrast ratio requirements
     *
     * @link https://www.w3.org/WAI/WCAG21/Understanding/contrast-enhanced.html
     */
    public const WCAG_AAA_NORMAL_TEXT_RATIO = 7.0;

    public const WCAG_AAA_LARGE_TEXT_RATIO = 4.5;
}
