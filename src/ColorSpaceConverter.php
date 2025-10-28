<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Constants\ColorSpaceConstants;
use Farzai\ColorPalette\Constants\ValidationConstants;
use Farzai\ColorPalette\Contracts\ColorInterface;
use InvalidArgumentException;

/**
 * Handles conversions between different color spaces
 *
 * Supports conversion between RGB and:
 * - HSL (Hue, Saturation, Lightness)
 * - HSV/HSB (Hue, Saturation, Value/Brightness)
 * - CMYK (Cyan, Magenta, Yellow, Key/Black)
 * - LAB (CIE L*a*b*)
 */
class ColorSpaceConverter
{
    /**
     * Convert RGB color to HSL color space
     *
     * @param  ColorInterface  $color  The color to convert
     * @return array{h: int, s: int, l: int} Hue (0-360), Saturation (0-100), Lightness (0-100)
     */
    public static function toHsl(ColorInterface $color): array
    {
        $r = $color->getRed() / ValidationConstants::MAX_RGB_VALUE;
        $g = $color->getGreen() / ValidationConstants::MAX_RGB_VALUE;
        $b = $color->getBlue() / ValidationConstants::MAX_RGB_VALUE;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = $s = $l = ($max + $min) / 2;

        if (abs($max - $min) < ValidationConstants::FLOAT_EPSILON) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
            }

