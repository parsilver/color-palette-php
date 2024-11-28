<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorInterface;
use InvalidArgumentException;

class Color implements ColorInterface
{
    private int $red;

    private int $green;

    private int $blue;

    public function __construct(int $red, int $green, int $blue)
    {
        $this->validateRed($red);
        $this->validateGreen($green);
        $this->validateBlue($blue);

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

    private function validateRed(int $value): void
    {
        if ($value < 0 || $value > 255) {
            throw new InvalidArgumentException('Invalid red color component. Must be between 0 and 255');
        }
    }

    private function validateGreen(int $value): void
    {
        if ($value < 0 || $value > 255) {
            throw new InvalidArgumentException('Invalid green color component. Must be between 0 and 255');
        }
    }

    private function validateBlue(int $value): void
    {
        if ($value < 0 || $value > 255) {
            throw new InvalidArgumentException('Invalid blue color component. Must be between 0 and 255');
        }
    }

    public static function fromHex(string $hex): self
    {
        $hex = ltrim($hex, '#');
        if (! preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
            throw new InvalidArgumentException('Invalid hex color format');
        }

        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        return new self($red, $green, $blue);
    }

    public static function fromRgb(array $rgb): self
    {
        $red = $rgb['r'] ?? $rgb[0] ?? 0;
        $green = $rgb['g'] ?? $rgb[1] ?? 0;
        $blue = $rgb['b'] ?? $rgb[2] ?? 0;

        return new self($red, $green, $blue);
    }

    public static function fromHsl(float $hue, float $saturation, float $lightness): self
    {
        // Convert HSL to RGB
        $h = $hue / 360;
        $s = $saturation / 100;
        $l = $lightness / 100;

        if ($s === 0) {
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

    public function toRgb(): array
    {
        return [
            'r' => $this->red,
            'g' => $this->green,
            'b' => $this->blue,
        ];
    }

    public function toHsl(): array
    {
        $r = $this->red / 255;
        $g = $this->green / 255;
        $b = $this->blue / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = $s = $l = ($max + $min) / 2;

        if ($max === $min) {
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
        return (($this->red * 299) + ($this->green * 587) + ($this->blue * 114)) / 1000;
    }

    public function isLight(): bool
    {
        return $this->getBrightness() > 128;
    }

    public function isDark(): bool
    {
        return ! $this->isLight();
    }

    public function getContrastRatio(ColorInterface $color): float
    {
        $l1 = $this->getLuminance() + 0.05;
        $l2 = $color->getLuminance() + 0.05;

        return $l1 > $l2 ? $l1 / $l2 : $l2 / $l1;
    }

    public function getLuminance(): float
    {
        $rgb = [$this->red, $this->green, $this->blue];
        $rgb = array_map(function ($value) {
            $value = $value / 255;

            return $value <= 0.03928
                ? $value / 12.92
                : pow(($value + 0.055) / 1.055, 2.4);
        }, $rgb);

        return $rgb[0] * 0.2126 + $rgb[1] * 0.7152 + $rgb[2] * 0.0722;
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

    public function toHsv(): array
    {
        $r = $this->red / 255;
        $g = $this->green / 255;
        $b = $this->blue / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $v = $max;
        $d = $max - $min;
        $s = $max === 0 ? 0 : $d / $max;

        if ($max === $min) {
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

        if ($s === 0) {
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

    public function toCmyk(): array
    {
        $r = $this->red / 255;
        $g = $this->green / 255;
        $b = $this->blue / 255;

        $k = 1 - max($r, $g, $b);

        if ($k === 1) {
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

    public function toLab(): array
    {
        // First convert RGB to XYZ
        $r = $this->red / 255;
        $g = $this->green / 255;
        $b = $this->blue / 255;

        // Convert RGB to linear RGB (remove gamma correction)
        $r = ($r > 0.04045) ? pow(($r + 0.055) / 1.055, 2.4) : $r / 12.92;
        $g = ($g > 0.04045) ? pow(($g + 0.055) / 1.055, 2.4) : $g / 12.92;
        $b = ($b > 0.04045) ? pow(($b + 0.055) / 1.055, 2.4) : $b / 12.92;

        // Convert to XYZ using more accurate matrix
        $x = $r * 0.4124564390896921 + $g * 0.357576077643909 + $b * 0.18043748326639894;
        $y = $r * 0.21267285140562253 + $g * 0.715152155287818 + $b * 0.07217499330655958;
        $z = $r * 0.019333895582329317 + $g * 0.119192025881303 + $b * 0.9503040785363677;

        // Convert XYZ to Lab using D65 illuminant
        $x = $x / 0.95047;
        $y = $y / 1.00000;
        $z = $z / 1.08883;

        $x = ($x > 0.008856) ? pow($x, 1 / 3) : (903.3 * $x + 16) / 116;
        $y = ($y > 0.008856) ? pow($y, 1 / 3) : (903.3 * $y + 16) / 116;
        $z = ($z > 0.008856) ? pow($z, 1 / 3) : (903.3 * $z + 16) / 116;

        return [
            'l' => (int) round((116 * $y) - 16),
            'a' => (int) round(500 * ($x - $y)),
            'b' => (int) round(200 * ($y - $z)),
        ];
    }

    public static function fromLab(float $lightness, float $a, float $b): self
    {
        // Validate LAB values
        if ($lightness < 0 || $lightness > 100) {
            throw new InvalidArgumentException('Lightness must be between 0 and 100');
        }
        if ($a < -128 || $a > 127) {
            throw new InvalidArgumentException('A value must be between -128 and 127');
        }
        if ($b < -128 || $b > 127) {
            throw new InvalidArgumentException('B value must be between -128 and 127');
        }

        // Convert Lab to XYZ
        $y = ($lightness + 16) / 116;
        $x = $a / 500 + $y;
        $z = $y - $b / 200;

        // More accurate conversion constants
        $x3 = pow($x, 3);
        $y3 = pow($y, 3);
        $z3 = pow($z, 3);

        $x = ($x3 > 0.008856) ? $x3 : ($x - 16 / 116) / 7.787037;
        $y = ($y3 > 0.008856) ? $y3 : ($y - 16 / 116) / 7.787037;
        $z = ($z3 > 0.008856) ? $z3 : ($z - 16 / 116) / 7.787037;

        // Scale XYZ values using D65 illuminant
        $x = $x * 0.95047;
        $y = $y * 1.00000;
        $z = $z * 1.08883;

        // Convert XYZ to RGB using more accurate matrix
        $r = $x * 3.2404542361916533 - $y * 1.5371385127253989 - $z * 0.4985314095560161;
        $g = -$x * 0.969266030505187 + $y * 1.8760108454795392 + $z * 0.04155601753034983;
        $b = $x * 0.05564343095911469 - $y * 0.2040259135167538 + $z * 1.0572251882231791;

        // Convert linear RGB to sRGB
        $r = ($r > 0.0031308) ? (1.055 * pow($r, 1 / 2.4) - 0.055) : 12.92 * $r;
        $g = ($g > 0.0031308) ? (1.055 * pow($g, 1 / 2.4) - 0.055) : 12.92 * $g;
        $b = ($b > 0.0031308) ? (1.055 * pow($b, 1 / 2.4) - 0.055) : 12.92 * $b;

        // Clip values and convert to 8-bit integers
        return new self(
            (int) round(max(0, min(1, $r)) * 255),
            (int) round(max(0, min(1, $g)) * 255),
            (int) round(max(0, min(1, $b)) * 255)
        );
    }
}
