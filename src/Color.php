<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Constants\AccessibilityConstants;
use Farzai\ColorPalette\Constants\ColorSpaceConstants;
use Farzai\ColorPalette\Constants\ValidationConstants;
use Farzai\ColorPalette\Contracts\ColorInterface;
use InvalidArgumentException;

class Color implements ColorInterface
{
    private int $red;

    private int $green;

    private int $blue;

    public function __construct(int $red, int $green, int $blue)
    {
        $this->validateColorComponent($red, 'red');
        $this->validateColorComponent($green, 'green');
        $this->validateColorComponent($blue, 'blue');

        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    public function getRed(): int
    {
        return $this->red;
    }

    public function getGreen(): int
    {
        return $this->green;
    }

    public function getBlue(): int
    {
        return $this->blue;
    }

    /**
     * Validate a color component value
     *
     * @param  int  $value  The color component value to validate
     * @param  string  $component  The name of the component (red, green, or blue)
     *
     * @throws InvalidArgumentException If the value is not in the valid range (0-255)
     */
    private function validateColorComponent(int $value, string $component): void
    {
        if ($value < ValidationConstants::MIN_RGB_VALUE || $value > ValidationConstants::MAX_RGB_VALUE) {
            throw new InvalidArgumentException(
                "Invalid {$component} color component. Must be between ".
                ValidationConstants::MIN_RGB_VALUE.' and '.ValidationConstants::MAX_RGB_VALUE.", got {$value}"
            );
        }
    }

    public static function fromHex(string $hex): self
    {
        $hex = ltrim($hex, '#');
        if (! preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
            throw new InvalidArgumentException('Invalid hex color format');
        }

        $red = (int) hexdec(substr($hex, 0, 2));
        $green = (int) hexdec(substr($hex, 2, 2));
        $blue = (int) hexdec(substr($hex, 4, 2));

        return new self($red, $green, $blue);
    }

    /**
     * Create a Color instance from an RGB array
     *
     * @param  array<string|int, int|float>  $rgb  Array with 'r', 'g', 'b' keys or numeric indices
     *
     * @throws InvalidArgumentException If array format is invalid
     */
    public static function fromRgb(array $rgb): self
    {
        // Check for string keys (r, g, b)
        if (isset($rgb['r'], $rgb['g'], $rgb['b'])) {
            return new self(
                (int) $rgb['r'],
                (int) $rgb['g'],
                (int) $rgb['b']
            );
        }

        // Check for numeric keys (0, 1, 2)
        if (isset($rgb[0], $rgb[1], $rgb[2])) {
            return new self(
                (int) $rgb[0],
                (int) $rgb[1],
                (int) $rgb[2]
            );
        }

        throw new InvalidArgumentException(
            'RGB array must have either string keys (r, g, b) or numeric keys (0, 1, 2). '.
            'Got keys: '.implode(', ', array_keys($rgb))
        );
    }

    public static function fromHsl(float $hue, float $saturation, float $lightness): self
    {
        // Convert HSL to RGB
        $h = $hue / 360;
        $s = $saturation / 100;
        $l = $lightness / 100;

        if (abs($s) < 0.0001) {
            $r = $g = $b = (int) round($l * 255);

            return new self($r, $g, $b);
        }

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        $r = self::hueToRgb($p, $q, $h + 1 / 3);
        $g = self::hueToRgb($p, $q, $h);
        $b = self::hueToRgb($p, $q, $h - 1 / 3);

        return new self(
            (int) round($r * 255),
            (int) round($g * 255),
            (int) round($b * 255)
        );
    }

    private static function hueToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1 / 2) {
            return $q;
        }
        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }

        return $p;
    }

    public function toHex(): string
    {
        return sprintf('#%02x%02x%02x', $this->red, $this->green, $this->blue);
    }

    /**
     * Convert color to RGB array
     *
     * @return array{r: int, g: int, b: int}
     */
    public function toRgb(): array
    {
        return [
            'r' => $this->red,
            'g' => $this->green,
            'b' => $this->blue,
        ];
    }

    /**
     * Convert color to HSL array
     *
     * @return array{h: int, s: int, l: int}
     */
    public function toHsl(): array
    {
        $r = $this->red / 255;
        $g = $this->green / 255;
        $b = $this->blue / 255;

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
                $h += 360;
            }
        }

        return [
            'h' => (int) $h,
            's' => (int) round($s * 100),
            'l' => (int) round($l * 100),
        ];
    }

    public function getBrightness(): float
    {
        return (($this->red * AccessibilityConstants::BRIGHTNESS_RED_COEFFICIENT) +
                ($this->green * AccessibilityConstants::BRIGHTNESS_GREEN_COEFFICIENT) +
                ($this->blue * AccessibilityConstants::BRIGHTNESS_BLUE_COEFFICIENT)) / AccessibilityConstants::BRIGHTNESS_DIVISOR;
    }

    public function isLight(): bool
    {
        return $this->getBrightness() > AccessibilityConstants::BRIGHTNESS_THRESHOLD;
    }

    public function isDark(): bool
    {
        return ! $this->isLight();
    }

    public function getContrastRatio(ColorInterface $color): float
    {
        $l1 = $this->getLuminance() + AccessibilityConstants::CONTRAST_LUMINANCE_OFFSET;
        $l2 = $color->getLuminance() + AccessibilityConstants::CONTRAST_LUMINANCE_OFFSET;

        return $l1 > $l2 ? $l1 / $l2 : $l2 / $l1;
    }

    public function getLuminance(): float
    {
        $rgb = [$this->red, $this->green, $this->blue];
        $rgb = array_map(function ($value) {
            $value = $value / ValidationConstants::MAX_RGB_VALUE;

            return $value <= AccessibilityConstants::LUMINANCE_GAMMA_THRESHOLD
                ? $value / AccessibilityConstants::LUMINANCE_GAMMA_DIVISOR
                : pow(($value + AccessibilityConstants::LUMINANCE_GAMMA_OFFSET) / AccessibilityConstants::LUMINANCE_GAMMA_MULTIPLIER,
                    AccessibilityConstants::LUMINANCE_GAMMA_POWER);
        }, $rgb);

        return $rgb[0] * AccessibilityConstants::LUMINANCE_RED_COEFFICIENT +
               $rgb[1] * AccessibilityConstants::LUMINANCE_GREEN_COEFFICIENT +
               $rgb[2] * AccessibilityConstants::LUMINANCE_BLUE_COEFFICIENT;
    }

    public function lighten(float $amount): self
    {
        $hsl = $this->toHsl();
        $hsl['l'] = min(100, $hsl['l'] + $amount * 100);

        return self::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);
    }

    public function darken(float $amount): self
    {
        $hsl = $this->toHsl();
        $hsl['l'] = max(0, $hsl['l'] - $amount * 100);

        return self::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);
    }

    public function saturate(float $amount): self
    {
        $hsl = $this->toHsl();
        $hsl['s'] = min(100, $hsl['s'] + $amount * 100);

        return self::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);
    }

    public function desaturate(float $amount): self
    {
        $hsl = $this->toHsl();
        $hsl['s'] = max(0, $hsl['s'] - $amount * 100);

        return self::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);
    }

    public function rotate(float $degrees): self
    {
        $hsl = $this->toHsl();
        $hsl['h'] = fmod(($hsl['h'] + $degrees + 360), 360);

        return self::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);
    }

    public function withLightness(float $lightness): self
    {
        $hsl = $this->toHsl();

        return self::fromHsl($hsl['h'], $hsl['s'], $lightness * 100);
    }

    /**
     * Convert color to HSV array
     *
     * @return array{h: int, s: int, v: int}
     */
    public function toHsv(): array
    {
        $r = $this->red / 255;
        $g = $this->green / 255;
        $b = $this->blue / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $v = $max;
        $d = $max - $min;
        $s = abs($max) < 0.0001 ? 0 : $d / $max;

        if (abs($max - $min) < 0.0001) {
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
            's' => (int) round($s * 100),
            'v' => (int) round($v * 100),
        ];
    }

    public static function fromHsv(float $hue, float $saturation, float $value): self
    {
        // Validate HSV values
        if ($hue < 0 || $hue >= 360) {
            throw new InvalidArgumentException('Hue must be between 0 and 360');
        }
        if ($saturation < 0 || $saturation > 100) {
            throw new InvalidArgumentException('Saturation must be between 0 and 100');
        }
        if ($value < 0 || $value > 100) {
            throw new InvalidArgumentException('Value must be between 0 and 100');
        }

        $h = $hue / 360;
        $s = $saturation / 100;
        $v = $value / 100;

        if (abs($s) < 0.0001) {
            $val = (int) round($v * 255);

            return new self($val, $val, $val);
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

        return new self(
            (int) round($r * 255),
            (int) round($g * 255),
            (int) round($b * 255)
        );
    }

    /**
     * Convert color to CMYK array
     *
     * @return array{c: int, m: int, y: int, k: int}
     */
    public function toCmyk(): array
    {
        $r = $this->red / 255;
        $g = $this->green / 255;
        $b = $this->blue / 255;

        $k = 1 - max($r, $g, $b);

        if (abs($k - 1) < ValidationConstants::FLOAT_EPSILON) {
            return [
                'c' => 0,
                'm' => 0,
                'y' => 0,
                'k' => 100,
            ];
        }

        $c = (1 - $r - $k) / (1 - $k);
        $m = (1 - $g - $k) / (1 - $k);
        $y = (1 - $b - $k) / (1 - $k);

        return [
            'c' => (int) round($c * 100),
            'm' => (int) round($m * 100),
            'y' => (int) round($y * 100),
            'k' => (int) round($k * 100),
        ];
    }

    public static function fromCmyk(float $cyan, float $magenta, float $yellow, float $key): self
    {
        // Validate CMYK values
        if ($cyan < 0 || $cyan > 100 ||
            $magenta < 0 || $magenta > 100 ||
            $yellow < 0 || $yellow > 100 ||
            $key < 0 || $key > 100) {
            throw new InvalidArgumentException('CMYK values must be between 0 and 100');
        }

        $c = $cyan / 100;
        $m = $magenta / 100;
        $y = $yellow / 100;
        $k = $key / 100;

        $r = 1 - min(1, $c * (1 - $k) + $k);
        $g = 1 - min(1, $m * (1 - $k) + $k);
        $b = 1 - min(1, $y * (1 - $k) + $k);

        return new self(
            (int) round($r * 255),
            (int) round($g * 255),
            (int) round($b * 255)
        );
    }

    /**
     * Convert color to LAB array
     *
     * @return array{l: int, a: int, b: int}
     */
    public function toLab(): array
    {
        // First convert RGB to XYZ
        $r = $this->red / 255;
        $g = $this->green / 255;
        $b = $this->blue / 255;

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

        // Convert to XYZ using more accurate matrix
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

    public static function fromLab(float $lightness, float $a, float $b): self
    {
        // Validate LAB values
        if ($lightness < ValidationConstants::LAB_L_MIN || $lightness > ValidationConstants::LAB_L_MAX) {
            throw new InvalidArgumentException(
                'Lightness must be between '.ValidationConstants::LAB_L_MIN.' and '.ValidationConstants::LAB_L_MAX
            );
        }
        if ($a < ValidationConstants::LAB_A_MIN || $a > ValidationConstants::LAB_A_MAX) {
            throw new InvalidArgumentException(
                'A value must be between '.ValidationConstants::LAB_A_MIN.' and '.ValidationConstants::LAB_A_MAX
            );
        }
        if ($b < ValidationConstants::LAB_B_MIN || $b > ValidationConstants::LAB_B_MAX) {
            throw new InvalidArgumentException(
                'B value must be between '.ValidationConstants::LAB_B_MIN.' and '.ValidationConstants::LAB_B_MAX
            );
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

        // Convert XYZ to RGB using more accurate matrix
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
        return new self(
            (int) round(max(0, min(1, $r)) * ValidationConstants::MAX_RGB_VALUE),
            (int) round(max(0, min(1, $g)) * ValidationConstants::MAX_RGB_VALUE),
            (int) round(max(0, min(1, $b)) * ValidationConstants::MAX_RGB_VALUE)
        );
    }
}
