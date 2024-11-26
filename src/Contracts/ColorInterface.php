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
     * Get the relative luminance of the color
     */
    public function getLuminance(): float;

    /**
     * Calculate the contrast ratio between this color and another color
     */
    public function getContrastRatio(ColorInterface $color): float;
}
