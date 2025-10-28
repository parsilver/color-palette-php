<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Constants;

/**
 * Color scheme and harmony constants
 *
 * This class contains constants for color wheel angles and relationships used
 * in generating harmonious color schemes (complementary, triadic, etc.).
 */
class ColorSchemeConstants
{
    /**
     * Color wheel angles for common color schemes
     *
     * These define the angular relationships between colors in various harmony schemes.
     */

    /**
     * Complementary color angle (opposite on color wheel)
     */
    public const COMPLEMENTARY_ANGLE = 180;

    /**
     * Triadic color angle (120 degrees apart, dividing the wheel into thirds)
     */
    public const TRIADIC_ANGLE = 120; // 360 / 3

    /**
     * Tetradic (square) color angle (90 degrees apart, dividing the wheel into quarters)
     */
    public const TETRADIC_ANGLE = 90; // 360 / 4

    /**
     * Pentadic color angle (72 degrees apart, dividing the wheel into fifths)
     */
    public const PENTADIC_ANGLE = 72; // 360 / 5

    /**
     * Analogous color angle (adjacent colors on the wheel)
     */
    public const ANALOGOUS_ANGLE = 30;

    /**
     * Split-complementary angles
     *
     * Split-complementary uses the two colors adjacent to the complement,
     * creating a more nuanced variation of the complementary scheme.
     */
    public const SPLIT_COMPLEMENTARY_ANGLE_1 = 150; // 180 - 30

    public const SPLIT_COMPLEMENTARY_ANGLE_2 = 210; // 180 + 30

    /**
     * Default manipulation step size for generating tints, shades, and tones
     *
     * This value is used as a multiplier when creating color variations.
     * A value of 0.8 means each step is 80% of the previous value.
     */
    public const DEFAULT_MANIPULATION_STEP = 0.8;
}
