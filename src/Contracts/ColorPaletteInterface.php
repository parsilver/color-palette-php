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
     * @return array<int, ColorInterface>
     */
    public function getColors(): array;

    /**
     * Get suggested text color based on background
     */
    public function getSuggestedTextColor(ColorInterface $backgroundColor): ColorInterface;

    /**
     * Get suggested surface colors
     *
     * @return array<string, ColorInterface>
     */
    public function getSuggestedSurfaceColors(): array;
}
