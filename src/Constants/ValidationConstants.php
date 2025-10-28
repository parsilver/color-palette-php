<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Constants;

/**
 * Color value validation constants
 *
 * This class contains all constants related to validating color component values
 * across different color spaces (RGB, HSL, LAB, etc.).
 */
class ValidationConstants
{
    /**
     * RGB component value constraints
     *
     * RGB values must be integers between 0 and 255 (inclusive).
     */
    public const MIN_RGB_VALUE = 0;

    public const MAX_RGB_VALUE = 255;

    /**
     * Hue value constraints
     *
     * Hue is measured in degrees around the color wheel, from 0 to 360.
     * Note: 0 and 360 represent the same color (red).
     */
    public const HUE_MIN = 0;

    public const HUE_MAX = 360;

    /**
     * Percentage value constraints
     *
     * Used for saturation, lightness, and other percentage-based values.
     * Values range from 0% to 100%.
     */
    public const PERCENTAGE_MIN = 0;

    public const PERCENTAGE_MAX = 100;

    /**
     * LAB color space 'a' component constraints
     *
     * The 'a' component represents the green-red axis.
     * Negative values indicate green, positive values indicate red.
     */
    public const LAB_A_MIN = -128;

    public const LAB_A_MAX = 127;

    /**
     * LAB color space 'b' component constraints
     *
     * The 'b' component represents the blue-yellow axis.
     * Negative values indicate blue, positive values indicate yellow.
     */
    public const LAB_B_MIN = -128;

    public const LAB_B_MAX = 127;

    /**
     * LAB color space 'L' (lightness) component constraints
     *
     * The 'L' component represents lightness from 0 (black) to 100 (white).
     */
    public const LAB_L_MIN = 0;

    public const LAB_L_MAX = 100;

    /**
     * Float comparison epsilon
     *
     * Used for comparing floating point numbers where exact equality
     * cannot be guaranteed due to floating point precision limitations.
     *
     * Use this when comparing calculated float values to avoid false negatives
     * due to rounding errors (e.g., |a - b| < FLOAT_EPSILON instead of a == b).
     */
    public const FLOAT_EPSILON = 1e-6; // 0.000001
}
