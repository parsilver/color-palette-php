<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Contracts;

/**
 * Interface for individual color operations
 */
interface ColorInterface
{
    /**
     * Convert the color to hexadecimal format
     */
    public function toHex(): string;

    /**
     * Convert the color to RGB format
     *
     * @return array{r: int, g: int, b: int}
     */
    public function toRgb(): array;

    /**
     * Convert the color to HSL format
     *
     * @return array{h: float, s: float, l: float}
     */
    public function toHsl(): array;

    /**
     * Get the brightness value of the color
     */
    public function getBrightness(): float;

    /**
     * Check if the color is considered light
     */
    public function isLight(): bool;

    /**
     * Check if the color is considered dark
     */
    public function isDark(): bool;

    /**
     * Calculate the contrast ratio between this color and another color
     */
    public function getContrastRatio(ColorInterface $color): float;

    /**
     * Get the relative luminance of the color
     */
    public function getLuminance(): float;

    /**
     * Lighten the color by a given amount
     */
    public function lighten(float $amount): self;

    /**
     * Darken the color by a given amount
     */
    public function darken(float $amount): self;

    /**
     * Saturate the color by a given amount
     */
    public function saturate(float $amount): self;

    /**
     * Desaturate the color by a given amount
     */
    public function desaturate(float $amount): self;

    /**
     * Rotate the color by a given degrees
     */
    public function rotate(float $degrees): self;

    /**
     * Set the lightness of the color
     */
    public function withLightness(float $lightness): self;

    /**
     * Get the red component of the color
     */
    public function getRed(): int;

    /**
     * Get the green component of the color
     */
    public function getGreen(): int;

    /**
     * Get the blue component of the color
     */
    public function getBlue(): int;
}