            $h = round($h * 60);
            if ($h < 0) {
                $h += ValidationConstants::HUE_MAX;
            }
        }

        return [
            'h' => (int) $h,
            's' => (int) round($s * ValidationConstants::PERCENTAGE_MAX),
            'l' => (int) round($l * ValidationConstants::PERCENTAGE_MAX),
        ];
    }

    /**
     * Create a Color instance from HSL values
     *
     * @param  float  $hue  Hue (0-360 degrees)
     * @param  float  $saturation  Saturation (0-100%)
     * @param  float  $lightness  Lightness (0-100%)
     */
    public static function fromHsl(float $hue, float $saturation, float $lightness): Color
    {
        // Convert HSL to RGB
        $h = $hue / ValidationConstants::HUE_MAX;
        $s = $saturation / ValidationConstants::PERCENTAGE_MAX;
        $l = $lightness / ValidationConstants::PERCENTAGE_MAX;

        if (abs($s) < ValidationConstants::FLOAT_EPSILON) {
            $r = $g = $b = (int) round($l * ValidationConstants::MAX_RGB_VALUE);

            return new Color($r, $g, $b);
        }

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        $r = self::hueToRgb($p, $q, $h + ColorSpaceConstants::HUE_SECTOR_ONE_SIXTH * 2);
        $g = self::hueToRgb($p, $q, $h);
        $b = self::hueToRgb($p, $q, $h - ColorSpaceConstants::HUE_SECTOR_ONE_SIXTH * 2);

        return new Color(
            (int) round($r * ValidationConstants::MAX_RGB_VALUE),
            (int) round($g * ValidationConstants::MAX_RGB_VALUE),
            (int) round($b * ValidationConstants::MAX_RGB_VALUE)
        );
    }

    /**
     * Helper function for HSL to RGB conversion
     *
     * @param  float  $p  Lower RGB value
     * @param  float  $q  Upper RGB value
     * @param  float  $t  Hue sector
     * @return float RGB component value (0-1)
     */
    private static function hueToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < ColorSpaceConstants::HUE_SECTOR_ONE_SIXTH) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < ColorSpaceConstants::HUE_SECTOR_ONE_HALF) {
            return $q;
        }
        if ($t < ColorSpaceConstants::HUE_SECTOR_TWO_THIRDS) {
            return $p + ($q - $p) * (ColorSpaceConstants::HUE_SECTOR_TWO_THIRDS - $t) * 6;
        }

        return $p;
    }

    /**
     * Convert RGB color to HSV/HSB color space
     *
     * @param  ColorInterface  $color  The color to convert
     * @return array{h: int, s: int, v: int} Hue (0-360), Saturation (0-100), Value (0-100)
     */
    public static function toHsv(ColorInterface $color): array
    {
        $r = $color->getRed() / ValidationConstants::MAX_RGB_VALUE;
        $g = $color->getGreen() / ValidationConstants::MAX_RGB_VALUE;
        $b = $color->getBlue() / ValidationConstants::MAX_RGB_VALUE;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $v = $max;
        $d = $max - $min;
        $s = abs($max) < ValidationConstants::FLOAT_EPSILON ? 0 : $d / $max;

        if (abs($max - $min) < ValidationConstants::FLOAT_EPSILON) {
            $h = 0;
        } else {
            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
                default:
                    $h = 0;
            }
            $h = round($h * 60);
        }

        return [
            'h' => (int) $h,
            's' => (int) round($s * ValidationConstants::PERCENTAGE_MAX),
            'v' => (int) round($v * ValidationConstants::PERCENTAGE_MAX),
        ];
    }

    /**
     * Convert RGB values to HSB (same as HSV, different name)
     *
     * @param  int  $r  Red (0-255)
     * @param  int  $g  Green (0-255)
     * @param  int  $b  Blue (0-255)
     * @return array{h: float, s: float, b: float} Hue (0-360), Saturation (0-1), Brightness (0-1)
     */
    public static function rgbToHsb(int $r, int $g, int $b): array
    {
        $r = $r / ValidationConstants::MAX_RGB_VALUE;
        $g = $g / ValidationConstants::MAX_RGB_VALUE;
        $b = $b / ValidationConstants::MAX_RGB_VALUE;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;

        // Calculate brightness
        $brightness = $max;

        // Calculate saturation
        $saturation = abs($max) < ValidationConstants::FLOAT_EPSILON ? 0 : $delta / $max;

        // Calculate hue
        $hue = 0;
        if (abs($delta) > ValidationConstants::FLOAT_EPSILON) {
            if (abs($max - $r) < ValidationConstants::FLOAT_EPSILON) {
                $hue = (($g - $b) / $delta) + ($g < $b ? 6 : 0);
            } elseif (abs($max - $g) < ValidationConstants::FLOAT_EPSILON) {
                $hue = (($b - $r) / $delta) + 2;
            } else {
                $hue = (($r - $g) / $delta) + 4;
            }
            $hue = $hue * 60;
        }

        return [
            'h' => $hue,
            's' => $saturation,
            'b' => $brightness,
        ];
    }

    /**
     * Create a Color instance from HSV values
     *
     * @param  float  $hue  Hue (0-360 degrees)
     * @param  float  $saturation  Saturation (0-100%)
     * @param  float  $value  Value/Brightness (0-100%)
     *
     * @throws InvalidArgumentException If values are out of range
     */
    public static function fromHsv(float $hue, float $saturation, float $value): Color
    {
        // Validate HSV values
        if ($hue < ValidationConstants::HUE_MIN || $hue >= ValidationConstants::HUE_MAX) {
            throw new InvalidArgumentException('Hue must be between 0 and 360');
        }
        if ($saturation < ValidationConstants::PERCENTAGE_MIN || $saturation > ValidationConstants::PERCENTAGE_MAX) {
            throw new InvalidArgumentException('Saturation must be between 0 and 100');
        }
        if ($value < ValidationConstants::PERCENTAGE_MIN || $value > ValidationConstants::PERCENTAGE_MAX) {
            throw new InvalidArgumentException('Value must be between 0 and 100');
        }

        $h = $hue / ValidationConstants::HUE_MAX;
        $s = $saturation / ValidationConstants::PERCENTAGE_MAX;
        $v = $value / ValidationConstants::PERCENTAGE_MAX;

        if (abs($s) < ValidationConstants::FLOAT_EPSILON) {
            $val = (int) round($v * ValidationConstants::MAX_RGB_VALUE);

            return new Color($val, $val, $val);
        }

        $h = $h * 6;
        $i = floor($h);
        $f = $h - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $s * $f);
        $t = $v * (1 - $s * (1 - $f));

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
            default:
                $r = $g = $b = 0;
        }

        return new Color(
            (int) round($r * ValidationConstants::MAX_RGB_VALUE),
            (int) round($g * ValidationConstants::MAX_RGB_VALUE),
            (int) round($b * ValidationConstants::MAX_RGB_VALUE)
        );
    }

    /**
     * Convert RGB color to CMYK color space
     *
     * @param  ColorInterface  $color  The color to convert
     * @return array{c: int, m: int, y: int, k: int} Cyan, Magenta, Yellow, Key (all 0-100)
     */
    public static function toCmyk(ColorInterface $color): array
    {
        $r = $color->getRed() / ValidationConstants::MAX_RGB_VALUE;
        $g = $color->getGreen() / ValidationConstants::MAX_RGB_VALUE;
        $b = $color->getBlue() / ValidationConstants::MAX_RGB_VALUE;

        $k = 1 - max($r, $g, $b);

        if (abs($k - 1) < ValidationConstants::FLOAT_EPSILON) {
            return [
                'c' => 0,
                'm' => 0,
                'y' => 0,
                'k' => ValidationConstants::PERCENTAGE_MAX,
            ];
        }

        $c = (1 - $r - $k) / (1 - $k);
        $m = (1 - $g - $k) / (1 - $k);
        $y = (1 - $b - $k) / (1 - $k);

        return [
            'c' => (int) round($c * ValidationConstants::PERCENTAGE_MAX),
            'm' => (int) round($m * ValidationConstants::PERCENTAGE_MAX),
            'y' => (int) round($y * ValidationConstants::PERCENTAGE_MAX),
            'k' => (int) round($k * ValidationConstants::PERCENTAGE_MAX),
        ];
    }

    /**
     * Create a Color instance from CMYK values
     *
     * @param  float  $cyan  Cyan (0-100%)
     * @param  float  $magenta  Magenta (0-100%)
     * @param  float  $yellow  Yellow (0-100%)
     * @param  float  $key  Key/Black (0-100%)
     *
     * @throws InvalidArgumentException If values are out of range
     */
    public static function fromCmyk(float $cyan, float $magenta, float $yellow, float $key): Color
    {
        // Validate CMYK values
        if ($cyan < ValidationConstants::PERCENTAGE_MIN || $cyan > ValidationConstants::PERCENTAGE_MAX ||
            $magenta < ValidationConstants::PERCENTAGE_MIN || $magenta > ValidationConstants::PERCENTAGE_MAX ||
            $yellow < ValidationConstants::PERCENTAGE_MIN || $yellow > ValidationConstants::PERCENTAGE_MAX ||
            $key < ValidationConstants::PERCENTAGE_MIN || $key > ValidationConstants::PERCENTAGE_MAX) {
            throw new InvalidArgumentException('CMYK values must be between 0 and 100');
        }

        $c = $cyan / ValidationConstants::PERCENTAGE_MAX;
        $m = $magenta / ValidationConstants::PERCENTAGE_MAX;
        $y = $yellow / ValidationConstants::PERCENTAGE_MAX;
        $k = $key / ValidationConstants::PERCENTAGE_MAX;

        $r = 1 - min(1, $c * (1 - $k) + $k);
        $g = 1 - min(1, $m * (1 - $k) + $k);
        $b = 1 - min(1, $y * (1 - $k) + $k);

        return new Color(
            (int) round($r * ValidationConstants::MAX_RGB_VALUE),
            (int) round($g * ValidationConstants::MAX_RGB_VALUE),
            (int) round($b * ValidationConstants::MAX_RGB_VALUE)
        );
    }

    /**
     * Convert RGB color to CIE L*a*b* color space
     *
     * Algorithm:
     * 1. Convert sRGB to linear RGB (remove gamma correction)
     * 2. Convert linear RGB to CIE XYZ using D65 illuminant
     * 3. Convert XYZ to L*a*b* using CIE 1976 formulas
     *
     * @param  ColorInterface  $color  The color to convert
     * @return array{l: int, a: int, b: int} L* (0-100), a* (-128-127), b* (-128-127)
     *
     * @link https://en.wikipedia.org/wiki/CIELAB_color_space
     * @link http://www.brucelindbloom.com/index.html?Eqn_RGB_XYZ_Matrix.html
     */
    public static function toLab(ColorInterface $color): array
    {
        // First convert RGB to XYZ
        $r = $color->getRed() / ValidationConstants::MAX_RGB_VALUE;
        $g = $color->getGreen() / ValidationConstants::MAX_RGB_VALUE;
        $b = $color->getBlue() / ValidationConstants::MAX_RGB_VALUE;

        // Convert RGB to linear RGB (remove gamma correction)
        $r = ($r > ColorSpaceConstants::SRGB_GAMMA_THRESHOLD)
            ? pow(($r + ColorSpaceConstants::SRGB_GAMMA_OFFSET) / ColorSpaceConstants::SRGB_GAMMA_MULTIPLIER, ColorSpaceConstants::SRGB_GAMMA_POWER)
            : $r / ColorSpaceConstants::SRGB_GAMMA_LINEAR_DIVISOR;
        $g = ($g > ColorSpaceConstants::SRGB_GAMMA_THRESHOLD)
            ? pow(($g + ColorSpaceConstants::SRGB_GAMMA_OFFSET) / ColorSpaceConstants::SRGB_GAMMA_MULTIPLIER, ColorSpaceConstants::SRGB_GAMMA_POWER)
            : $g / ColorSpaceConstants::SRGB_GAMMA_LINEAR_DIVISOR;
        $b = ($b > ColorSpaceConstants::SRGB_GAMMA_THRESHOLD)
            ? pow(($b + ColorSpaceConstants::SRGB_GAMMA_OFFSET) / ColorSpaceConstants::SRGB_GAMMA_MULTIPLIER, ColorSpaceConstants::SRGB_GAMMA_POWER)
            : $b / ColorSpaceConstants::SRGB_GAMMA_LINEAR_DIVISOR;

        // Convert to XYZ using matrix
        $x = $r * ColorSpaceConstants::RGB_TO_XYZ_RED_X + $g * ColorSpaceConstants::RGB_TO_XYZ_GREEN_X + $b * ColorSpaceConstants::RGB_TO_XYZ_BLUE_X;
        $y = $r * ColorSpaceConstants::RGB_TO_XYZ_RED_Y + $g * ColorSpaceConstants::RGB_TO_XYZ_GREEN_Y + $b * ColorSpaceConstants::RGB_TO_XYZ_BLUE_Y;
        $z = $r * ColorSpaceConstants::RGB_TO_XYZ_RED_Z + $g * ColorSpaceConstants::RGB_TO_XYZ_GREEN_Z + $b * ColorSpaceConstants::RGB_TO_XYZ_BLUE_Z;

        // Convert XYZ to Lab using D65 illuminant
        $x = $x / ColorSpaceConstants::D65_WHITE_X;
        $y = $y / ColorSpaceConstants::D65_WHITE_Y;
        $z = $z / ColorSpaceConstants::D65_WHITE_Z;

        $x = ($x > ColorSpaceConstants::LAB_EPSILON) ? pow($x, 1 / 3) : (ColorSpaceConstants::LAB_KAPPA * $x + ColorSpaceConstants::LAB_OFFSET) / ColorSpaceConstants::LAB_MULTIPLIER;
        $y = ($y > ColorSpaceConstants::LAB_EPSILON) ? pow($y, 1 / 3) : (ColorSpaceConstants::LAB_KAPPA * $y + ColorSpaceConstants::LAB_OFFSET) / ColorSpaceConstants::LAB_MULTIPLIER;
        $z = ($z > ColorSpaceConstants::LAB_EPSILON) ? pow($z, 1 / 3) : (ColorSpaceConstants::LAB_KAPPA * $z + ColorSpaceConstants::LAB_OFFSET) / ColorSpaceConstants::LAB_MULTIPLIER;

        return [
            'l' => (int) round((ColorSpaceConstants::LAB_MULTIPLIER * $y) - ColorSpaceConstants::LAB_OFFSET),
            'a' => (int) round(500 * ($x - $y)),
            'b' => (int) round(200 * ($y - $z)),
        ];
    }

    /**
     * Create a Color instance from CIE L*a*b* values
     *
     * @param  float  $lightness  L* (0-100)
     * @param  float  $a  a* component (-128 to 127)
     * @param  float  $b  b* component (-128 to 127)
     *
     * @throws InvalidArgumentException If values are out of range
     */
    public static function fromLab(float $lightness, float $a, float $b): Color
    {
        // Validate LAB values
        if ($lightness < ValidationConstants::LAB_L_MIN || $lightness > ValidationConstants::LAB_L_MAX) {
            throw new InvalidArgumentException('Lightness must be between 0 and 100');
        }
        if ($a < ValidationConstants::LAB_A_MIN || $a > ValidationConstants::LAB_A_MAX) {
            throw new InvalidArgumentException('A value must be between -128 and 127');
        }
        if ($b < ValidationConstants::LAB_B_MIN || $b > ValidationConstants::LAB_B_MAX) {
            throw new InvalidArgumentException('B value must be between -128 and 127');
        }

        // Convert Lab to XYZ
        $y = ($lightness + ColorSpaceConstants::LAB_OFFSET) / ColorSpaceConstants::LAB_MULTIPLIER;
        $x = $a / 500 + $y;
        $z = $y - $b / 200;

        // More accurate conversion constants
        $x3 = pow($x, 3);
        $y3 = pow($y, 3);
        $z3 = pow($z, 3);

        $x = ($x3 > ColorSpaceConstants::LAB_EPSILON) ? $x3 : ($x - ColorSpaceConstants::LAB_OFFSET / ColorSpaceConstants::LAB_MULTIPLIER) / ColorSpaceConstants::LAB_INVERSE_KAPPA;
        $y = ($y3 > ColorSpaceConstants::LAB_EPSILON) ? $y3 : ($y - ColorSpaceConstants::LAB_OFFSET / ColorSpaceConstants::LAB_MULTIPLIER) / ColorSpaceConstants::LAB_INVERSE_KAPPA;
        $z = ($z3 > ColorSpaceConstants::LAB_EPSILON) ? $z3 : ($z - ColorSpaceConstants::LAB_OFFSET / ColorSpaceConstants::LAB_MULTIPLIER) / ColorSpaceConstants::LAB_INVERSE_KAPPA;

        // Scale XYZ values using D65 illuminant
        $x = $x * ColorSpaceConstants::D65_WHITE_X;
        $y = $y * ColorSpaceConstants::D65_WHITE_Y;
        $z = $z * ColorSpaceConstants::D65_WHITE_Z;

        // Convert XYZ to RGB using matrix
        $r = $x * ColorSpaceConstants::XYZ_TO_RGB_X_RED + $y * ColorSpaceConstants::XYZ_TO_RGB_Y_RED + $z * ColorSpaceConstants::XYZ_TO_RGB_Z_RED;
        $g = $x * ColorSpaceConstants::XYZ_TO_RGB_X_GREEN + $y * ColorSpaceConstants::XYZ_TO_RGB_Y_GREEN + $z * ColorSpaceConstants::XYZ_TO_RGB_Z_GREEN;
        $b = $x * ColorSpaceConstants::XYZ_TO_RGB_X_BLUE + $y * ColorSpaceConstants::XYZ_TO_RGB_Y_BLUE + $z * ColorSpaceConstants::XYZ_TO_RGB_Z_BLUE;

        // Convert linear RGB to sRGB
        $r = ($r > ColorSpaceConstants::SRGB_INVERSE_GAMMA_THRESHOLD)
            ? (ColorSpaceConstants::SRGB_GAMMA_MULTIPLIER * pow($r, 1 / ColorSpaceConstants::SRGB_GAMMA_POWER) - ColorSpaceConstants::SRGB_GAMMA_OFFSET)
            : ColorSpaceConstants::SRGB_GAMMA_LINEAR_DIVISOR * $r;
        $g = ($g > ColorSpaceConstants::SRGB_INVERSE_GAMMA_THRESHOLD)
            ? (ColorSpaceConstants::SRGB_GAMMA_MULTIPLIER * pow($g, 1 / ColorSpaceConstants::SRGB_GAMMA_POWER) - ColorSpaceConstants::SRGB_GAMMA_OFFSET)
            : ColorSpaceConstants::SRGB_GAMMA_LINEAR_DIVISOR * $g;
        $b = ($b > ColorSpaceConstants::SRGB_INVERSE_GAMMA_THRESHOLD)
            ? (ColorSpaceConstants::SRGB_GAMMA_MULTIPLIER * pow($b, 1 / ColorSpaceConstants::SRGB_GAMMA_POWER) - ColorSpaceConstants::SRGB_GAMMA_OFFSET)
            : ColorSpaceConstants::SRGB_GAMMA_LINEAR_DIVISOR * $b;

        // Clip values and convert to 8-bit integers
        return new Color(
            (int) round(max(0, min(1, $r)) * ValidationConstants::MAX_RGB_VALUE),
            (int) round(max(0, min(1, $g)) * ValidationConstants::MAX_RGB_VALUE),
            (int) round(max(0, min(1, $b)) * ValidationConstants::MAX_RGB_VALUE)
        );
    }
}
