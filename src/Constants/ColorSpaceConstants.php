<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Constants;

/**
 * Color space conversion constants
 *
 * This class contains all constants related to color space conversions including
 * RGB, HSL, LAB, XYZ color spaces and their transformations.
 */
class ColorSpaceConstants
{
    /**
     * D65 illuminant reference white values for LAB color space conversion
     *
     * D65 represents standard daylight with a correlated color temperature of 6504K.
     * These values are the XYZ tristimulus values of the D65 white point.
     *
     * @link https://en.wikipedia.org/wiki/Illuminant_D65
     */
    public const D65_WHITE_X = 0.95047;

    public const D65_WHITE_Y = 1.00000;

    public const D65_WHITE_Z = 1.08883;

    /**
     * LAB color space conversion constants
     *
     * Epsilon and Kappa are used in the CIE L*a*b* color space conversion
     * to handle the nonlinear relationship between XYZ and LAB.
     */
    public const LAB_EPSILON = 0.008856; // (6/29)^3

    public const LAB_KAPPA = 903.3; // (29/3)^3

    public const LAB_OFFSET = 16;

    public const LAB_MULTIPLIER = 116;

    /**
     * Inverse kappa constant for LAB to XYZ conversion
     *
     * This is calculated as (29/6)^2 and is used in the reverse transformation
     * from LAB color space back to XYZ.
     */
    public const LAB_INVERSE_KAPPA = 7.787037037037037;

    /**
     * RGB to XYZ conversion matrix coefficients (sRGB with D65 illuminant)
     *
     * @link http://www.brucelindbloom.com/index.html?Eqn_RGB_XYZ_Matrix.html
     */
    public const RGB_TO_XYZ_RED_X = 0.4124564390896921;

    public const RGB_TO_XYZ_GREEN_X = 0.357576077643909;

    public const RGB_TO_XYZ_BLUE_X = 0.18043748326639894;

    public const RGB_TO_XYZ_RED_Y = 0.21267285140562253;

    public const RGB_TO_XYZ_GREEN_Y = 0.715152155287818;

    public const RGB_TO_XYZ_BLUE_Y = 0.07217499330655958;

    public const RGB_TO_XYZ_RED_Z = 0.019333895582329317;

    public const RGB_TO_XYZ_GREEN_Z = 0.119192025881303;

    public const RGB_TO_XYZ_BLUE_Z = 0.9503040785363677;

    /**
     * XYZ to RGB conversion matrix coefficients (sRGB with D65 illuminant)
     */
    public const XYZ_TO_RGB_X_RED = 3.2404542361916533;

    public const XYZ_TO_RGB_Y_RED = -1.5371385127253989;

    public const XYZ_TO_RGB_Z_RED = -0.4985314095560161;

    public const XYZ_TO_RGB_X_GREEN = -0.969266030505187;

    public const XYZ_TO_RGB_Y_GREEN = 1.8760108454795392;

    public const XYZ_TO_RGB_Z_GREEN = 0.04155601753034983;

    public const XYZ_TO_RGB_X_BLUE = 0.05564343095911469;

    public const XYZ_TO_RGB_Y_BLUE = -0.2040259135167538;

    public const XYZ_TO_RGB_Z_BLUE = 1.0572251882231791;

    /**
     * sRGB gamma correction constants
     *
     * Used for converting between linear RGB and gamma-corrected sRGB color spaces.
     */
    public const SRGB_GAMMA_THRESHOLD = 0.04045;

    public const SRGB_GAMMA_LINEAR_DIVISOR = 12.92;

    public const SRGB_GAMMA_OFFSET = 0.055;

    public const SRGB_GAMMA_MULTIPLIER = 1.055;

    public const SRGB_GAMMA_POWER = 2.4;

    public const SRGB_INVERSE_GAMMA_THRESHOLD = 0.0031308;

    /**
     * Hue to RGB conversion fractions
     *
     * These represent positions on the RGB color wheel for HSL/HSV conversion.
     */
    public const HUE_SECTOR_ONE_SIXTH = 1 / 6; // 60 degrees

    public const HUE_SECTOR_ONE_HALF = 1 / 2; // 180 degrees

    public const HUE_SECTOR_TWO_THIRDS = 2 / 3; // 240 degrees
}
