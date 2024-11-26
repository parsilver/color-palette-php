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

    /**
     * Create a new Color instance
     *
     * @param  int  $red  Red component (0-255)
     * @param  int  $green  Green component (0-255)
     * @param  int  $blue  Blue component (0-255)
     *
     * @throws InvalidArgumentException
     */
    public function __construct(int $red, int $green, int $blue)
    {
        $this->validateColorComponent($red, 'red');
        $this->validateColorComponent($green, 'green');
        $this->validateColorComponent($blue, 'blue');

        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    /**
     * Create a Color instance from a hex string
     *
     * @param  string  $hex  Color in hexadecimal format (e.g., "#ff0000" or "ff0000")
     *
     * @throws InvalidArgumentException
     */
    public static function fromHex(string $hex): self
    {
        // Remove hash if present
        $hex = ltrim($hex, '#');

        if (! preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
            throw new InvalidArgumentException('Invalid hex color format');
        }

        return new self(
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        );
    }

    /**
     * Create a Color instance from RGB array
     *
     * @param  array{r: int, g: int, b: int}  $rgb
     */
    public static function fromRgb(array $rgb): self
    {
        return new self(
            $rgb['r'] ?? 0,
            $rgb['g'] ?? 0,
            $rgb['b'] ?? 0
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toHex(): string
    {
        return sprintf('#%02x%02x%02x', $this->red, $this->green, $this->blue);
    }

    /**
     * {@inheritdoc}
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
     * Convert RGB to HSL
     */
    public function toHsl(): array
    {
        $r = $this->red / 255;
        $g = $this->green / 255;
        $b = $this->blue / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = 0;
        $s = 0;
        $l = ($max + $min) / 2;

        if ($max !== $min) {
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

            $h /= 6;
        }

        return [
            'h' => round($h * 360),
            's' => round($s * 100),
            'l' => round($l * 100),
        ];
    }

    /**
     * Create a Color instance from HSL values
     */
    public static function fromHsl(float $h, float $s, float $l): self
    {
        // Convert HSL percentages to decimals
        $h = ($h % 360) / 360;
        $s = min(100, max(0, $s)) / 100;
        $l = min(100, max(0, $l)) / 100;

        if ($s === 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;

            $r = self::hueToRgb($p, $q, $h + 1/3);
            $g = self::hueToRgb($p, $q, $h);
            $b = self::hueToRgb($p, $q, $h - 1/3);
        }

        return new self(
            (int) round($r * 255),
            (int) round($g * 255),
            (int) round($b * 255)
        );
    }

    private static function hueToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }

    public function lighten(float $amount): self
    {
        $hsl = $this->toHsl();
        return self::fromHsl(
            $hsl['h'],
            $hsl['s'],
            min(100, $hsl['l'] + $amount * 100)
        );
    }

    public function darken(float $amount): self
    {
        $hsl = $this->toHsl();
        return self::fromHsl(
            $hsl['h'],
            $hsl['s'],
            max(0, $hsl['l'] - $amount * 100)
        );
    }

    public function saturate(float $amount): self
    {
        $hsl = $this->toHsl();
        return self::fromHsl(
            $hsl['h'],
            min(100, $hsl['s'] + $amount * 100),
            $hsl['l']
        );
    }

    public function desaturate(float $amount): self
    {
        $hsl = $this->toHsl();
        return self::fromHsl(
            $hsl['h'],
            max(0, $hsl['s'] - $amount * 100),
            $hsl['l']
        );
    }

    public function rotate(float $degrees): self
    {
        $hsl = $this->toHsl();
        return self::fromHsl(
            ($hsl['h'] + $degrees) % 360,
            $hsl['s'],
            $hsl['l']
        );
    }

    public function adjustHue(float $degrees): self
    {
        return $this->rotate($degrees);
    }

    public function withLightness(float $lightness): self
    {
        $hsl = $this->toHsl();
        return self::fromHsl(
            $hsl['h'],
            $hsl['s'],
            min(100, max(0, $lightness * 100))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBrightness(): float
    {
        // Using the HSP (Highly Sensitive Poop) color model
        // More info: http://alienryderflex.com/hsp.html
        return sqrt(
            0.299 * ($this->red ** 2) +
            0.587 * ($this->green ** 2) +
            0.114 * ($this->blue ** 2)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isLight(): bool
    {
        return $this->getBrightness() > 127.5;
    }

    /**
     * {@inheritdoc}
     */
    public function isDark(): bool
    {
        return ! $this->isLight();
    }

    /**
     * {@inheritdoc}
     */
    public function getLuminance(): float
    {
        $rgb = array_map(function ($val) {
            $val = $val / 255;

            return $val <= 0.03928
                ? $val / 12.92
                : pow(($val + 0.055) / 1.055, 2.4);
        }, [$this->red, $this->green, $this->blue]);

        return $rgb[0] * 0.2126 + $rgb[1] * 0.7152 + $rgb[2] * 0.0722;
    }

    /**
     * {@inheritdoc}
     */
    public function getContrastRatio(ColorInterface $color): float
    {
        $l1 = $this->getLuminance() + 0.05;
        $l2 = $color->getLuminance() + 0.05;

        return max($l1, $l2) / min($l1, $l2);
    }

    /**
     * Validate a color component value
     *
     * @throws InvalidArgumentException
     */
    private function validateColorComponent(int $value, string $component): void
    {
        if ($value < 0 || $value > 255) {
            throw new InvalidArgumentException(
                sprintf('Invalid %s color component. Must be between 0 and 255', $component)
            );
        }
    }
}
