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
}
