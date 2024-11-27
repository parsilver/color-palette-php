<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Contracts;

/**
 * Interface for color palette operations
 */
interface ColorPaletteInterface
{
    /**
     * Get all colors in the palette
     *
     * @return array<string|int, ColorInterface>
     */
    public function getColors(): array;

    /**
     * Get suggested text color for a background color
     */
    public function getSuggestedTextColor(ColorInterface $backgroundColor): ColorInterface;

    /**
     * Get suggested surface colors based on the palette
     *
     * @return array<string, ColorInterface>
     */
    public function getSuggestedSurfaceColors(): array;
}
