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
     * @param  string|int  $red  Red component (0-255) or hex string
     * @param  int|null  $green  Green component (0-255)
     * @param  int|null  $blue  Blue component (0-255)
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string|int $red, ?int $green = null, ?int $blue = null)
    {
        // If first argument is a string, treat it as hex
        if (is_string($red)) {
            $color = self::fromHex($red);
            $this->red = $color->getRed();
            $this->green = $color->getGreen();
            $this->blue = $color->getBlue();

            return;
        }

        // Otherwise, treat as RGB values
        if ($green === null || $blue === null) {
            throw new InvalidArgumentException('RGB values must be provided');
        }

        $this->validateColorComponent($red, 'red');
        $this->validateColorComponent($green, 'green');
        $this->validateColorComponent($blue, 'blue');

        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    /**
     * Get the red component
     */
    public function getRed(): int
    {
        return $this->red;
    }

    /**
     * Get the green component
     */
    public function getGreen(): int
    {
        return $this->green;
    }

    /**
     * Get the blue component
     */
    public function getBlue(): int
    {
        return $this->blue;
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

        // Handle shorthand hex (e.g., #f00)
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

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
     * Create a Color instance from RGB values
     *
     * @param  int|array  $red  Red component (0-255) or RGB array
     * @param  int|null  $green  Green component (0-255)
     * @param  int|null  $blue  Blue component (0-255)
     */
    public static function fromRgb(int|array $red, ?int $green = null, ?int $blue = null): self
    {
        if (is_array($red)) {
            if (isset($red[0], $red[1], $red[2])) {
                return new self($red[0], $red[1], $red[2]);
            }

            $r = $red['r'] ?? $red['red'] ?? 0;
            $g = $red['g'] ?? $red['green'] ?? 0;
            $b = $red['b'] ?? $red['blue'] ?? 0;

            return new self($r, $g, $b);
        }

        return new self($red, $green ?? 0, $blue ?? 0);
    }

    /**
     * Create a Color instance from HSL values
     *
     * @param  float  $hue  Hue (0-360)
     * @param  float  $saturation  Saturation (0-100)
     * @param  float  $lightness  Lightness (0-100)
     */
    public static function fromHsl(float $hue, float $saturation, float $lightness): self
    {
        $rgb = self::hslToRgbStatic($hue, $saturation / 100, $lightness / 100);

        return new self($rgb['r'], $rgb['g'], $rgb['b']);
    }

    /**
     * Convert to hex string
     */
    public function toHex(): string
    {
        return sprintf('#%02x%02x%02x', $this->red, $this->green, $this->blue);
    }

    /**
     * Rotate the hue by a certain angle
     *
     * @param  float  $angle  The angle to rotate by (-360 to 360)
     */
    public function rotate(float $angle): self
    {
        return $this->adjustHue($angle);
    }

    /**
     * Set the lightness to a specific value
     *
     * @param  float  $lightness  The lightness value (0-100)
     */
    public function withLightness(float $lightness): self
    {
        $hsl = $this->toHsl();
        $hsl['l'] = max(0, min(1, $lightness / 100));
        $rgb = $this->hslToRgb($hsl['h'], $hsl['s'], $hsl['l']);

        return new self(
            (int) round($rgb['r']),
            (int) round($rgb['g']),
            (int) round($rgb['b'])
        );
    }

    /**
     * Mix with another color
     *
     * @param  ColorInterface  $color  The color to mix with
     * @param  float  $weight  The weight of the other color (0-100)
     */
    public function mix(ColorInterface $color, float $weight): self
    {
        $weight = max(0, min(100, $weight)) / 100;
        $w = $weight * 2 - 1;
        $a = 0; // We don't support alpha yet

        $w1 = ((($w * $a === -1) ? $w : ($w + $a) / (1 + $w * $a)) + 1) / 2;
        $w2 = 1 - $w1;

        return new self(
            (int) round($this->red * $w1 + $color->getRed() * $w2),
            (int) round($this->green * $w1 + $color->getGreen() * $w2),
            (int) round($this->blue * $w1 + $color->getBlue() * $w2)
        );
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
                sprintf('Invalid %s color component: %d (must be between 0 and 255)', $component, $value)
            );
        }
    }

    /**
     * Convert RGB to HSL
     *
     * @return array{h: float, s: float, l: float}
     */
    private function toHsl(): array
    {
        $r = $this->red / 255;
        $g = $this->green / 255;
        $b = $this->blue / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

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
                default:
                    $h = 0;
            }

            $h = $h * 60;
            if ($h < 0) {
                $h += 360;
            }
        }

        return [
            'h' => (float) round($h),
            's' => (float) round($s * 100000000000) / 100000000000,
            'l' => (float) round($l * 100000000000) / 100000000000,
        ];
    }

    /**
     * Convert HSL to RGB
     *
     * @return array{r: int, g: int, b: int}
     */
    private function hslToRgb(float $h, float $s, float $l): array
    {
        if ($s === 0) {
            $r = $g = $b = (int) round($l * 255);

            return ['r' => $r, 'g' => $g, 'b' => $b];
        }

        $h = fmod($h + 360, 360) / 360;

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        $r = $this->hueToRgb($p, $q, $h + 1 / 3);
        $g = $this->hueToRgb($p, $q, $h);
        $b = $this->hueToRgb($p, $q, $h - 1 / 3);

        return [
            'r' => (int) round($r * 255),
            'g' => (int) round($g * 255),
            'b' => (int) round($b * 255),
        ];
    }

    /**
     * Helper function for HSL to RGB conversion
     */
    private function hueToRgb(float $p, float $q, float $t): float
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

    /**
     * Static version of hslToRgb for use in fromHsl
     */
    private static function hslToRgbStatic(float $h, float $s, float $l): array
    {
        if ($s === 0) {
            $value = (int) round($l * 255);

            return [
                'r' => $value,
                'g' => $value,
                'b' => $value,
            ];
        }

        $h = fmod($h + 360, 360) / 360;

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        $r = self::hueToRgbStatic($p, $q, $h + 1 / 3);
        $g = self::hueToRgbStatic($p, $q, $h);
        $b = self::hueToRgbStatic($p, $q, $h - 1 / 3);

        return [
            'r' => (int) round($r * 255),
            'g' => (int) round($g * 255),
            'b' => (int) round($b * 255),
        ];
    }

    /**
     * Static version of hueToRgb for use in hslToRgbStatic
     */
    private static function hueToRgbStatic(float $p, float $q, float $t): float
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

    /**
     * Get the hue component (0-360)
     */
    public function getHue(): float
    {
        return $this->toHsl()['h'];
    }

    /**
     * Get the saturation component (0-100)
     */
    public function getSaturation(): float
    {
        return $this->toHsl()['s'] * 100;
    }

    /**
     * Get the lightness component (0-100)
     */
    public function getLightness(): float
    {
        return $this->toHsl()['l'] * 100;
    }

    /**
     * Saturate the color
     *
     * @param  float  $amount  The amount to saturate by (0-100)
     */
    public function saturate(float $amount): self
    {
        $hsl = $this->toHsl();
        $hsl['s'] = min(1, $hsl['s'] + ($amount / 100));
        $rgb = $this->hslToRgb($hsl['h'], $hsl['s'], $hsl['l']);

        return new self(
            (int) round($rgb['r']),
            (int) round($rgb['g']),
            (int) round($rgb['b'])
        );
    }

    /**
     * Desaturate the color
     *
     * @param  float  $amount  The amount to desaturate by (0-100)
     */
    public function desaturate(float $amount): self
    {
        $hsl = $this->toHsl();
        $hsl['s'] = max(0, $hsl['s'] - ($amount / 100));
        $rgb = $this->hslToRgb($hsl['h'], $hsl['s'], $hsl['l']);

        return new self(
            (int) round($rgb['r']),
            (int) round($rgb['g']),
            (int) round($rgb['b'])
        );
    }

    /**
     * Lighten the color
     *
     * @param  float  $amount  The amount to lighten by (0-100)
     */
    public function lighten(float $amount): self
    {
        $hsl = $this->toHsl();
        $hsl['l'] = min(1, $hsl['l'] + ($amount / 100));
        $rgb = $this->hslToRgb($hsl['h'], $hsl['s'], $hsl['l']);

        return new self(
            (int) round($rgb['r']),
            (int) round($rgb['g']),
            (int) round($rgb['b'])
        );
    }

    /**
     * Darken the color
     *
     * @param  float  $amount  The amount to darken by (0-100)
     */
    public function darken(float $amount): self
    {
        $hsl = $this->toHsl();
        $hsl['l'] = max(0, $hsl['l'] - ($amount / 100));
        $rgb = $this->hslToRgb($hsl['h'], $hsl['s'], $hsl['l']);

        return new self(
            (int) round($rgb['r']),
            (int) round($rgb['g']),
            (int) round($rgb['b'])
        );
    }

    /**
     * Adjust the hue of the color
     *
     * @param  float  $amount  The amount to adjust by (-360 to 360)
     */
    public function adjustHue(float $amount): self
    {
        $hsl = $this->toHsl();
        $hsl['h'] = fmod($hsl['h'] + $amount + 360, 360);
        $rgb = $this->hslToRgb($hsl['h'], $hsl['s'], $hsl['l']);

        return new self(
            (int) round($rgb['r']),
            (int) round($rgb['g']),
            (int) round($rgb['b'])
        );
    }

    /**
     * Get the luminance value
     */
    public function getLuminance(): float
    {
        $r = $this->red / 255;
        $g = $this->green / 255;
        $b = $this->blue / 255;

        $r = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Check if the color is light
     */
    public function isLight(): bool
    {
        return $this->getLuminance() > 0.5;
    }

    /**
     * Check if the color is dark
     */
    public function isDark(): bool
    {
        return ! $this->isLight();
    }

    /**
     * Get the contrast ratio with another color
     */
    public function getContrastRatio(ColorInterface $color): float
    {
        $l1 = $this->getLuminance();
        $l2 = $color->getLuminance();

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Get the RGB components as an array
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
     * Get the brightness value
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
}
