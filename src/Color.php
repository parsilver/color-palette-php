<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Constants\ValidationConstants;
use Farzai\ColorPalette\Contracts\ColorInterface;
use InvalidArgumentException;

class Color implements ColorInterface
{
    private readonly int $red;

    private readonly int $green;

    private readonly int $blue;

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

        if (preg_match('/^[0-9A-Fa-f]{3}$/', $hex)) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

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
        $color = ColorSpaceConverter::fromHsl($hue, $saturation, $lightness);

        return new self($color->getRed(), $color->getGreen(), $color->getBlue());
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
        return ColorSpaceConverter::toHsl($this);
    }

    public function getBrightness(): float
    {
        return ColorAnalyzer::getBrightness($this);
    }

    public function isLight(): bool
    {
        return ColorAnalyzer::isLight($this);
    }

    public function isDark(): bool
    {
        return ColorAnalyzer::isDark($this);
    }

    public function getContrastRatio(ColorInterface $color): float
    {
        return ColorAnalyzer::getContrastRatio($this, $color);
    }

    public function getLuminance(): float
    {
        return ColorAnalyzer::getLuminance($this);
    }

    public function lighten(float $amount): self
    {
        return ColorManipulator::lighten($this, $amount);
    }

    public function darken(float $amount): self
    {
        return ColorManipulator::darken($this, $amount);
    }

    public function saturate(float $amount): self
    {
        return ColorManipulator::saturate($this, $amount);
    }

    public function desaturate(float $amount): self
    {
        return ColorManipulator::desaturate($this, $amount);
    }

    public function rotate(float $degrees): self
    {
        return ColorManipulator::rotate($this, $degrees);
    }

    public function withLightness(float $lightness): self
    {
        return ColorManipulator::withLightness($this, $lightness);
    }

    /**
     * Convert color to HSV array
     *
     * @return array{h: int, s: int, v: int}
     */
    public function toHsv(): array
    {
        return ColorSpaceConverter::toHsv($this);
    }

    public static function fromHsv(float $hue, float $saturation, float $value): self
    {
        $color = ColorSpaceConverter::fromHsv($hue, $saturation, $value);

        return new self($color->getRed(), $color->getGreen(), $color->getBlue());
    }

    /**
     * Convert color to CMYK array
     *
     * @return array{c: int, m: int, y: int, k: int}
     */
    public function toCmyk(): array
    {
        return ColorSpaceConverter::toCmyk($this);
    }

    public static function fromCmyk(float $cyan, float $magenta, float $yellow, float $key): self
    {
        $color = ColorSpaceConverter::fromCmyk($cyan, $magenta, $yellow, $key);

        return new self($color->getRed(), $color->getGreen(), $color->getBlue());
    }

    /**
     * Convert color to LAB array
     *
     * @return array{l: int, a: int, b: int}
     */
    public function toLab(): array
    {
        return ColorSpaceConverter::toLab($this);
    }

    public static function fromLab(float $lightness, float $a, float $b): self
    {
        $color = ColorSpaceConverter::fromLab($lightness, $a, $b);

        return new self($color->getRed(), $color->getGreen(), $color->getBlue());
    }
}
