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
     *
     * @return string
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
     *
     * @return float
     */
    public function getBrightness(): float;

    /**
     * Get the luminance of the color
     * This is useful for calculating contrast ratios
     *
     * @return float
     */
    public function getLuminance(): float;

    /**
     * Calculate contrast ratio with another color
     *
     * @param ColorInterface $color
     * @return float
     */
    public function getContrastRatio(ColorInterface $color): float;

    /**
     * Check if the color is light
     *
     * @return bool
     */
    public function isLight(): bool;

    /**
     * Check if the color is dark
     *
     * @return bool
     */
    public function isDark(): bool;
}