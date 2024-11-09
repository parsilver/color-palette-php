<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Contracts;

/**
 * Interface for individual color operations
 */
interface ColorInterface
{
    /**
     * Get color in hex format
     */
    public function toHex(): string;

    /**
     * Get color in RGB format
     *
     * @return array{r: int, g: int, b: int}
     */
    public function toRgb(): array;

    /**
     * Get color brightness value
     */
    public function getBrightness(): float;

    /**
     * Get the luminance of the color
     * This is useful for calculating contrast ratios
     */
    public function getLuminance(): float;

    /**
     * Calculate contrast ratio with another color
     */
    public function getContrastRatio(ColorInterface $color): float;

    /**
     * Check if the color is light
     */
    public function isLight(): bool;

    /**
     * Check if the color is dark
     */
    public function isDark(): bool;
}
